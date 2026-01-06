<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'calon_karyawan') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$biodata_query = "SELECT * FROM biodata WHERE id_user = $user_id";
$biodata_result = mysqli_query($conn, $biodata_query);
$biodata = mysqli_fetch_assoc($biodata_result);

$rt = '';
$rw = '';
if ($biodata && !empty($biodata['rt_rw'])) {
    $rt_rw_array = explode('/', $biodata['rt_rw']);
    $rt = $rt_rw_array[0] ?? '';
    $rw = $rt_rw_array[1] ?? '';
}

if ($biodata) {
    $pendidikan_query = "SELECT * FROM pendidikan WHERE id_biodata = '{$biodata['id_biodata']}'";
    $pendidikan_result = mysqli_query($conn, $pendidikan_query);

    $pk_query = "SELECT * FROM pengalaman_kerja WHERE id_biodata = '{$biodata['id_biodata']}'";
    $pk_result = mysqli_query($conn, $pk_query);
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_biodata'])) {
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $ttl = mysqli_real_escape_string($conn, $_POST['ttl']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $provinsi = mysqli_real_escape_string($conn, $_POST['provinsi']);
    $kota_kabupaten = mysqli_real_escape_string($conn, $_POST['kota_kabupaten']);
    $kecamatan = mysqli_real_escape_string($conn, $_POST['kecamatan']);
    $kelurahan_desa = mysqli_real_escape_string($conn, $_POST['kelurahan_desa']);
    
    $rt_input = mysqli_real_escape_string($conn, $_POST['rt']);
    $rw_input = mysqli_real_escape_string($conn, $_POST['rw']);
    $rt_rw = $rt_input . '/' . $rw_input;
    
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
            $rt = $rt_input;
            $rw = $rw_input;
        }
    } else {
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
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biodata - Calon Karyawan</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="../assets/adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/adminlte/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1><i class="fas fa-id-card"></i> Biodata</h1>
            </div>
        </section>
        
        <section class="content">
            <div class="container-fluid">
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title"><i class="fas fa-edit"></i> Form Biodata Lengkap</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <!-- Data Identitas -->
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-user"></i> Data Identitas
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>NIK (Nomor Induk Kependudukan) *</label>
                                        <input type="text" name="nik" class="form-control" required 
                                               value="<?php echo $biodata['nik'] ?? ''; ?>" 
                                               placeholder="16 digit NIK" maxlength="16">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nama Lengkap *</label>
                                        <input type="text" name="nama" class="form-control" required 
                                               value="<?php echo $biodata['nama'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Tempat, Tanggal Lahir *</label>
                                <input type="text" name="ttl" class="form-control" required 
                                       value="<?php echo $biodata['ttl'] ?? ''; ?>" 
                                       placeholder="Jakarta, 01 Januari 2000">
                            </div>
                            
                            <!-- Alamat Lengkap -->
                            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">
                                <i class="fas fa-map-marker-alt"></i> Alamat Lengkap
                            </h5>
                            
                            <div class="form-group">
                                <label>Alamat Lengkap *</label>
                                <textarea name="alamat" class="form-control" rows="3" required><?php echo $biodata['alamat'] ?? ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Provinsi *</label>
                                        <input type="text" name="provinsi" class="form-control" required 
                                               value="<?php echo $biodata['provinsi'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kota/Kabupaten *</label>
                                        <input type="text" name="kota_kabupaten" class="form-control" required 
                                               value="<?php echo $biodata['kota_kabupaten'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kecamatan *</label>
                                        <input type="text" name="kecamatan" class="form-control" required 
                                               value="<?php echo $biodata['kecamatan'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kelurahan/Desa *</label>
                                        <input type="text" name="kelurahan_desa" class="form-control" required 
                                               value="<?php echo $biodata['kelurahan_desa'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>RT *</label>
                                        <input type="text" name="rt" class="form-control" required 
                                               value="<?php echo $rt; ?>" 
                                               placeholder="001" maxlength="3">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>RW *</label>
                                        <input type="text" name="rw" class="form-control" required 
                                               value="<?php echo $rw; ?>" 
                                               placeholder="002" maxlength="3">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kode Pos *</label>
                                        <input type="text" name="kode_pos" class="form-control" required 
                                               value="<?php echo $biodata['kode_pos'] ?? ''; ?>" 
                                               placeholder="60111" maxlength="5">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Data Pribadi -->
                            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">
                                <i class="fas fa-info-circle"></i> Data Pribadi
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jenis Kelamin *</label>
                                        <select name="jenis_kelamin" class="form-control" required>
                                            <option value="">Pilih</option>
                                            <option value="L" <?php echo ($biodata['jenis_kelamin'] ?? '') == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="P" <?php echo ($biodata['jenis_kelamin'] ?? '') == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Golongan Darah *</label>
                                        <select name="golongan_darah" class="form-control" required>
                                            <option value="">Pilih</option>
                                            <option value="A" <?php echo ($biodata['golongan_darah'] ?? '') == 'A' ? 'selected' : ''; ?>>A</option>
                                            <option value="B" <?php echo ($biodata['golongan_darah'] ?? '') == 'B' ? 'selected' : ''; ?>>B</option>
                                            <option value="AB" <?php echo ($biodata['golongan_darah'] ?? '') == 'AB' ? 'selected' : ''; ?>>AB</option>
                                            <option value="O" <?php echo ($biodata['golongan_darah'] ?? '') == 'O' ? 'selected' : ''; ?>>O</option>
                                            <option value="Tidak Tahu" <?php echo ($biodata['golongan_darah'] ?? '') == 'Tidak Tahu' ? 'selected' : ''; ?>>Tidak Tahu</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status Perkawinan *</label>
                                        <select name="status" class="form-control" required>
                                            <option value="">Pilih</option>
                                            <option value="BM" <?php echo ($biodata['status'] ?? '') == 'BM' ? 'selected' : ''; ?>>Belum Menikah</option>
                                            <option value="M" <?php echo ($biodata['status'] ?? '') == 'M' ? 'selected' : ''; ?>>Menikah</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Pekerjaan Saat Ini</label>
                                        <input type="text" name="pekerjaan" class="form-control" 
                                               value="<?php echo $biodata['pekerjaan'] ?? ''; ?>" 
                                               placeholder="Kosongkan jika belum bekerja">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Kontak & Lainnya -->
                            <h5 class="text-primary border-bottom pb-2 mb-3 mt-4">
                                <i class="fas fa-phone"></i> Kontak & Lainnya
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. HP *</label>
                                        <input type="text" name="no_hp" class="form-control" required 
                                               value="<?php echo $biodata['no_hp'] ?? ''; ?>" 
                                               placeholder="08xxxxxxxxxx">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email *</label>
                                        <input type="email" name="email" class="form-control" required 
                                               value="<?php echo $biodata['email'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Agama *</label>
                                        <input type="text" name="agama" class="form-control" required 
                                               value="<?php echo $biodata['agama'] ?? ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kewarganegaraan *</label>
                                        <input type="text" name="kewarganegaraan" class="form-control" required 
                                               value="<?php echo $biodata['kewarganegaraan'] ?? 'Indonesia'; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" name="submit_biodata" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Biodata
                            </button>
                        </form>
                    </div>
                </div>
                
                <?php if ($biodata): ?>
                <!-- Pendidikan -->
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Riwayat Pendidikan</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#modalPendidikan">
                                <i class="fas fa-plus"></i> Tambah Pendidikan
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Jenjang</th>
                                    <th>Nama Sekolah</th>
                                    <th>Tahun Masuk</th>
                                    <th>Tahun Lulus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (mysqli_num_rows($pendidikan_result) > 0):
                                    while ($row = mysqli_fetch_assoc($pendidikan_result)): 
                                ?>
                                <tr>
                                    <td><?php echo $row['jenjang']; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_sekolah']); ?></td>
                                    <td><?php echo $row['tahun_masuk']; ?></td>
                                    <td><?php echo $row['tahun_lulus']; ?></td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada data pendidikan</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pengalaman Kerja -->
                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title"><i class="fas fa-briefcase"></i> Pengalaman Kerja</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#modalPK">
                                <i class="fas fa-plus"></i> Tambah Pengalaman
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
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
                                <?php 
                                if (mysqli_num_rows($pk_result) > 0):
                                    while ($row = mysqli_fetch_assoc($pk_result)): 
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nama_perusahaan']); ?></td>
                                    <td><?php echo htmlspecialchars($row['posisi']); ?></td>
                                    <td><?php echo $row['jenis']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['mulai'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['selesai'])); ?></td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data pengalaman kerja</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </section>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</div>

<!-- Modal Pendidikan -->
<div class="modal fade" id="modalPendidikan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">Tambah Pendidikan</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Jenjang *</label>
                        <select name="jenjang" class="form-control" required>
                            <option value="">Pilih Jenjang</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA/SMK</option>
                            <option value="D3">D3</option>
                            <option value="S1">S1</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Sekolah/Universitas *</label>
                        <input type="text" name="nama_sekolah" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tahun Masuk *</label>
                                <input type="number" name="tahun_masuk" class="form-control" required min="1950" max="2030">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tahun Lulus *</label>
                                <input type="number" name="tahun_lulus" class="form-control" required min="1950" max="2030">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" name="add_pendidikan" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pengalaman Kerja -->
<div class="modal fade" id="modalPK">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title">Tambah Pengalaman Kerja</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Perusahaan *</label>
                        <input type="text" name="nama_perusahaan" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Posisi *</label>
                        <input type="text" name="posisi" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis *</label>
                        <select name="jenis" class="form-control" required>
                            <option value="">Pilih Jenis</option>
                            <option value="PK">Karyawan Tetap (PK)</option>
                            <option value="Non PK">Karyawan Kontrak (Non PK)</option>
                            <option value="Magang">Magang</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mulai *</label>
                                <input type="date" name="mulai" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Selesai *</label>
                                <input type="date" name="selesai" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" name="add_pk" class="btn btn-info">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/adminlte/dist/js/adminlte.min.js"></script>
</body>
</html>