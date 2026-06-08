<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'online_theft_clothing';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if admin exists
$result = $conn->query("SELECT * FROM users WHERE email = 'admin@onlinetheft.com'");
if ($result->num_rows == 0) {
    // Create admin if not exists
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (full_name, email, password, role) VALUES ('Admin User', 'admin@onlinetheft.com', '$hash', 'admin')");
    echo "Admin user created!<br>";
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['full_name'];
            $_SESSION['role'] = $row['role'];
            
            echo "<h2 style='color:green'>✅ LOGIN SUCCESSFUL!</h2>";
            echo "<p>Redirecting to dashboard in 2 seconds...</p>";
            echo "<script>setTimeout(function(){ window.location.href='admin/dashboard.php'; }, 2000);</script>";
            exit();
        } else {
            $error = "Wrong password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
    <style>
        body{font-family:Arial;background:#f0f2f5;display:flex;justify-content:center;align-items:center;height:100vh;}
        .box{background:white;padding:30px;border-radius:10px;width:350px;}
        input{width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;}
        button{width:100%;padding:10px;background:#0a192f;color:white;border:none;cursor:pointer;}
        .error{color:red;margin-bottom:10px;}
    </style>
</head>
<body>
    <div class="box">
        <h2>Test Login</h2>
        <div style="background:#e0f2fe;padding:10px;margin-bottom:15px;">
            Email: admin@onlinetheft.com<br>
            Password: admin123
        </div>
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>