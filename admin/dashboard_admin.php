<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];


$total_calon = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='calon_karyawan'"))['total'];
$total_diterima = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penilaian WHERE status='Lulus'"))['total'];
$total_lowongan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM lowongan"))['total'];
$total_periode_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM periode WHERE status='Aktif'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - PT Maju Mundur</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php include 'includes/header.php'; ?>

    <?php include 'includes/sidebar.php'; ?>


    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Welcome Card -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-primary card-outline">
                            <div class="card-body box-profile">
                                <h3 class="profile-username text-center">Selamat Datang di Sistem Manajemen SDM</h3>
                                <p class="text-muted text-center">PT Maju Mundur - Admin Panel</p>
                                <hr>
                                <p class="text-center">Kelola data karyawan, lowongan, dan penilaian dengan mudah</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $total_calon; ?></h3>
                                <p>Calon Karyawan</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <a href="tampilkan_karyawan.php" class="small-box-footer">
                                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $total_diterima; ?></h3>
                                <p>Karyawan Diterima</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <a href="upload_nilai.php" class="small-box-footer">
                                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $total_lowongan; ?></h3>
                                <p>Total Lowongan</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <a href="kelola_lowongan.php" class="small-box-footer">
                                Kelola Lowongan <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?php echo $total_periode_aktif; ?></h3>
                                <p>Periode Aktif</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <a href="kelola_periode.php" class="small-box-footer">
                                Kelola Periode <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header border-0">
                                <h3 class="card-title">
                                    <i class="fas fa-user-plus mr-1"></i>
                                    Pendaftar Terbaru
                                </h3>
                            </div>
                            <div class="card-body table-responsive p-0" style="max-height: 300px;">
                                <table class="table table-striped table-valign-middle">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $recent_query = "SELECT b.nama, b.email, b.status_akun 
                                                        FROM biodata b 
                                                        ORDER BY b.id_biodata DESC 
                                                        LIMIT 5";
                                        $recent_result = mysqli_query($conn, $recent_query);
                                        
                                        if (mysqli_num_rows($recent_result) > 0):
                                            while ($row = mysqli_fetch_assoc($recent_result)):
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <?php
                                                $status = $row['status_akun'];
                                                if ($status == 0) {
                                                    echo '<span class="badge badge-warning">Belum Lengkap</span>';
                                                } elseif ($status == 1) {
                                                    echo '<span class="badge badge-info">Tersubmit</span>';
                                                } else {
                                                    echo '<span class="badge badge-success">Divalidasi</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Belum ada pendaftar</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header border-0">
                                <h3 class="card-title">
                                    <i class="fas fa-briefcase mr-1"></i>
                                    Lowongan Aktif
                                </h3>
                            </div>
                            <div class="card-body table-responsive p-0" style="max-height: 300px;">
                                <table class="table table-striped table-valign-middle">
                                    <thead>
                                        <tr>
                                            <th>Posisi</th>
                                            <th>Tanggal Tutup</th>
                                            <th>Pelamar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $lowongan_query = "SELECT l.id_lowongan, l.posisi, l.tgl_tutup,
                                                          (SELECT COUNT(*) FROM pemilihan_lowongan WHERE id_lowongan = l.id_lowongan) as total_pelamar
                                                          FROM lowongan l
                                                          JOIN periode p ON l.id_periode = p.id_periode
                                                          WHERE p.status = 'Aktif'
                                                          ORDER BY l.tgl_tutup ASC
                                                          LIMIT 5";
                                        $lowongan_result = mysqli_query($conn, $lowongan_query);
                                        
                                        if (mysqli_num_rows($lowongan_result) > 0):
                                            while ($row = mysqli_fetch_assoc($lowongan_result)):
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['posisi']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['tgl_tutup'])); ?></td>
                                            <td>
                                                <span class="badge badge-primary"><?php echo $row['total_pelamar']; ?> Pelamar</span>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Belum ada lowongan aktif</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>