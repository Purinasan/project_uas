<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'calon_karyawan') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get biodata
$biodata_query = "SELECT * FROM biodata WHERE id_user = $user_id";
$biodata_result = mysqli_query($conn, $biodata_query);
$biodata = mysqli_fetch_assoc($biodata_result);

// Get penilaian - only if biodata exists
if ($biodata) {
    $nilai_query = "SELECT p.*, pl.id_lowongan, l.posisi 
                    FROM penilaian p
                    JOIN pemilihan_lowongan pl ON p.id_pilihan = pl.id_pilihan
                    JOIN lowongan l ON pl.id_lowongan = l.id_lowongan
                    WHERE pl.id_biodata = '{$biodata['id_biodata']}'";
    $nilai_result = mysqli_query($conn, $nilai_query);
    if (!$nilai_result) {
        // Handle query error
        echo "Error: " . mysqli_error($conn);
        $nilai_result = null;
    }
} else {
    $nilai_result = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .score-card { background: white; padding: 30px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .score-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #f0f0f0; }
        .score-header h3 { margin: 0; color: #333; font-size: 1.5rem; }
        .status-badge { padding: 8px 20px; border-radius: 25px; font-weight: 600; font-size: 14px; }
        .status-lulus { background: #d4edda; color: #155724; }
        .status-tidak-lulus { background: #f8d7da; color: #721c24; }
        .status-belum { background: #fff3cd; color: #856404; }
        .score-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin: 20px 0; }
        .score-item { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 25px; border-radius: 10px; color: white; text-align: center; }
        .score-item h4 { margin: 0 0 10px 0; font-size: 14px; opacity: 0.9; }
        .score-value { font-size: 3rem; font-weight: bold; margin: 10px 0; }
        .pemberkasan { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 20px; }
        .pemberkasan h4 { margin-bottom: 15px; color: #333; }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo"><h2>PT Maju Mundur</h2></div>
            <ul class="nav-menu">
                <li><a href="dashboard_karyawan.php">🏠 Dashboard</a></li>
                <li><a href="biodata.php">📋 Biodata</a></li>
                <li><a href="pemilihan_lowongan.php">💼 Pemilihan Lowongan</a></li>
                <li class="active"><a href="penilaian.php">📊 Penilaian</a></li>
            </ul>
            <div class="nav-footer">
                <a href="../logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Penilaian</h1>
                <div class="user-info">
                    <span class="user-role">Calon Karyawan</span>
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </header>
            
            <div class="content">
                <h2 style="margin-bottom: 25px;">Hasil Penilaian Anda</h2>
                
                <?php if (!$biodata || !$nilai_result || mysqli_num_rows($nilai_result) == 0): ?>
                    <div style="background: white; padding: 40px; text-align: center; border-radius: 10px;">
                        <p style="font-size: 18px; color: #888; margin-bottom: 15px;">📊 Belum ada penilaian tersedia</p>
                        <p style="color: #999;">Penilaian akan muncul setelah Anda mengikuti tes dan wawancara</p>
                    </div>
                <?php else: ?>
                    <?php while ($nilai = mysqli_fetch_assoc($nilai_result)): ?>
                    <div class="score-card">
                        <div class="score-header">
                            <div>
                                <h3>💼 <?php echo $nilai['posisi']; ?></h3>
                                <p style="color: #888; margin-top: 5px;">Posisi yang dilamar</p>
                            </div>
                            <span class="status-badge status-<?php echo ($nilai['status'] == 'Lulus') ? 'lulus' : (($nilai['status'] == 'Tidak Lulus') ? 'tidak-lulus' : 'belum'); ?>">
                                <?php echo $nilai['status'] ?? 'Belum Dinilai'; ?>
                            </span>
                        </div>
                        
                        <div class="score-grid">
                            <div class="score-item">
                                <h4>Nilai TKD</h4>
                                <div class="score-value"><?php echo $nilai['nilai_tkd'] ?? '-'; ?></div>
                                <p style="margin: 0; opacity: 0.9; font-size: 14px;">Tes Kompetensi Dasar</p>
                            </div>
                            
                            <div class="score-item">
                                <h4>Nilai Interview</h4>
                                <div class="score-value"><?php echo $nilai['nilai_interview'] ?? '-'; ?></div>
                                <p style="margin: 0; opacity: 0.9; font-size: 14px;">Wawancara</p>
                            </div>
                        </div>
                        
                        <div class="pemberkasan">
                            <h4>📁 Status Pemberkasan</h4>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <span style="font-size: 2rem;">
                                    <?php echo ($nilai['status_pemberkasan'] == 'Lengkap') ? '✅' : '⚠️'; ?>
                                </span>
                                <div>
                                    <p style="margin: 0; font-weight: 600; font-size: 16px; color: #333;">
                                        <?php echo $nilai['status_pemberkasan'] ?? 'Belum Diperiksa'; ?>
                                    </p>
                                    <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">
                                        <?php 
                                        if ($nilai['status_pemberkasan'] == 'Lengkap') {
                                            echo 'Semua dokumen telah lengkap';
                                        } else {
                                            echo 'Mohon lengkapi dokumen yang dibutuhkan';
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($nilai['status'] == 'Lulus'): ?>
                        <div style="background: #d4edda; padding: 20px; border-radius: 10px; margin-top: 20px; border-left: 4px solid #28a745;">
                            <p style="margin: 0; color: #155724; font-weight: 600; font-size: 16px;">
                                🎉 Selamat! Anda dinyatakan LULUS untuk posisi ini.
                            </p>
                            <p style="margin: 10px 0 0 0; color: #155724;">
                                Silakan tunggu informasi selanjutnya dari tim HRD.
                            </p>
                        </div>
                        <?php elseif ($nilai['status'] == 'Tidak Lulus'): ?>
                        <div style="background: #f8d7da; padding: 20px; border-radius: 10px; margin-top: 20px; border-left: 4px solid #dc3545;">
                            <p style="margin: 0; color: #721c24; font-weight: 600; font-size: 16px;">
                                Mohon maaf, Anda belum berhasil pada seleksi kali ini.
                            </p>
                            <p style="margin: 10px 0 0 0; color: #721c24;">
                                Jangan berkecil hati, terus tingkatkan kemampuan Anda dan coba lagi di kesempatan berikutnya.
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>