<?php
// reset_admin.php
require_once 'config.php';

// Set new admin password
$new_password = 'admin123'; // Change this to your desired password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update admin password
$update_query = "UPDATE users SET password = '$hashed_password' WHERE username = 'admin'";

if (mysqli_query($conn, $update_query)) {
    echo "Password reset successful!<br>";
    echo "New password for admin is: <strong>$new_password</strong><br>";
    echo "Please change this password immediately after login!<br>";
    echo "<a href='login.php'>Go to Login</a>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>