<?php
// kelola_lowongan.php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];
$success = "";
$error = "";

// Get all lowongan
$query = "SELECT l.*, p.nama_periode 
          FROM lowongan l 
          LEFT JOIN periode p ON l.id_periode = p.id_periode 
          ORDER BY l.tgl_buka DESC";
$result = mysqli_query($conn, $query);

// Get all periode for dropdown
$periode_query = "SELECT * FROM periode ORDER BY id_periode DESC";
$periode_result = mysqli_query($conn, $periode_query);

// Handle add lowongan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_lowongan'])) {
    $posisi = mysqli_real_escape_string($conn, $_POST['posisi']);
    $persyaratan = mysqli_real_escape_string($conn, $_POST['persyaratan']);
    $tgl_buka = $_POST['tgl_buka'];
    $tgl_tutup = $_POST['tgl_tutup'];
    $tgl_interview = $_POST['tgl_interview'];
    $tgl_tkd = $_POST['tgl_tkd'];
    $pengumuman_hasil = $_POST['pengumuman_hasil'];
    $id_periode = $_POST['id_periode'];
    
    $insert = "INSERT INTO lowongan (id_periode, posisi, persyaratan, tgl_buka, tgl_tutup, tgl_interview, tgl_tkd, pengumuman_hasil) 
               VALUES ('$id_periode', '$posisi', '$persyaratan', '$tgl_buka', '$tgl_tutup', '$tgl_interview', '$tgl_tkd', '$pengumuman_hasil')";
    
    if (mysqli_query($conn, $insert)) {
        $success = "Lowongan berhasil ditambahkan!";
        header("refresh:1");
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Handle delete lowongan
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete = "DELETE FROM lowongan WHERE id_lowongan = $id";
    if (mysqli_query($conn, $delete)) {
        $success = "Lowongan berhasil dihapus!";
        header("Location: kelola_lowongan.php");
        exit();
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
    <title>Kelola Lowongan - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .table-container { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600; }
        .btn-action { padding: 5px 12px; text-decoration: none; border-radius: 5px; font-size: 12px; margin-right: 5px; }
        .btn-edit { background: #28a745; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-add { padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; display: inline-block; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 30px; width: 90%; max-width: 600px; border-radius: 10px; }
        .close { float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group textarea { min-height: 100px; resize: vertical; }
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
                <h1>Kelola Lowongan</h1>
                <div class="user-info">
                    <span class="user-role">Admin</span>
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
                
                <a href="javascript:void(0)" class="btn-add" onclick="document.getElementById('addModal').style.display='block'">+ Tambah Lowongan</a>
                
                <div class="table-container">
                    <h3 style="margin-bottom: 20px;">Daftar Lowongan</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Posisi</th>
                                <th>Periode</th>
                                <th>Tanggal Buka</th>
                                <th>Tanggal Tutup</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            mysqli_data_seek($result, 0);
                            while ($row = mysqli_fetch_assoc($result)): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['posisi']); ?></td>
                                <td><?php echo $row['nama_periode'] ?? '-'; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tgl_buka'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['tgl_tutup'])); ?></td>
                                <td>
                                    <a href="edit_lowongan.php?id=<?php echo $row['id_lowongan']; ?>" class="btn-action btn-edit">Edit</a>
                                    <a href="kelola_lowongan.php?delete=<?php echo $row['id_lowongan']; ?>" class="btn-action btn-delete" onclick="return confirm('Hapus lowongan ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal Tambah Lowongan -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
            <h3 style="margin-bottom: 20px;">Tambah Lowongan Baru</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Posisi *</label>
                    <input type="text" name="posisi" required placeholder="Contoh: Software Engineer">
                </div>
                
                <div class="form-group">
                    <label>Periode</label>
                    <select name="id_periode" required>
                        <option value="">Pilih Periode</option>
                        <?php while ($periode = mysqli_fetch_assoc($periode_result)): ?>
                        <option value="<?php echo $periode['id_periode']; ?>"><?php echo $periode['nama_periode']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Persyaratan *</label>
                    <textarea name="persyaratan" required placeholder="Masukkan persyaratan lowongan..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Tanggal Buka *</label>
                    <input type="date" name="tgl_buka" required>
                </div>
                
                <div class="form-group">
                    <label>Tanggal Tutup *</label>
                    <input type="date" name="tgl_tutup" required>
                </div>
                
                <div class="form-group">
                    <label>Tanggal Interview *</label>
                    <input type="date" name="tgl_interview" required>
                </div>
                
                <div class="form-group">
                    <label>Tanggal TKD *</label>
                    <input type="date" name="tgl_tkd" required>
                </div>
                
                <div class="form-group">
                    <label>Tanggal Pengumuman Hasil *</label>
                    <input type="date" name="pengumuman_hasil" required>
                </div>
                
                <button type="submit" name="add_lowongan" class="btn-add" style="width: 100%; padding: 12px; margin-top: 10px;">Simpan Lowongan</button>
            </form>
        </div>
    </div>
    
    <script>
        window.onclick = function(event) {
            var modal = document.getElementById('addModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>