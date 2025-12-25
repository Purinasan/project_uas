<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'calon_karyawan') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get or create biodata
$biodata_query = "SELECT * FROM biodata WHERE id_user = $user_id";
$biodata_result = mysqli_query($conn, $biodata_query);
$biodata = mysqli_fetch_assoc($biodata_result);

// Get pendidikan
if ($biodata) {
    $pendidikan_query = "SELECT * FROM pendidikan WHERE id_biodata = '{$biodata['id_biodata']}'";
    $pendidikan_result = mysqli_query($conn, $pendidikan_query);

    // Get pengalaman kerja
    $pk_query = "SELECT * FROM pengalaman_kerja WHERE id_biodata = '{$biodata['id_biodata']}'";
    $pk_result = mysqli_query($conn, $pk_query);
}

$success = "";
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_biodata'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $ttl = mysqli_real_escape_string($conn, $_POST['ttl']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $jk = $_POST['jenis_kelamin'];
    $status = $_POST['status'];
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $agama = mysqli_real_escape_string($conn, $_POST['agama']);
    
    if ($biodata) {
        // Update
        $update_query = "UPDATE biodata SET nama='$nama', ttl='$ttl', alamat='$alamat', jenis_kelamin='$jk', status='$status', no_hp='$no_hp', email='$email', agama='$agama', status_akun=1 WHERE id_user=$user_id";
        if (mysqli_query($conn, $update_query)) {
            $success = "Biodata berhasil diupdate!";
        }
    } else {
        // Insert new
        $id_biodata = "BIO" . str_pad($user_id, 7, "0", STR_PAD_LEFT);
        $insert_query = "INSERT INTO biodata (id_biodata, nama, ttl, alamat, jenis_kelamin, status, no_hp, email, agama, status_akun, id_user) VALUES ('$id_biodata', '$nama', '$ttl', '$alamat', '$jk', '$status', '$no_hp', '$email', '$agama', 1, $user_id)";
        if (mysqli_query($conn, $insert_query)) {
            $success = "Biodata berhasil disimpan!";
            header("refresh:1");
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pendidikan'])) {
    // Generate shorter ID: PDK + last 7 digits of timestamp
    $id_pendidikan = "PDK" . substr(time(), -7);
    $jenjang = $_POST['jenjang'];
    $nama_sekolah = mysqli_real_escape_string($conn, $_POST['nama_sekolah']);
    $tahun_masuk = $_POST['tahun_masuk'];
    $tahun_lulus = $_POST['tahun_lulus'];
    
    $insert = "INSERT INTO pendidikan (id_pendidikan, id_biodata, jenjang, nama_sekolah, tahun_masuk, tahun_lulus) VALUES ('$id_pendidikan', '{$biodata['id_biodata']}', '$jenjang', '$nama_sekolah', '$tahun_masuk', '$tahun_lulus')";
    if (mysqli_query($conn, $insert)) {
        header("Location: biodata.php");
        exit();
    }
}
// Handle pengalaman kerja
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pk'])) {
    $nama_perusahaan = mysqli_real_escape_string($conn, $_POST['nama_perusahaan']);
    $posisi = mysqli_real_escape_string($conn, $_POST['posisi']);
    $jenis = $_POST['jenis'];
    $mulai = $_POST['mulai'];
    $selesai = $_POST['selesai'];
    
    $insert = "INSERT INTO pengalaman_kerja (nama_perusahaan, posisi, jenis, mulai, selesai, id_biodata) VALUES ('$nama_perusahaan', '$posisi', '$jenis', '$mulai', '$selesai', '{$biodata['id_biodata']}')";
    if (mysqli_query($conn, $insert)) {
        header("Location: biodata.php");
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biodata - Calon Karyawan</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .form-biodata { background: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn-submit { padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: 600; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .table-container { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        .btn-add { padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 15px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 30px; width: 80%; max-width: 600px; border-radius: 10px; }
        .close { float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo"><h2>PT Maju Mundur</h2></div>
            <ul class="nav-menu">
                <li><a href="dashboard_karyawan.php">🏠 Dashboard</a></li>
                <li class="active"><a href="biodata.php">📋 Biodata</a></li>
                <li><a href="pemilihan_lowongan.php">💼 Pemilihan Lowongan</a></li>
                <li><a href="penilaian.php">📊 Penilaian</a></li>
            </ul>
            <div class="nav-footer">
                <a href="../logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Biodata</h1>
                <div class="user-info">
                    <span class="user-role">Calon Karyawan</span>
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </header>
            
            <div class="content">
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="form-biodata">
                    <h2 style="margin-bottom: 25px;">Form Biodata</h2>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Lengkap *</label>
                                <input type="text" name="nama" required value="<?php echo $biodata['nama'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Tempat, Tanggal Lahir *</label>
                                <input type="text" name="ttl" required value="<?php echo $biodata['ttl'] ?? ''; ?>" placeholder="Jakarta, 01 Januari 2000">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Alamat *</label>
                            <textarea name="alamat" required><?php echo $biodata['alamat'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jenis Kelamin *</label>
                                <select name="jenis_kelamin" required>
                                    <option value="">Pilih</option>
                                    <option value="L" <?php echo ($biodata['jenis_kelamin'] ?? '') == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="P" <?php echo ($biodata['jenis_kelamin'] ?? '') == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status *</label>
                                <select name="status" required>
                                    <option value="">Pilih</option>
                                    <option value="BM" <?php echo ($biodata['status'] ?? '') == 'BM' ? 'selected' : ''; ?>>Belum Menikah</option>
                                    <option value="M" <?php echo ($biodata['status'] ?? '') == 'M' ? 'selected' : ''; ?>>Menikah</option>
                                    <option value="K" <?php echo ($biodata['status'] ?? '') == 'K' ? 'selected' : ''; ?>>Kawin</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>No. HP *</label>
                                <input type="text" name="no_hp" required value="<?php echo $biodata['no_hp'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" required value="<?php echo $biodata['email'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Agama *</label>
                            <input type="text" name="agama" required value="<?php echo $biodata['agama'] ?? ''; ?>">
                        </div>
                        
                        <button type="submit" name="submit_biodata" class="btn-submit">💾 Simpan Biodata</button>
                    </form>
                </div>
                
                <?php if ($biodata): ?>
                <!-- Pendidikan -->
                <div class="table-container">
                    <h3>Riwayat Pendidikan</h3>
                    <button class="btn-add" onclick="document.getElementById('modalPendidikan').style.display='block'">+ Tambah Pendidikan</button>
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
                            <?php while ($row = mysqli_fetch_assoc($pendidikan_result)): ?>
                            <tr>
                                <td><?php echo $row['jenjang']; ?></td>
                                <td><?php echo $row['nama_sekolah']; ?></td>
                                <td><?php echo $row['tahun_masuk']; ?></td>
                                <td><?php echo $row['tahun_lulus']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pengalaman Kerja -->
                <div class="table-container">
                    <h3>Pengalaman Kerja</h3>
                    <button class="btn-add" onclick="document.getElementById('modalPK').style.display='block'">+ Tambah Pengalaman</button>
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
                            <?php while ($row = mysqli_fetch_assoc($pk_result)): ?>
                            <tr>
                                <td><?php echo $row['nama_perusahaan']; ?></td>
                                <td><?php echo $row['posisi']; ?></td>
                                <td><?php echo $row['jenis']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['mulai'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['selesai'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Modal Pendidikan -->
    <div id="modalPendidikan" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('modalPendidikan').style.display='none'">&times;</span>
            <h3>Tambah Pendidikan</h3>
            <form method="POST" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Jenjang</label>
                    <select name="jenjang" required>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                        <option value="D3">D3</option>
                        <option value="S1">S1</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nama Sekolah</label>
                    <input type="text" name="nama_sekolah" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tahun Masuk</label>
                        <input type="number" name="tahun_masuk" required min="1950" max="2030">
                    </div>
                    <div class="form-group">
                        <label>Tahun Lulus</label>
                        <input type="number" name="tahun_lulus" required min="1950" max="2030">
                    </div>
                </div>
                <button type="submit" name="add_pendidikan" class="btn-submit">Simpan</button>
            </form>
        </div>
    </div>
    
    <!-- Modal Pengalaman Kerja -->
    <div id="modalPK" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('modalPK').style.display='none'">&times;</span>
            <h3>Tambah Pengalaman Kerja</h3>
            <form method="POST" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Nama Perusahaan</label>
                    <input type="text" name="nama_perusahaan" required>
                </div>
                <div class="form-group">
                    <label>Posisi</label>
                    <input type="text" name="posisi" required>
                </div>
                <div class="form-group">
                    <label>Jenis</label>
                    <select name="jenis" required>
                        <option value="PK">PK</option>
                        <option value="Non PK">Non PK</option>
                        <option value="Magang">Magang</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Mulai</label>
                        <input type="date" name="mulai" required>
                    </div>
                    <div class="form-group">
                        <label>Selesai</label>
                        <input type="date" name="selesai" required>
                    </div>
                </div>
                <button type="submit" name="add_pk" class="btn-submit">Simpan</button>
            </form>
        </div>
    </div>
</body>
</html>