<?php
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

$query = "SELECT * FROM lowongan WHERE id_lowongan = $id";
$result = mysqli_query($conn, $query);
$lowongan = mysqli_fetch_assoc($result);

if (!$lowongan) {
    header("Location: kelola_lowongan.php");
    exit();
}

$periode_query = "SELECT * FROM periode ORDER BY id_periode DESC";
$periode_result = mysqli_query($conn, $periode_query);

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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Lowongan - PT Maju Mundur</title>

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
                        <h1 class="m-0">Edit Lowongan</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard_admin.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="kelola_lowongan.php">Kelola Lowongan</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-check"></i> <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-ban"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Form Edit Lowongan</h3>
                    </div>
                    <form method="POST">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Posisi <span class="text-danger">*</span></label>
                                <input type="text" name="posisi" class="form-control" value="<?php echo htmlspecialchars($lowongan['posisi']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Periode <span class="text-danger">*</span></label>
                                <select name="id_periode" class="form-control" required>
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
                                <label>Persyaratan <span class="text-danger">*</span></label>
                                <textarea name="persyaratan" class="form-control" rows="4" required><?php echo htmlspecialchars($lowongan['persyaratan']); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Buka <span class="text-danger">*</span></label>
                                        <input type="date" name="tgl_buka" class="form-control" value="<?php echo $lowongan['tgl_buka']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Tutup <span class="text-danger">*</span></label>
                                        <input type="date" name="tgl_tutup" class="form-control" value="<?php echo $lowongan['tgl_tutup']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Interview <span class="text-danger">*</span></label>
                                        <input type="date" name="tgl_interview" class="form-control" value="<?php echo $lowongan['tgl_interview']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal TKD <span class="text-danger">*</span></label>
                                        <input type="date" name="tgl_tkd" class="form-control" value="<?php echo $lowongan['tgl_tkd']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Tanggal Pengumuman Hasil <span class="text-danger">*</span></label>
                                <input type="date" name="pengumuman_hasil" class="form-control" value="<?php echo $lowongan['pengumuman_hasil']; ?>" required>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="kelola_lowongan.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" name="update_lowongan" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Lowongan
                            </button>
                        </div>
                    </form>
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