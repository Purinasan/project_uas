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

if (!$biodata) {
    header("Location: biodata.php");
    exit();
}

// Get active lowongan with periode
$lowongan_query = "SELECT l.*, p.nama_periode FROM lowongan l 
                   LEFT JOIN periode p ON l.id_periode = p.id_periode 
                   WHERE p.status = 'Aktif' 
                   ORDER BY l.tgl_buka DESC";
$lowongan_result = mysqli_query($conn, $lowongan_query);

// Get selected lowongan
$selected_query = "SELECT pl.*, l.posisi, l.tgl_interview, l.tgl_tkd 
                   FROM pemilihan_lowongan pl
                   JOIN lowongan l ON pl.id_lowongan = l.id_lowongan
                   WHERE pl.id_biodata = '{$biodata['id_biodata']}'";
$selected_result = mysqli_query($conn, $selected_query);

$success = "";
$error = "";

// Handle pilih lowongan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pilih_lowongan'])) {
    $id_lowongan = mysqli_real_escape_string($conn, $_POST['id_lowongan']);
    
    // Check if already selected
    $check = mysqli_query($conn, "SELECT * FROM pemilihan_lowongan WHERE id_biodata = '{$biodata['id_biodata']}' AND id_lowongan = $id_lowongan");
    if (mysqli_num_rows($check) > 0) {
        $error = "Anda sudah memilih lowongan ini!";
    } else {
        $insert = "INSERT INTO pemilihan_lowongan (id_biodata, id_lowongan) VALUES ('{$biodata['id_biodata']}', $id_lowongan)";
        if (mysqli_query($conn, $insert)) {
            $success = "Lowongan berhasil dipilih!";
            // Refresh the page to show updated data
            echo "<script>setTimeout(function(){ window.location.href = 'pemilihan_lowongan.php'; }, 1000);</script>";
        } else {
            $error = "Terjadi kesalahan: " . mysqli_error($conn);
        }
    }
}

// Re-fetch selected lowongan after potential insert
$selected_query = "SELECT pl.*, l.posisi, l.tgl_interview, l.tgl_tkd 
                   FROM pemilihan_lowongan pl
                   JOIN lowongan l ON pl.id_lowongan = l.id_lowongan
                   WHERE pl.id_biodata = '{$biodata['id_biodata']}'";
$selected_result = mysqli_query($conn, $selected_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemilihan Lowongan</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .lowongan-card { background: white; padding: 25px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #667eea; }
        .lowongan-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px; }
        .lowongan-header h3 { color: #333; margin: 0; }
        .periode-badge { background: #667eea; color: white; padding: 5px 15px; border-radius: 20px; font-size: 14px; }
        .lowongan-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 15px 0; }
        .info-item { display: flex; align-items: center; gap: 10px; color: #666; }
        .info-item strong { color: #333; }
        .btn-pilih { padding: 10px 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn-pilih:disabled { opacity: 0.5; cursor: not-allowed; }
        .table-container { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo"><h2>SDM System</h2></div>
            <ul class="nav-menu">
                <li><a href="dashboard_karyawan.php">🏠 Dashboard</a></li>
                <li><a href="biodata.php">📋 Biodata</a></li>
                <li class="active"><a href="pemilihan_lowongan.php">💼 Pemilihan Lowongan</a></li>
                <li><a href="penilaian.php">📊 Penilaian</a></li>
            </ul>
            <div class="nav-footer">
                <a href="../logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Pemilihan Lowongan</h1>
                <div class="user-info">
                    <span class="user-role">Calon Karyawan</span>
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </header>
            
            <div class="content">
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <h2 style="margin-bottom: 20px;">Lowongan Tersedia</h2>
                
                <?php if (mysqli_num_rows($lowongan_result) == 0): ?>
                    <div style="background: white; padding: 30px; text-align: center; border-radius: 10px;">
                        <p style="color: #666; font-size: 18px;">Belum ada lowongan yang tersedia saat ini</p>
                    </div>
                <?php else: ?>
                    <?php while ($lowongan = mysqli_fetch_assoc($lowongan_result)): ?>
                    <div class="lowongan-card">
                        <div class="lowongan-header">
                            <div>
                                <h3>💼 <?php echo $lowongan['posisi']; ?></h3>
                                <p style="color: #888; margin-top: 5px;"><?php echo $lowongan['nama_periode']; ?></p>
                            </div>
                            <span class="periode-badge">Aktif</span>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;">
                            <strong>Persyaratan:</strong>
                            <p style="margin-top: 8px; color: #555;"><?php echo nl2br($lowongan['persyaratan']); ?></p>
                        </div>
                        
                        <div class="lowongan-info">
                            <div class="info-item">
                                <span>📅</span>
                                <div>
                                    <strong>Tanggal Buka:</strong> <?php echo date('d/m/Y', strtotime($lowongan['tgl_buka'])); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <span>🔒</span>
                                <div>
                                    <strong>Tanggal Tutup:</strong> <?php echo date('d/m/Y', strtotime($lowongan['tgl_tutup'])); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <span>🎤</span>
                                <div>
                                    <strong>Interview:</strong> <?php echo date('d/m/Y', strtotime($lowongan['tgl_interview'])); ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <span>📝</span>
                                <div>
                                    <strong>TKD:</strong> <?php echo date('d/m/Y', strtotime($lowongan['tgl_tkd'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST" style="margin-top: 15px;" onsubmit="console.log('Form submitted');">
                            <input type="hidden" name="id_lowongan" value="<?php echo $lowongan['id_lowongan']; ?>">
                            <button type="submit" name="pilih_lowongan" class="btn-pilih">✅ Pilih Lowongan Ini</button>
                        </form>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
                
                <div class="table-container" style="margin-top: 40px;">
                    <h3 style="margin-bottom: 20px;">Lowongan Yang Dipilih</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Posisi</th>
                                <th>Tanggal Interview</th>
                                <th>Tanggal TKD</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($selected_result) == 0): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #888;">Belum ada lowongan yang dipilih</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = mysqli_fetch_assoc($selected_result)): ?>
                                <tr>
                                    <td><?php echo $row['posisi']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tgl_interview'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['tgl_tkd'])); ?></td>
                                    <td><span style="color: #28a745;">✓ Terdaftar</span></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>