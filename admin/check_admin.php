<?php
// check_admin.php
require_once 'config.php';

// Get admin password hash from database
$query = "SELECT password FROM users WHERE username = 'admin' AND role = 'admin'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

echo "Current admin password hash:<br>";
echo "<code>" . $row['password'] . "</code><br><br>";

// Test if a password matches
$test_password = 'admin123'; // Try different passwords here
if (password_verify($test_password, $row['password'])) {
    echo "Password '$test_password' is CORRECT!";
} else {
    echo "Password '$test_password' is incorrect.";
}
?>