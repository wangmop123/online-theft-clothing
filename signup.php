<?php
session_start();
include 'config.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $password  = $_POST['password'];

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Email already registered!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (full_name, email, password, role) VALUES ('$full_name', '$email', '$hashed', 'user')";
            if (mysqli_query($conn, $sql)) {
                // ✅ NO auto-login here – just redirect to login page
                header('Location: login.php?signup=success');
                exit();
            } else {
                $error = "Registration failed! Please try again.";
            }
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <?php include 'header.php'; ?>

    <div class="auth-container" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f5f7fa; padding-top: 100px;">
        <div class="auth-card" style="background: white; padding: 2.5rem; border-radius: 20px; width: 100%; max-width: 450px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="auth-header" style="text-align: center; margin-bottom: 2rem;">
                <h2 style="color: #0a192f;">Create Account</h2>
                <p style="color: #8892b0;">Join the theft community</p>
            </div>

            <?php if ($error): ?>
                <div style="background: #ff6b6b; color: white; padding: 0.75rem; border-radius: 10px; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #0a192f;">Full Name</label>
                    <input type="text" name="full_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 10px;">
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #0a192f;">Email</label>
                    <input type="email" name="email" required style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 10px;">
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #0a192f;">Password</label>
                    <input type="password" name="password" required style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 10px;">
                </div>
                <button type="submit" style="width: 100%; background: #64ffda; color: #0a192f; padding: 0.75rem; border: none; border-radius: 40px; font-weight: bold; font-size: 1rem; cursor: pointer;">Sign Up</button>
            </form>
            <div class="auth-footer" style="text-align: center; margin-top: 1.5rem;">
                <p>Already have an account? <a href="login.php" style="color: #64ffda;">Login</a></p>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>