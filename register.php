<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Check if username already exists
        $check_query = "SELECT * FROM users WHERE username = '$username'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Check if email already exists
            $check_email = "SELECT * FROM users WHERE username = '$email'";
            $check_email_result = mysqli_query($conn, $check_email);
            
            if (mysqli_num_rows($check_email_result) > 0) {
                $error = "Email sudah digunakan!";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user (default role: calon_karyawan)
                $insert_query = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'calon_karyawan')";
                
                if (mysqli_query($conn, $insert_query)) {
                    $success = "Registrasi berhasil! Silakan login.";
                    // Optional: Auto redirect after 2 seconds
                    // header("refresh:2;url=login.php");
                } else {
                    $error = "Terjadi kesalahan: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - PT Maju Mundur</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="left-content">
                <h1>WELCOME<br>BACK!</h1>
                <p>Welcome to PT Maju Mundur</p>
            </div>
        </div>
        
        <div class="right-panel">
            <div class="register-box">
                <h2>Sign Up</h2>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success-message">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="input-group">
                        <label>Username</label>
                        <div class="input-wrapper">
                            <input type="text" name="username" placeholder="Enter your username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            <span class="input-icon">👤</span>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>Email</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <span class="input-icon">✉️</span>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="password" placeholder="••••••••" required>
                            <span class="input-icon">🔒</span>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>Confirm Password</label>
                        <div class="input-wrapper">
                            <input type="password" name="confirm_password" placeholder="••••••••" required>
                            <span class="input-icon">🔒</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-signup">Sign Up</button>
                    
                    <div class="login-link">
                        Already have an account? <a href="login.php">Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>