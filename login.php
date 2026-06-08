<?php
// Only start session once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// If already logged in, redirect to appropriate page
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['full_name'];
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['role'] = strtolower($row['role']); // ensure lowercase

                // Redirect based on role
                if ($_SESSION['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "Email not found!";
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body.auth-page {
            background: #f5f7fa;
            font-family: 'Inter', sans-serif;
        }
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .auth-container {

           padding-top: 100px;
        }
        
        .auth-card {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-header h2 {
            color: #0a192f;
        }
        .auth-header p {
            color: #8892b0;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #0a192f;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
        }
        .btn-login {
            width: 100%;
            background: #64ffda;
            color: #0a192f;
            padding: 0.75rem;
            border: none;
            border-radius: 40px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-login:hover {
            background: #00c4a7;
        }
        .error-message {
            background: #ff6b6b;
            color: white;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
        }
        .auth-footer a {
            color: #64ffda;
            text-decoration: none;
        }
    </style>
</head>
<body class="auth-page">
    <?php include 'header.php'; ?>


    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Welcome Back</h2>
                <p>Login to your account</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
            <div class="auth-footer">
                <p>Don't have an account? <a href="signup.php">Sign up</a></p>
            </div>

        </div>
    </div>
    

 <?php include 'footer.php'; ?>
</body>
</html>