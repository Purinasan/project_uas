<?php
session_start();
require_once '../config.php';

// Check if user is calon_karyawan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'calon_karyawan') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Check if biodata exists
$biodata_query = "SELECT * FROM biodata WHERE id_user = $user_id";
$biodata_result = mysqli_query($conn, $biodata_query);
$has_biodata = mysqli_num_rows($biodata_result) > 0;

if ($has_biodata) {
    $biodata = mysqli_fetch_assoc($biodata_result);
    $status_akun = $biodata['status_akun'];
} else {
    $status_akun = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Calon Karyawan</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo">
                <h2>SDM System</h2>
            </div>
            <ul class="nav-menu">
                <li class="active"><a href="dashboard_karyawan.php">🏠 Dashboard</a></li>
                <li><a href="biodata.php">📋 Biodata</a></li>
                <li><a href="pemilihan_lowongan.php">💼 Pemilihan Lowongan</a></li>
                <li><a href="penilaian.php">📊 Penilaian</a></li>
            </ul>
            <div class="nav-footer">
                <a href="../logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Dashboard Calon Karyawan</h1>
                <div class="user-info">
                    <span class="user-role">Calon Karyawan</span>
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </header>
            
            <div class="content">
                <div class="welcome-card">
                    <h2>Selamat Datang, <?php echo htmlspecialchars($username); ?>! 👋</h2>
                    <p>Lengkapi biodata Anda untuk melanjutkan proses rekrutmen</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📝</div>
                        <div class="stat-info">
                            <h3>Status Biodata</h3>
                            <p class="stat-number">
                                <?php 
                                if ($status_akun == 0) echo "Belum Lengkap";
                                elseif ($status_akun == 1) echo "Tersubmit";
                                elseif ($status_akun == 2) echo "Divalidasi";
                                else echo "Belum Ada";
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">💼</div>
                        <div class="stat-info">
                            <h3>Lowongan Dipilih</h3>
                            <p class="stat-number">
                                <?php
                                if ($has_biodata) {
                                    $lowongan_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pemilihan_lowongan WHERE id_biodata = '{$biodata['id_biodata']}'"))['total'];
                                    echo $lowongan_count;
                                } else {
                                    echo "0";
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">🎯</div>
                        <div class="stat-info">
                            <h3>Status Penilaian</h3>
                            <p class="stat-number">-</p>
                        </div>
                    </div>
                </div>
                
                <?php if (!$has_biodata || $status_akun == 0): ?>
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; border-radius: 8px; margin-top: 20px;">
                    <h3 style="margin-bottom: 10px; color: #856404;">⚠️ Perhatian!</h3>
                    <p style="color: #856404; margin-bottom: 15px;">Silakan lengkapi biodata Anda terlebih dahulu sebelum memilih lowongan.</p>
                    <a href="biodata.php" style="display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 5px;">Lengkapi Biodata</a>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>