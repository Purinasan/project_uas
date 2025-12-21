<?php
// detail_karyawan.php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: tampilkan_karyawan.php");
    exit();
}

$id_biodata = mysqli_real_escape_string($conn, $_GET['id']);

// Get biodata details
$query = "SELECT b.*, u.username 
          FROM biodata b 
          JOIN users u ON b.id_user = u.id_user 
          WHERE b.id_biodata = '$id_biodata'";
$result = mysqli_query($conn, $query);
$biodata = mysqli_fetch_assoc($result);

if (!$biodata) {
    header("Location: tampilkan_karyawan.php");
    exit();
}

// Get pendidikan
$pendidikan_query = "SELECT * FROM pendidikan WHERE id_biodata = '$id_biodata'";
$pendidikan_result = mysqli_query($conn, $pendidikan_query);

// Get pengalaman kerja
$pk_query = "SELECT * FROM pengalaman_kerja WHERE id_biodata = '$id_biodata'";
$pk_result = mysqli_query($conn, $pk_query);

// Get lowongan yang dipilih
$lowongan_query = "SELECT pl.*, l.posisi, l.tgl_interview, l.tgl_tkd 
                   FROM pemilihan_lowongan pl 
                   JOIN lowongan l ON pl.id_lowongan = l.id_lowongan 
                   WHERE pl.id_biodata = '$id_biodata'";
$lowongan_result = mysqli_query($conn, $lowongan_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Karyawan - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .detail-container { background: white; padding: 30px; border-radius: 10px; }
        .info-card { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #667eea; }
        .info-row { display: grid; grid-template-columns: 150px 1fr; gap: 15px; margin-bottom: 10px; }
        .info-label { font-weight: 600; color: #333; }
        .info-value { color: #555; }
        .section-title { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; margin: 30px 0 20px 0; }
        .table-container { margin-top: 15px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #333; }
        .btn-back { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        .btn-back:hover { background: #764ba2; }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo"><h2>PT Maju Mundur</h2></div>
            <ul class="nav-menu">
                <li><a href="dashboard_admin.php">🏠 Dashboard</a></li>
                <li class="active"><a href="tampilkan_karyawan.php">👥 Tampilkan Karyawan</a></li>
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
                <h1>Detail Karyawan</h1>
                <div class="user-info">
                    <span class="user-role">Admin</span>
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </header>
            
            <div class="content">
                <a href="tampilkan_karyawan.php" class="btn-back">← Kembali</a>
                
                <div class="detail-container">
                    <h2 style="margin-bottom: 25px;">Data Calon Karyawan</h2>
                    
                    <!-- Biodata Section -->
                    <div class="info-card">
                        <h3>📋 Informasi Pribadi</h3>
                        <div class="info-row">
                            <div class="info-label">Username:</div>
                            <div class="info-value"><?php echo htmlspecialchars($biodata['username']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Nama Lengkap:</div>
                            <div class="info-value"><?php echo htmlspecialchars($biodata['nama']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Tempat, Tgl Lahir:</div>
                            <div class="info-value"><?php echo htmlspecialchars($biodata['ttl']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Jenis Kelamin:</div>
                            <div class="info-value"><?php echo $biodata['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Status:</div>
                            <div class="info-value">
                                <?php 
                                $status = [
                                    'BM' => 'Belum Menikah',
                                    'M' => 'Menikah',
                                    'K' => 'Kawin'
                                ];
                                echo $status[$biodata['status']] ?? '-';
                                ?>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Agama:</div>
                            <div class="info-value"><?php echo htmlspecialchars($biodata['agama']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value"><?php echo htmlspecialchars($biodata['email']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">No. HP:</div>
                            <div class="info-value"><?php echo htmlspecialchars($biodata['no_hp']); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Alamat:</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($biodata['alamat'])); ?></div>
                        </div>
                    </div>
                    
                    <!-- Pendidikan Section -->
                    <h3 class="section-title">🎓 Riwayat Pendidikan</h3>
                    <?php if (mysqli_num_rows($pendidikan_result) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Jenjang</th>
                                        <th>Nama Sekolah</th>
                                        <th>Tahun Masuk</th>
                                        <th>Tahun Lulus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pendidikan = mysqli_fetch_assoc($pendidikan_result)): ?>
                                    <tr>
                                        <td><?php echo $pendidikan['jenjang']; ?></td>
                                        <td><?php echo htmlspecialchars($pendidikan['nama_sekolah']); ?></td>
                                        <td><?php echo $pendidikan['tahun_masuk']; ?></td>
                                        <td><?php echo $pendidikan['tahun_lulus']; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="color: #888; padding: 15px; background: #f8f9fa; border-radius: 5px;">Belum ada data pendidikan.</p>
                    <?php endif; ?>
                    
                    <!-- Pengalaman Kerja Section -->
                    <h3 class="section-title">💼 Pengalaman Kerja</h3>
                    <?php if (mysqli_num_rows($pk_result) > 0): ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Perusahaan</th>
                                        <th>Posisi</th>
                                        <th>Jenis</th>
                                        <th>Mulai</th>
                                        <th>Selesai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pk = mysqli_fetch_assoc($pk_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pk['nama_perusahaan']); ?></td>
                                        <td><?php echo htmlspecialchars($pk['posisi']); ?></td>
                                        <td><?php echo $pk['jenis']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($pk['mulai'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($pk['selesai'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="color: #888; padding: 15px; background: #f8f9fa; border-radius: 5px;">Belum ada data pengalaman kerja.</p>
                    <?php endif; ?>
                    
                    <!-- Lowongan Dipilih Section -->
                    <h3 class="section-title">📝 Lowongan Yang Dipilih</h3>
                    <?php if (mysqli_num_rows($lowongan_result) > 0): ?>
                        <div class="table-container">
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
                                    <?php while ($lowongan = mysqli_fetch_assoc($lowongan_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($lowongan['posisi']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($lowongan['tgl_interview'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($lowongan['tgl_tkd'])); ?></td>
                                        <td><span style="color: #28a745;">✓ Terdaftar</span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="color: #888; padding: 15px; background: #f8f9fa; border-radius: 5px;">Belum memilih lowongan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>