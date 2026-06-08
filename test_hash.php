<?php
echo "PHP Version: " . phpversion() . "<br>";

if (function_exists('password_hash')) {
    echo "✅ password_hash() function exists<br>";
    
    $test_password = "admin123";
    $hash = password_hash($test_password, PASSWORD_DEFAULT);
    echo "Test hash created: " . $hash . "<br>";
    
    if (password_verify($test_password, $hash)) {
        echo "✅ password_verify() works correctly!<br>";
    } else {
        echo "❌ password_verify() failed!<br>";
    }
} else {
    echo "❌ password_hash() function does NOT exist!<br>";
    echo "Please upgrade PHP to version 5.5 or higher.<br>";
}
?>