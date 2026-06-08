<?php
// ==================== GET PRODUCTS ====================
// get_products.php - Fetch products with filtering
include 'config.php';
header('Content-Type: application/json');

$conn = getConnection();
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if ($category && $category !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY price DESC";
        break;
    default:
        $sql .= " ORDER BY created_at DESC";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($products);
$conn->close();
?>