<?php
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

$pendidikan_query = "SELECT * FROM pendidikan WHERE id_biodata = '$id_biodata'";
$pendidikan_result = mysqli_query($conn, $pendidikan_query);

$pk_query = "SELECT * FROM pengalaman_kerja WHERE id_biodata = '$id_biodata'";
$pk_result = mysqli_query($conn, $pk_query);

$lowongan_query = "SELECT pl.*, l.posisi, l.tgl_interview, l.tgl_tkd 
                   FROM pemilihan_lowongan pl 
                   JOIN lowongan l ON pl.id_lowongan = l.id_lowongan 
                   WHERE pl.id_biodata = '$id_biodata'";
$lowongan_result = mysqli_query($conn, $lowongan_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Karyawan - PT Maju Mundur</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Detail Calon Karyawan</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard_admin.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="tampilkan_karyawan.php">Karyawan</a></li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <a href="tampilkan_karyawan.php" class="btn btn-secondary mb-3">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <!-- Data Identitas -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-id-card mr-2"></i>Data Identitas</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Username</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($biodata['username']); ?></dd>
                            
                            <dt class="col-sm-3">NIK</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($biodata['nik'] ?? '-'); ?></dd>
                            
                            <dt class="col-sm-3">Nama Lengkap</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($biodata['nama']); ?></dd>
                            
                            <dt class="col-sm-3">Tempat, Tanggal Lahir</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($biodata['ttl']); ?></dd>
                        </dl>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-map-marker-alt mr-2"></i>Alamat Lengkap</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Alamat:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($biodata['alamat'])); ?></p>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>Provinsi:</strong> <?php echo htmlspecialchars($biodata['provinsi'] ?? '-'); ?></p>
                                <p><strong>Kota/Kabupaten:</strong> <?php echo htmlspecialchars($biodata['kota_kabupaten'] ?? '-'); ?></p>
                                <p><strong>Kecamatan:</strong> <?php echo htmlspecialchars($biodata['kecamatan'] ?? '-'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Kelurahan/Desa:</strong> <?php echo htmlspecialchars($biodata['kelurahan_desa'] ?? '-'); ?></p>
                                <p><strong>RT/RW:</strong> <?php echo htmlspecialchars($biodata['rt_rw'] ?? '-'); ?></p>
                                <p><strong>Kode Pos:</strong> <?php echo htmlspecialchars($biodata['kode_pos'] ?? '-'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Pribadi -->
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user mr-2"></i>Data Pribadi</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Jenis Kelamin</dt>
                            <dd class="col-sm-9"><?php echo $biodata['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></dd>
                            
                            <dt class="col-sm-3">Golongan Darah</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($biodata['golongan_darah'] ?? '-'); ?></dd>
                            
                            <dt class="col-sm-3">Status Perkawinan</dt>
                            <dd class="col-sm-9">
                                <?php 
                                $status = [
                                    'BM' => 'Belum Menikah',
                                    'M' => 'Menikah',
                                    'K' => 'Kawin'
                                ];
                                echo $status[$biodata['status']] ?? '-';
                                ?>
                            </dd>
                            
                            <dt class="col-sm-3">Pekerjaan Saat Ini</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($biodata['pekerjaan'] ?? '-'); ?></dd>
                            
                            <dt class="col-sm-3">Agama</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($biodata['agama']); ?></dd>
                            
                            <dt class="col-sm-3">Kewarganegaraan</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($biodata['kewarganegaraan'] ?? '-'); ?></dd>
                            
                            <dt class="col-sm-3">Email</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($biodata['email']); ?></dd>
                            
                            <dt class="col-sm-3">No. HP</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($biodata['no_hp']); ?></dd>
                        </dl>
                    </div>
                </div>

                <!-- Pendidikan -->
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-graduation-cap mr-2"></i>Riwayat Pendidikan</h3>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($pendidikan_result) > 0): ?>
                        <table class="table table-bordered">
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
                        <?php else: ?>
                        <p class="text-muted">Belum ada data pendidikan.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pengalaman Kerja -->
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-briefcase mr-2"></i>Pengalaman Kerja</h3>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($pk_result) > 0): ?>
                        <table class="table table-bordered">
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
                        <?php else: ?>
                        <p class="text-muted">Belum ada data pengalaman kerja.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lowongan -->
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Lowongan Yang Dipilih</h3>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($lowongan_result) > 0): ?>
                        <table class="table table-bordered">
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
                                    <td><span class="badge badge-success"><i class="fas fa-check"></i> Terdaftar</span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-muted">Belum memilih lowongan.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>