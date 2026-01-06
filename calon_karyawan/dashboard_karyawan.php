<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'calon_karyawan') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

$biodata_query = "SELECT * FROM biodata WHERE id_user = $user_id";
$biodata_result = mysqli_query($conn, $biodata_query);
$has_biodata = mysqli_num_rows($biodata_result) > 0;

if ($has_biodata) {
    $biodata = mysqli_fetch_assoc($biodata_result);
    $status_akun = $biodata['status_akun'];
} else {
    $status_akun = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Calon Karyawan</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="../assets/adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../assets/adminlte/dist/css/adminlte.min.css">
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
                        <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <div class="card card-primary card-outline">
                    <div class="card-body">
                        <h3><i class="fas fa-hand-sparkles"></i> Selamat Datang, <?php echo htmlspecialchars($username); ?>!</h3>
                        <p class="mb-0">Lengkapi biodata Anda untuk melanjutkan proses rekrutmen</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>
                                    <?php 
                                    if ($status_akun == 0) echo "Belum Lengkap";
                                    elseif ($status_akun == 1) echo "Tersubmit";
                                    elseif ($status_akun == 2) echo "Divalidasi";
                                    else echo "Belum Ada";
                                    ?>
                                </h3>
                                <p>Status Biodata</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <a href="biodata.php" class="small-box-footer">
                                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>
                                    <?php
                                    if ($has_biodata) {
                                        $lowongan_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pemilihan_lowongan WHERE id_biodata = '{$biodata['id_biodata']}'"))['total'];
                                        echo $lowongan_count;
                                    } else {
                                        echo "0";
                                    }
                                    ?>
                                </h3>
                                <p>Lowongan Dipilih</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <a href="pemilihan_lowongan.php" class="small-box-footer">
                                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>-</h3>
                                <p>Status Penilaian</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <a href="penilaian.php" class="small-box-footer">
                                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if (!$has_biodata || $status_akun == 0): ?>
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                    Silakan lengkapi biodata Anda terlebih dahulu sebelum memilih lowongan.
                    <br><br>
                    <a href="biodata.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Lengkapi Biodata
                    </a>
                </div>
                <?php endif; ?>
                
            </div>
        </section>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</div>

<script src="../assets/adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/adminlte/dist/js/adminlte.min.js"></script>
</body>
</html>