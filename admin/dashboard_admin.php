<?php
session_start();
require_once '../config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// Get statistics
$total_calon = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='calon_karyawan'"))['total'];
$total_diterima = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penilaian WHERE status='Lulus'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PT Maju Mundur</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo">
                <h2>PT Maju Mundur</h2>
            </div>
            <ul class="nav-menu">
                <li class="active"><a href="dashboard_admin.php">🏠 Dashboard</a></li>
                <li><a href="tampilkan_karyawan.php">👥 Tampilkan Karyawan</a></li>
                <li><a href="upload_nilai.php">📝 Upload Nilai</a></li>
                <li><a href="kelola_lowongan.php">💼 Kelola Lowongan</a></li>
                <li><a href="kelola_periode.php">📅 Kelola Periode</a></li>
            </ul>
            <div class="nav-footer">
                <a href="../logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Dashboard Admin</h1>
                <div class="user-info">
                    <span class="user-role">Admin</span>
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </header>
            
            <div class="content">
                <div class="welcome-card">
                    <h2>Selamat Datang di PT Maju Mundur! 👋</h2>
                    <p>Sistem Manajemen SDM - Admin Panel</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">🏢</div>
                        <div class="stat-info">
                            <h3>Nama Perusahaan</h3>
                            <p class="stat-number">PT Maju Mundur</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-info">
                            <h3>Jumlah Calon Karyawan</h3>
                            <p class="stat-number"><?php echo $total_calon; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <h3>Karyawan Diterima</h3>
                            <p class="stat-number"><?php echo $total_diterima; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>