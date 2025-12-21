<?php
// create_admin.php
require_once 'config.php';

$username = 'administrator'; // Or any username you prefer
$password = 'admin123'; // Change this
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'admin')";

if (mysqli_query($conn, $query)) {
    echo "Admin user created successfully!<br>";
    echo "Username: <strong>$username</strong><br>";
    echo "Password: <strong>$password</strong><br>";
    echo "<a href='login.php'>Go to Login</a>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>