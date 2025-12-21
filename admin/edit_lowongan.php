<?php
// edit_lowongan.php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: kelola_lowongan.php");
    exit();
}

$id = $_GET['id'];
$username = $_SESSION['username'];
$success = "";
$error = "";

// Get lowongan data
$query = "SELECT * FROM lowongan WHERE id_lowongan = $id";
$result = mysqli_query($conn, $query);
$lowongan = mysqli_fetch_assoc($result);

if (!$lowongan) {
    header("Location: kelola_lowongan.php");
    exit();
}

// Get all periode
$periode_query = "SELECT * FROM periode ORDER BY id_periode DESC";
$periode_result = mysqli_query($conn, $periode_query);

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_lowongan'])) {
    $posisi = mysqli_real_escape_string($conn, $_POST['posisi']);
    $persyaratan = mysqli_real_escape_string($conn, $_POST['persyaratan']);
    $tgl_buka = $_POST['tgl_buka'];
    $tgl_tutup = $_POST['tgl_tutup'];
    $tgl_interview = $_POST['tgl_interview'];
    $tgl_tkd = $_POST['tgl_tkd'];
    $pengumuman_hasil = $_POST['pengumuman_hasil'];
    $id_periode = $_POST['id_periode'];
    
    $update = "UPDATE lowongan SET 
               id_periode = '$id_periode',
               posisi = '$posisi',
               persyaratan = '$persyaratan',
               tgl_buka = '$tgl_buka',
               tgl_tutup = '$tgl_tutup',
               tgl_interview = '$tgl_interview',
               tgl_tkd = '$tgl_tkd',
               pengumuman_hasil = '$pengumuman_hasil'
               WHERE id_lowongan = $id";
    
    if (mysqli_query($conn, $update)) {
        $success = "Lowongan berhasil diupdate!";
        header("refresh:2;url=kelola_lowongan.php");
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lowongan - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .form-container { background: white; padding: 30px; border-radius: 10px; max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .btn-submit { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn-back { display: inline-block; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo"><h2>PT Maju Mundur</h2></div>
            <ul class="nav-menu">
                <li><a href="dashboard_admin.php">🏠 Dashboard</a></li>
                <li><a href="tampilkan_karyawan.php">👥 Tampilkan Karyawan</a></li>
                <li><a href="upload_nilai.php">📝 Upload Nilai</a></li>
                <li class="active"><a href="kelola_lowongan.php">💼 Kelola Lowongan</a></li>
                <li><a href="kelola_periode.php">📅 Kelola Periode</a></li>
            </ul>
            <div class="nav-footer">
                <a href="../logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Edit Lowongan</h1>
                <div class="user-info">
                    <span class="user-role">Admin</span>
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </header>
            
            <div class="content">
                <a href="kelola_lowongan.php" class="btn-back">← Kembali</a>
                
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="form-container">
                    <form method="POST">
                        <div class="form-group">
                            <label>Posisi *</label>
                            <input type="text" name="posisi" value="<?php echo htmlspecialchars($lowongan['posisi']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Periode</label>
                            <select name="id_periode" required>
                                <option value="">Pilih Periode</option>
                                <?php 
                                mysqli_data_seek($periode_result, 0);
                                while ($periode = mysqli_fetch_assoc($periode_result)): 
                                ?>
                                <option value="<?php echo $periode['id_periode']; ?>" <?php echo $lowongan['id_periode'] == $periode['id_periode'] ? 'selected' : ''; ?>>
                                    <?php echo $periode['nama_periode']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Persyaratan *</label>
                            <textarea name="persyaratan" required><?php echo htmlspecialchars($lowongan['persyaratan']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Tanggal Buka *</label>
                            <input type="date" name="tgl_buka" value="<?php echo $lowongan['tgl_buka']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Tanggal Tutup *</label>
                            <input type="date" name="tgl_tutup" value="<?php echo $lowongan['tgl_tutup']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Tanggal Interview *</label>
                            <input type="date" name="tgl_interview" value="<?php echo $lowongan['tgl_interview']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Tanggal TKD *</label>
                            <input type="date" name="tgl_tkd" value="<?php echo $lowongan['tgl_tkd']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Tanggal Pengumuman Hasil *</label>
                            <input type="date" name="pengumuman_hasil" value="<?php echo $lowongan['pengumuman_hasil']; ?>" required>
                        </div>
                        
                        <button type="submit" name="update_lowongan" class="btn-submit">Update Lowongan</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>