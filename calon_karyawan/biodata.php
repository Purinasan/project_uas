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
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $ttl = mysqli_real_escape_string($conn, $_POST['ttl']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $provinsi = mysqli_real_escape_string($conn, $_POST['provinsi']);
    $kota_kabupaten = mysqli_real_escape_string($conn, $_POST['kota_kabupaten']);
    $kecamatan = mysqli_real_escape_string($conn, $_POST['kecamatan']);
    $kelurahan_desa = mysqli_real_escape_string($conn, $_POST['kelurahan_desa']);
    $rt_rw = mysqli_real_escape_string($conn, $_POST['rt_rw']);
    $kode_pos = mysqli_real_escape_string($conn, $_POST['kode_pos']);
    $jk = $_POST['jenis_kelamin'];
    $golongan_darah = $_POST['golongan_darah'];
    $status = $_POST['status'];
    $pekerjaan = mysqli_real_escape_string($conn, $_POST['pekerjaan']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $agama = mysqli_real_escape_string($conn, $_POST['agama']);
    $kewarganegaraan = mysqli_real_escape_string($conn, $_POST['kewarganegaraan']);
    
    if ($biodata) {
        // Update
        $update_query = "UPDATE biodata SET 
            nik='$nik',
            nama='$nama', 
            ttl='$ttl', 
            alamat='$alamat',
            provinsi='$provinsi',
            kota_kabupaten='$kota_kabupaten',
            kecamatan='$kecamatan',
            kelurahan_desa='$kelurahan_desa',
            rt_rw='$rt_rw',
            kode_pos='$kode_pos',
            jenis_kelamin='$jk',
            golongan_darah='$golongan_darah',
            status='$status',
            pekerjaan='$pekerjaan',
            no_hp='$no_hp', 
            email='$email', 
            agama='$agama',
            kewarganegaraan='$kewarganegaraan',
            status_akun=1 
            WHERE id_user=$user_id";
        if (mysqli_query($conn, $update_query)) {
            $success = "Biodata berhasil diupdate!";
        }
    } else {
        // Insert new
        $id_biodata = "BIO" . str_pad($user_id, 7, "0", STR_PAD_LEFT);
        $insert_query = "INSERT INTO biodata (
            id_biodata, nik, nama, ttl, alamat, provinsi, kota_kabupaten, kecamatan, 
            kelurahan_desa, rt_rw, kode_pos, jenis_kelamin, golongan_darah, status, 
            pekerjaan, no_hp, email, agama, kewarganegaraan, status_akun, id_user
        ) VALUES (
            '$id_biodata', '$nik', '$nama', '$ttl', '$alamat', '$provinsi', 
            '$kota_kabupaten', '$kecamatan', '$kelurahan_desa', '$rt_rw', '$kode_pos',
            '$jk', '$golongan_darah', '$status', '$pekerjaan', '$no_hp', '$email', 
            '$agama', '$kewarganegaraan', 1, $user_id
        )";
        if (mysqli_query($conn, $insert_query)) {
            $success = "Biodata berhasil disimpan!";
            header("refresh:1");
        }
    }
}

// Handle pendidikan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pendidikan'])) {
    $id_pendidikan = "PDK" . substr(time(), -7);
    $jenjang = $_POST['jenjang'];
    $nama_sekolah = mysqli_real_escape_string($conn, $_POST['nama_sekolah']);
    $tahun_masuk = $_POST['tahun_masuk'];
    $tahun_lulus = $_POST['tahun_lulus'];
    
    $insert = "INSERT INTO pendidikan (id_pendidikan, id_biodata, jenjang, nama_sekolah, tahun_masuk, tahun_lulus) 
               VALUES ('$id_pendidikan', '{$biodata['id_biodata']}', '$jenjang', '$nama_sekolah', '$tahun_masuk', '$tahun_lulus')";
    if (mysqli_query($conn, $insert)) {
        header("Location: biodata.php");
    }
}

// Handle pengalaman kerja
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pk'])) {
    $nama_perusahaan = mysqli_real_escape_string($conn, $_POST['nama_perusahaan']);
    $posisi = mysqli_real_escape_string($conn, $_POST['posisi']);
    $jenis = $_POST['jenis'];
    $mulai = $_POST['mulai'];
    $selesai = $_POST['selesai'];
    
    $insert = "INSERT INTO pengalaman_kerja (nama_perusahaan, posisi, jenis, mulai, selesai, id_biodata) 
               VALUES ('$nama_perusahaan', '$posisi', '$jenis', '$mulai', '$selesai', '{$biodata['id_biodata']}')";
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
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; 
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn-submit { 
            padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: 600; 
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .table-container { background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        .btn-add { padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 15px; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 30px; width: 80%; max-width: 600px; border-radius: 10px; max-height: 80vh; overflow-y: auto; }
        .close { float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .section-divider { 
            margin: 30px 0 20px 0; padding: 10px 0; 
            border-bottom: 2px solid #667eea; color: #667eea; font-weight: 600; font-size: 18px; 
        }
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
                    <h2 style="margin-bottom: 25px;">📝 Form Biodata Lengkap</h2>
                    <form method="POST">
                        <!-- Data Identitas -->
                        <div class="section-divider">🆔 Data Identitas</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>NIK (Nomor Induk Kependudukan) *</label>
                                <input type="text" name="nik" required value="<?php echo $biodata['nik'] ?? ''; ?>" placeholder="16 digit NIK" maxlength="16">
                            </div>
                            <div class="form-group">
                                <label>Nama Lengkap *</label>
                                <input type="text" name="nama" required value="<?php echo $biodata['nama'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Tempat, Tanggal Lahir *</label>
                            <input type="text" name="ttl" required value="<?php echo $biodata['ttl'] ?? ''; ?>" placeholder="Jakarta, 01 Januari 2000">
                        </div>
                        
                        <!-- Alamat Lengkap -->
                        <div class="section-divider">📍 Alamat Lengkap</div>
                        
                        <div class="form-group">
                            <label>Alamat Lengkap *</label>
                            <textarea name="alamat" required><?php echo $biodata['alamat'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Provinsi *</label>
                                <input type="text" name="provinsi" required value="<?php echo $biodata['provinsi'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Kota/Kabupaten *</label>
                                <input type="text" name="kota_kabupaten" required value="<?php echo $biodata['kota_kabupaten'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Kecamatan *</label>
                                <input type="text" name="kecamatan" required value="<?php echo $biodata['kecamatan'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Kelurahan/Desa *</label>
                                <input type="text" name="kelurahan_desa" required value="<?php echo $biodata['kelurahan_desa'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>RT/RW *</label>
                                <input type="text" name="rt_rw" required value="<?php echo $biodata['rt_rw'] ?? ''; ?>" placeholder="001/002">
                            </div>
                            <div class="form-group">
                                <label>Kode Pos *</label>
                                <input type="text" name="kode_pos" required value="<?php echo $biodata['kode_pos'] ?? ''; ?>" placeholder="60111" maxlength="5">
                            </div>
                        </div>
                        
                        <!-- Data Pribadi -->
                        <div class="section-divider">👤 Data Pribadi</div>
                        
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
                                <label>Golongan Darah *</label>
                                <select name="golongan_darah" required>
                                    <option value="">Pilih</option>
                                    <option value="A" <?php echo ($biodata['golongan_darah'] ?? '') == 'A' ? 'selected' : ''; ?>>A</option>
                                    <option value="B" <?php echo ($biodata['golongan_darah'] ?? '') == 'B' ? 'selected' : ''; ?>>B</option>
                                    <option value="AB" <?php echo ($biodata['golongan_darah'] ?? '') == 'AB' ? 'selected' : ''; ?>>AB</option>
                                    <option value="O" <?php echo ($biodata['golongan_darah'] ?? '') == 'O' ? 'selected' : ''; ?>>O</option>
                                    <option value="Tidak Tahu" <?php echo ($biodata['golongan_darah'] ?? '') == 'Tidak Tahu' ? 'selected' : ''; ?>>Tidak Tahu</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Status Perkawinan *</label>
                                <select name="status" required>
                                    <option value="">Pilih</option>
                                    <option value="BM" <?php echo ($biodata['status'] ?? '') == 'BM' ? 'selected' : ''; ?>>Belum Menikah</option>
                                    <option value="K" <?php echo ($biodata['status'] ?? '') == 'K' ? 'selected' : ''; ?>>Kawin</option>
                                    <option value="M" <?php echo ($biodata['status'] ?? '') == 'M' ? 'selected' : ''; ?>>Menikah</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Pekerjaan Saat Ini</label>
                                <input type="text" name="pekerjaan" value="<?php echo $biodata['pekerjaan'] ?? ''; ?>" placeholder="Kosongkan jika belum bekerja">
                            </div>
                        </div>
                        
                        <!-- Kontak & Lainnya -->
                        <div class="section-divider">📞 Kontak & Lainnya</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>No. HP *</label>
                                <input type="text" name="no_hp" required value="<?php echo $biodata['no_hp'] ?? ''; ?>" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" required value="<?php echo $biodata['email'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Agama *</label>
                                <input type="text" name="agama" required value="<?php echo $biodata['agama'] ?? ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Kewarganegaraan *</label>
                                <input type="text" name="kewarganegaraan" required value="<?php echo $biodata['kewarganegaraan'] ?? 'Indonesia'; ?>">
                            </div>
                        </div>
                        
                        <button type="submit" name="submit_biodata" class="btn-submit">💾 Simpan Biodata</button>
                    </form>
                </div>
                
                <?php if ($biodata): ?>
                <!-- Pendidikan -->
                <div class="table-container">
                    <h3>🎓 Riwayat Pendidikan</h3>
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
                    <h3>💼 Pengalaman Kerja</h3>
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
                        <option value="S2">S2</option>
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
    
    <script>
        window.onclick = function(event) {
            var modalPendidikan = document.getElementById('modalPendidikan');
            var modalPK = document.getElementById('modalPK');
            if (event.target == modalPendidikan) {
                modalPendidikan.style.display = "none";
            }
            if (event.target == modalPK) {
                modalPK.style.display = "none";
            }
        }
    </script>
</body>
</html>