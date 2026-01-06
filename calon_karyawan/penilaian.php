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

if ($biodata) {
    $nilai_query = "SELECT p.*, pl.id_lowongan, l.posisi, l.id_periode,
                    per.nama_periode, per.tahun_mulai, per.tahun_selesai
                    FROM penilaian p
                    JOIN pemilihan_lowongan pl ON p.id_pilihan = pl.id_pilihan
                    JOIN lowongan l ON pl.id_lowongan = l.id_lowongan
                    LEFT JOIN periode per ON l.id_periode = per.id_periode
                    WHERE pl.id_biodata = '{$biodata['id_biodata']}'";
    $nilai_result = mysqli_query($conn, $nilai_query);
    if (!$nilai_result) {
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penilaian - PT Maju Mundur</title>

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
                        <h1 class="m-0">Hasil Penilaian</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard_karyawan.php">Home</a></li>
                            <li class="breadcrumb-item active">Penilaian</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php if (!$biodata || !$nilai_result || mysqli_num_rows($nilai_result) == 0): ?>
                    <!-- No Data Card -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-chart-bar fa-5x text-muted mb-4"></i>
                            <h4 class="text-muted">Belum Ada Penilaian Tersedia</h4>
                            <p class="text-muted">Penilaian akan muncul setelah Anda mengikuti tes dan wawancara</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php while ($nilai = mysqli_fetch_assoc($nilai_result)): ?>
                    <!-- Score Card -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-briefcase mr-2"></i>
                                <?php echo htmlspecialchars($nilai['posisi']); ?>
                            </h3>
                            <div class="card-tools">
                                <?php 
                                $status = $nilai['status'] ?? 'Belum Dinilai';
                                $badgeClass = 'secondary';
                                if ($status == 'Lulus') {
                                    $badgeClass = 'success';
                                } elseif ($status == 'Tidak Lulus') {
                                    $badgeClass = 'danger';
                                } else {
                                    $badgeClass = 'warning';
                                }
                                ?>
                                <span class="badge badge-<?php echo $badgeClass; ?> badge-lg px-3 py-2">
                                    <?php echo $status; ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Periode Information -->
                            <?php if ($nilai['nama_periode']): ?>
                            <div class="alert alert-info">
                                <h5><i class="fas fa-calendar-alt"></i> Periode Rekrutmen</h5>
                                <p class="mb-1"><strong><?php echo htmlspecialchars($nilai['nama_periode']); ?></strong></p>
                                <p class="mb-0">
                                    <i class="fas fa-clock"></i> 
                                    Tahun: <?php echo $nilai['tahun_mulai']; ?> - <?php echo $nilai['tahun_selesai']; ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="row">
                                <!-- Nilai TKD -->
                                <div class="col-md-6">
                                    <div class="info-box bg-gradient-primary">
                                        <span class="info-box-icon"><i class="fas fa-pen-fancy"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Nilai TKD</span>
                                            <span class="info-box-number" style="font-size: 2.5rem;">
                                                <?php echo $nilai['nilai_tkd'] ?? '-'; ?>
                                            </span>
                                            <span class="progress-description">
                                                Tes Kompetensi Dasar
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nilai Interview -->
                                <div class="col-md-6">
                                    <div class="info-box bg-gradient-info">
                                        <span class="info-box-icon"><i class="fas fa-comments"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Nilai Interview</span>
                                            <span class="info-box-number" style="font-size: 2.5rem;">
                                                <?php echo $nilai['nilai_interview'] ?? '-'; ?>
                                            </span>
                                            <span class="progress-description">
                                                Wawancara
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Pemberkasan -->
                            <div class="card card-secondary mt-3">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-folder-open mr-2"></i>Status Pemberkasan</h3>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <?php if ($nilai['status_pemberkasan'] == 'Lengkap'): ?>
                                                <i class="fas fa-check-circle fa-3x text-success"></i>
                                            <?php else: ?>
                                                <i class="fas fa-exclamation-circle fa-3x text-warning"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">
                                                <?php echo $nilai['status_pemberkasan'] ?? 'Belum Diperiksa'; ?>
                                            </h5>
                                            <p class="text-muted mb-0">
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
                            </div>

                            <!-- Status Message -->
                            <?php if ($nilai['status'] == 'Lulus'): ?>
                            <div class="alert alert-success alert-dismissible mt-3">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <h5><i class="icon fas fa-check-circle"></i> Selamat!</h5>
                                <p class="mb-0">
                                    Anda dinyatakan <strong>LULUS</strong> untuk posisi ini. 
                                    Silakan tunggu informasi selanjutnya dari tim HRD.
                                </p>
                            </div>
                            <?php elseif ($nilai['status'] == 'Tidak Lulus'): ?>
                            <div class="alert alert-danger alert-dismissible mt-3">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <h5><i class="icon fas fa-info-circle"></i> Informasi</h5>
                                <p class="mb-0">
                                    Mohon maaf, Anda belum berhasil pada seleksi kali ini. 
                                    Jangan berkecil hati, terus tingkatkan kemampuan Anda dan coba lagi di kesempatan berikutnya.
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
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