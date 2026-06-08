<?php
// ==================== ADMIN DASHBOARD ====================
session_start();
require_once __DIR__ . '/../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$conn = getConnection();

// Statistics
$stats = [];
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['users'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM products");
$stats['products'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total, SUM(total_amount) as revenue FROM orders");
$orderStats = $result->fetch_assoc();
$stats['orders'] = $orderStats['total'];
$stats['revenue'] = $orderStats['revenue'] ?? 0;

$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$stats['pending'] = $result->fetch_assoc()['total'];

// Handle POST actions (products & orders)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = sanitize($_POST['name']);
        $category = sanitize($_POST['category']);
        $price = (float)$_POST['price'];
        $old_price = (float)($_POST['old_price'] ?? 0);
        $stock = (int)$_POST['stock'];
        $description = sanitize($_POST['description']);
        
        $image = 'assets/images/product-' . time() . '.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target = '../' . $image;
            move_uploaded_file($_FILES['image']['tmp_name'], $target);
        }
        
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, old_price, stock, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddiss", $name, $category, $price, $old_price, $stock, $description, $image);
        $stmt->execute();
        header('Location: dashboard.php?msg=added');
        exit();
    }
    
    if (isset($_POST['update_order'])) {
        $order_id = (int)$_POST['order_id'];
        $status = sanitize($_POST['status']);
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        header('Location: dashboard.php?tab=orders&msg=updated');
        exit();
    }
}

$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$orders = $conn->query("SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.ordered_at DESC")->fetch_all(MYSQLI_ASSOC);
$users = $conn->query("SELECT id, full_name, email, role, status, created_at FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Your existing CSS (kept as is) */
        .admin-container { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 280px; background: #0a192f; color: white; padding: 2rem; }
        .admin-sidebar h2 { color: #64ffda; margin-bottom: 2rem; }
        .admin-sidebar a { display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; color: #f5f7fa; text-decoration: none; border-radius: 10px; margin-bottom: 0.5rem; transition: all 0.3s ease; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: rgba(100,255,218,0.1); color: #64ffda; }
        .admin-main { flex: 1; padding: 2rem; background: #f5f7fa; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .stat-card h3 { font-size: 0.9rem; color: #8892b0; margin-bottom: 0.5rem; }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #0a192f; }
        .admin-table { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .admin-table table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #f5f7fa; }
        .admin-table th { background: #0a192f; color: white; }
        .btn-sm { padding: 0.25rem 0.75rem; font-size: 0.8rem; margin-right: 0.5rem; background: #0a192f; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); align-items: center; justify-content: center; z-index: 1200; }
        .modal.active { display: flex; }
        .modal-content { background: white; border-radius: 20px; padding: 2rem; max-width: 500px; width: 90%; position: relative; }
        .close-modal { position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; cursor: pointer; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .status-pending { background: #ffc107; color: #000; }
        .status-approved { background: #28a745; color: #fff; }
        .status-shipped { background: #17a2b8; color: #fff; }
        .status-delivered { background: #007bff; color: #fff; }
        .status-cancelled { background: #dc3545; color: #fff; }
        .status-active { background: #28a745; color: #fff; }
        .status-inactive { background: #dc3545; color: #fff; }
        .full-width { width: 100%; }
        .form-group { margin-bottom: 1rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; }
        .btn-primary { background: #64ffda; color: #0a192f; border: none; padding: 0.75rem 1.5rem; border-radius: 40px; cursor: pointer; }
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: #0a192f; color: white; padding: 1rem 1.5rem; border-radius: 10px; z-index: 1300; transform: translateX(200%); transition: all 0.3s ease; }
        .toast.show { transform: translateX(0); }
        .toast.success { background: #28a745; }
        .toast.error { background: #dc3545; }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="admin-sidebar">
        <h2>OTC Admin</h2>
        <nav>
            <a href="#" class="active" data-tab="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="#" data-tab="products"><i class="fas fa-box"></i> Products</a>
            <a href="#" data-tab="orders"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="#" data-tab="users"><i class="fas fa-users"></i> Users</a>

            <form method="POST" action="../logout.php" style="margin:0;">
                <button type="submit" style="background:none; border:none; color:white; cursor:pointer; display:flex; 
            align-items:center; gap:1rem; padding:0.75rem 1rem; width:100%; font-size:1rem;">

               <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>

        </nav>
    </div>
    <div class="admin-main">
        <!-- Dashboard Tab -->
        <div id="dashboard-tab" class="tab-pane active">
            <div class="stats-grid">
                <div class="stat-card"><h3>Total Users</h3><div class="number"><?php echo $stats['users']; ?></div></div>
                <div class="stat-card"><h3>Products</h3><div class="number"><?php echo $stats['products']; ?></div></div>
                <div class="stat-card"><h3>Orders</h3><div class="number"><?php echo $stats['orders']; ?></div></div>
                <div class="stat-card"><h3>Revenue</h3><div class="number">रु <?php echo number_format($stats['revenue'],2); ?></div></div>
                <div class="stat-card"><h3>Pending Orders</h3><div class="number"><?php echo $stats['pending']; ?></div></div>
            </div>
            <div class="admin-table">
                <h3 style="padding:1rem;">Recent Orders</h3>
                <table>
                    <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach (array_slice($orders,0,5) as $order): ?>
                        <tr>
                            <td><?php echo $order['order_number']; ?></td>
                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                            <td>रु <?php echo number_format($order['total_amount'],2); ?></td>
                            <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span></td>
                            <td><button class="btn-sm edit-order" data-id="<?php echo $order['id']; ?>">Update</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Products Tab -->
        <div id="products-tab" class="tab-pane">
            <button class="btn-primary" id="addProductBtn" style="margin-bottom:1rem;">+ Add Product</button>
            <div class="admin-table">
                <table>
                    <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><img src="../<?php echo $product['image']; ?>" width="50" height="50" style="object-fit:cover;"></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo $product['category']; ?></td>
                            <td>रु <?php echo number_format($product['price'],2); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><button class="btn-sm edit-product" data-id="<?php echo $product['id']; ?>">Edit</button><button class="btn-sm delete-product" data-id="<?php echo $product['id']; ?>">Delete</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Orders Tab -->
        <div id="orders-tab" class="tab-pane">
            <div class="admin-table">
                <table>
                    <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['order_number']; ?></td>
                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                            <td>रु <?php echo number_format($order['total_amount'],2); ?></td>
                            <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($order['ordered_at'])); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $order['status']=='pending'?'selected':''; ?>>Pending</option>
                                        <option value="approved" <?php echo $order['status']=='approved'?'selected':''; ?>>Approved</option>
                                        <option value="shipped" <?php echo $order['status']=='shipped'?'selected':''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $order['status']=='delivered'?'selected':''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $order['status']=='cancelled'?'selected':''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_order" value="1">
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Users Tab (with Add User button and Edit column) -->
        <div id="users-tab" class="tab-pane">
            <button class="btn-primary" id="addUserBtn" style="margin-bottom:1rem;">+ Add New User</button>
            <div class="admin-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><span class="status-badge status-approved"><?php echo $user['role']; ?></span></td>
                            <td><span class="status-badge status-<?php echo $user['status']; ?>"><?php echo $user['status']; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <button class="btn-sm edit-user" data-id="<?php echo $user['id']; ?>">Edit</button>
                                <button class="btn-sm toggle-status" data-id="<?php echo $user['id']; ?>"><?php echo $user['status'] == 'active' ? 'Block' : 'Unblock'; ?></button>
                                <button class="btn-sm change-role" data-id="<?php echo $user['id']; ?>"><?php echo $user['role'] == 'admin' ? 'Make User' : 'Make Admin'; ?></button>
                                <button class="btn-sm delete-user" data-id="<?php echo $user['id']; ?>">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal" id="productModal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3 id="modalTitle">Add Product</h3>
        <form id="productForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="productId">
            <div class="form-group"><input type="text" name="name" id="productName" placeholder="Product Name" required></div>
            <div class="form-group"><select name="category" id="productCategory" required><option value="men">Men</option><option value="women">Women</option><option value="kids">Kids</option></select></div>
            <div class="form-group"><input type="number" step="0.01" name="price" id="productPrice" placeholder="Price" required></div>
            <div class="form-group"><input type="number" step="0.01" name="old_price" id="productOldPrice" placeholder="Old Price"></div>
            <div class="form-group"><input type="number" name="stock" id="productStock" placeholder="Stock Quantity" required></div>
            <div class="form-group"><textarea name="description" id="productDescription" placeholder="Product Description" rows="3"></textarea></div>
            <div class="form-group"><input type="file" name="image" id="productImage" accept="image/*"></div>
            <button type="submit" name="add_product" class="btn-primary full-width">Save Product</button>
        </form>
    </div>
</div>

<!-- Order Modal -->
<div class="modal" id="orderModal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3>Update Order Status</h3>
        <form method="POST">
            <input type="hidden" name="order_id" id="orderId">
            <div class="form-group"><select name="status" id="orderStatus" required><option value="pending">Pending</option><option value="approved">Approved</option><option value="shipped">Shipped</option><option value="delivered">Delivered</option><option value="cancelled">Cancelled</option></select></div>
            <button type="submit" name="update_order" class="btn-primary full-width">Update Status</button>
        </form>
    </div>
</div>

<!-- User Modal (Add / Edit) -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3 id="userModalTitle">Add User</h3>
        <form id="userForm">
            <input type="hidden" name="user_id" id="userId">
            <div class="form-group"><input type="text" name="full_name" id="userFullName" placeholder="Full Name" required></div>
            <div class="form-group"><input type="email" name="email" id="userEmail" placeholder="Email" required></div>
            <div class="form-group"><input type="password" name="password" id="userPassword" placeholder="Password (leave blank if not changing)"></div>
            <div class="form-group">
                <select name="role" id="userRole" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <select name="status" id="userStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn-primary full-width">Save User</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$(document).ready(function(){
    // Tab switching
    $('.admin-sidebar a').on('click',function(e){
        e.preventDefault();
        $('.admin-sidebar a').removeClass('active');
        $(this).addClass('active');
        $('.tab-pane').removeClass('active');
        var tab = $(this).data('tab');
        $('#'+tab+'-tab').addClass('active');
    });

    // ---------- PRODUCTS ----------
    $('#addProductBtn').on('click',function(){
        $('#modalTitle').text('Add Product');
        $('#productForm')[0].reset();
        $('#productId').val('');
        $('#productModal').addClass('active');
    });
    $('.edit-product').on('click',function(){
        var id = $(this).data('id');
        $.ajax({url:'get_product.php', method:'GET', data:{id:id}, success:function(p){
            $('#modalTitle').text('Edit Product');
            $('#productId').val(p.id);
            $('#productName').val(p.name);
            $('#productCategory').val(p.category);
            $('#productPrice').val(p.price);
            $('#productOldPrice').val(p.old_price);
            $('#productStock').val(p.stock);
            $('#productDescription').val(p.description);
            $('#productModal').addClass('active');
        }});
    });
    $('.delete-product').on('click',function(){
        if(confirm('Delete this product?')){
            var id = $(this).data('id');
            $.ajax({url:'delete_product.php', method:'POST', data:{id:id}, success:function(){ location.reload(); }});
        }
    });

    // ---------- ORDERS ----------
    $('.edit-order').on('click',function(){
        var id = $(this).data('id');
        $('#orderId').val(id);
        $('#orderModal').addClass('active');
    });

    // ---------- USER MANAGEMENT ----------
    // Add User button
    $('#addUserBtn').on('click', function(){
        $('#userModalTitle').text('Add User');
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#userPassword').prop('required', true);
        $('#userModal').addClass('active');
    });

    // Edit User button
    $('.edit-user').on('click', function(){
        var id = $(this).data('id');
        $.ajax({
            url: 'edit_user.php',
            method: 'GET',
            data: { id: id },
            success: function(user){
                $('#userModalTitle').text('Edit User');
                $('#userId').val(user.id);
                $('#userFullName').val(user.full_name);
                $('#userEmail').val(user.email);
                $('#userRole').val(user.role);
                $('#userStatus').val(user.status);
                $('#userPassword').prop('required', false);
                $('#userModal').addClass('active');
            }
        });
    });

    // Submit Add/Edit User form
    $('#userForm').on('submit', function(e){
        e.preventDefault();
        var id = $('#userId').val();
        var url = id ? 'update_user.php' : 'add_user.php';
        $.ajax({
            url: url,
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res){
                if(res.success){
                    showToast(id ? 'User updated' : 'User added', 'success');
                    setTimeout(function(){ location.reload(); }, 1500);
                } else {
                    showToast(res.message || 'Error occurred', 'error');
                }
            },
            error: function(){
                showToast('Server error', 'error');
            }
        });
    });

    // Toggle Status (Block/Unblock)
    $('.toggle-status').on('click', function(){
        var id = $(this).data('id');
        var btn = $(this);
        if(confirm('Do you want to change this user\'s status?')){
            $.ajax({
                url: 'toggle_status.php',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    if(res.success){
                        var row = btn.closest('tr');
                        var statusSpan = row.find('.status-badge.status-active, .status-badge.status-inactive');
                        var newStatus = res.new_status;
                        statusSpan.removeClass('status-active status-inactive').addClass('status-'+newStatus);
                        statusSpan.text(newStatus);
                        btn.text(newStatus == 'active' ? 'Block' : 'Unblock');
                        showToast('Status updated', 'success');
                    } else {
                        showToast(res.message || 'Error occured', 'error');
                    }
                },
                error: function(){
                    showToast('server error', 'error');
                }
            });
        }
    });

    // Change Role (Make Admin / Make User)
    $('.change-role').on('click', function(){
        var id = $(this).data('id');
        var btn = $(this);
        if(confirm('Do you want to change this user\'s role')){
            $.ajax({
                url: 'change_role.php',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    if(res.success){
                        var row = btn.closest('tr');
                        var roleSpan = row.find('.status-badge.status-approved');
                        var newRole = res.new_role;
                        roleSpan.text(newRole);
                        btn.text(newRole == 'admin' ? 'Make User' : 'Make Admin');
                        showToast('Role changed', 'success');
                    } else {
                        showToast(res.message || 'Error occurred', 'error');
                    }
                },
                error: function(){
                    showToast('Server error', 'error');
                }
            });
        }
    });

    // Delete User
    $('.delete-user').on('click', function(){
        var id = $(this).data('id');
        var btn = $(this);
        if(confirm('Are you sure you want to permanently delete this user? This action cannot be undone.')){
            $.ajax({
                url: 'delete_user.php',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(res){
                    if(res.success){
                        btn.closest('tr').remove();
                        showToast('प्रयोगकर्ता मेटियो', 'success');
                    } else {
                        showToast(res.message || 'Could not delete', 'error');
                    }
                },
                error: function(){
                    showToast('Server error', 'error');
                }
            });
        }
    });

    // Close modals
    $('.close-modal').on('click',function(){ $(this).closest('.modal').removeClass('active'); });
    $(window).on('click',function(e){ if($(e.target).hasClass('modal')) $('.modal').removeClass('active'); });
});

// Toast notification function
function showToast(msg, type){
    var toast = $('#toast');
    if(toast.length === 0){
        $('body').append('<div id="toast" class="toast"></div>');
        toast = $('#toast');
    }
    toast.removeClass('success error').addClass(type);
    toast.text(msg).addClass('show');
    setTimeout(function(){ toast.removeClass('show'); }, 3000);
}
</script>
</body>
</html>