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

if (!$biodata) {
    header("Location: biodata.php");
    exit();
}

$success = "";
$error = "";

// Handle messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $success = "Lowongan berhasil dihapus! Anda bisa memilih lowongan lain.";
    } elseif ($_GET['msg'] == 'confirmed') {
        $success = "Pilihan lowongan berhasil dipermanenkan! Data Anda akan diproses oleh admin.";
    }
}

if (isset($_GET['error'])) {
    if ($_GET['error'] == 'delete_failed') {
        $error = "Gagal menghapus lowongan!";
    } elseif ($_GET['error'] == 'unauthorized') {
        $error = "Aksi tidak diizinkan!";
    } elseif ($_GET['error'] == 'has_assessment') {
        $error = "Tidak dapat menghapus lowongan yang sudah dinilai!";
    } elseif ($_GET['error'] == 'already_confirmed') {
        $error = "Lowongan sudah dipermanenkan dan tidak dapat diubah!";
    }
}

// Check existing selection
$check_existing = mysqli_query($conn, "SELECT pl.*, l.posisi, l.tgl_buka, l.tgl_tutup, 
                                       l.tgl_interview, l.tgl_tkd, l.persyaratan, 
                                       p.nama_periode, p.tahun_mulai, p.tahun_selesai
                                       FROM pemilihan_lowongan pl
                                       JOIN lowongan l ON pl.id_lowongan = l.id_lowongan
                                       LEFT JOIN periode p ON l.id_periode = p.id_periode
                                       WHERE pl.id_biodata = '{$biodata['id_biodata']}'");

$has_selected = mysqli_num_rows($check_existing) > 0;
$selected_lowongan = $has_selected ? mysqli_fetch_assoc($check_existing) : null;

// Check if selection is permanent
$is_permanent = $has_selected && $selected_lowongan['status_pilihan'] == 'permanen';

if (!$has_selected) {
    $lowongan_query = "SELECT l.*, p.nama_periode, p.tahun_mulai, p.tahun_selesai
                       FROM lowongan l 
                       LEFT JOIN periode p ON l.id_periode = p.id_periode 
                       WHERE p.status = 'Aktif' 
                       ORDER BY l.tgl_buka DESC";
    $lowongan_result = mysqli_query($conn, $lowongan_query);
}

// Handle job selection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pilih_lowongan'])) {
    $id_lowongan = mysqli_real_escape_string($conn, $_POST['id_lowongan']);

    if ($has_selected) {
        $error = "Anda sudah memilih lowongan! Hapus lowongan yang dipilih terlebih dahulu.";
    } else {
        $insert = "INSERT INTO pemilihan_lowongan (id_biodata, id_lowongan, status_pilihan) 
                   VALUES ('{$biodata['id_biodata']}', $id_lowongan, 'draft')";
        if (mysqli_query($conn, $insert)) {
            $success = "Lowongan berhasil dipilih!";
            echo "<script>setTimeout(function(){ window.location.href = 'pemilihan_lowongan.php'; }, 1500);</script>";
        } else {
            $error = "Terjadi kesalahan: " . mysqli_error($conn);
        }
    }
}

// Handle permanent confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_permanent'])) {
    $id_pilihan = (int)$_POST['id_pilihan'];
    
    // Verify ownership
    $verify = mysqli_query($conn, "SELECT pl.* FROM pemilihan_lowongan pl 
                                   WHERE pl.id_pilihan = $id_pilihan 
                                   AND pl.id_biodata = '{$biodata['id_biodata']}'");
    
    if (mysqli_num_rows($verify) > 0) {
        $row = mysqli_fetch_assoc($verify);
        
        if ($row['status_pilihan'] == 'permanen') {
            $error = "Lowongan sudah dipermanenkan sebelumnya!";
        } else {
            // Update to permanent and set biodata status to tersubmit
            $update = "UPDATE pemilihan_lowongan SET status_pilihan = 'permanen' 
                      WHERE id_pilihan = $id_pilihan";
            
            if (mysqli_query($conn, $update)) {
                // Also update biodata status to tersubmit (1)
                mysqli_query($conn, "UPDATE biodata SET status_akun = 1 WHERE id_biodata = '{$biodata['id_biodata']}'");
                
                header("Location: pemilihan_lowongan.php?msg=confirmed");
                exit();
            } else {
                $error = "Gagal mempermanenkan pilihan!";
            }
        }
    } else {
        $error = "Aksi tidak diizinkan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemilihan Lowongan - PT Maju Mundur</title>

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
                        <h1 class="m-0">Pemilihan Lowongan</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard_karyawan.php">Home</a></li>
                            <li class="breadcrumb-item active">Pemilihan Lowongan</li>
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

                <?php if ($has_selected): ?>
                    <div class="callout callout-info">
                        <h5><i class="icon fas fa-info-circle"></i> Informasi</h5>
                        <p>Anda hanya dapat memilih <strong>1 lowongan</strong>. Jika ingin mengubah pilihan, hapus lowongan ini terlebih dahulu<?php if (!$is_permanent): ?> atau pilih permanen untuk mengirimkan lamaran Anda<?php endif; ?>.</p>
                    </div>

                    <?php if ($is_permanent): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Status: Terdaftar Permanen</h5>
                        <p class="mb-0">Pilihan lowongan Anda telah dipermanenkan dan sedang diproses oleh admin. Anda tidak dapat mengubah pilihan ini.</p>
                    </div>
                    <?php endif; ?>

                    <div class="card card-<?php echo $is_permanent ? 'success' : 'warning'; ?> card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-<?php echo $is_permanent ? 'check-circle' : 'clock'; ?>"></i> 
                                <?php echo $is_permanent ? 'Lowongan Yang Dipilih (Permanen)' : 'Lowongan Yang Dipilih (Draft)'; ?>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h3 class="text-primary">
                                        <i class="fas fa-briefcase"></i> 
                                        <?php echo htmlspecialchars($selected_lowongan['posisi']); ?>
                                    </h3>

                                    <?php if ($selected_lowongan['nama_periode']): ?>
                                    <div class="mb-3">
                                        <span class="badge badge-info badge-lg" style="font-size: 1rem; padding: 8px 15px;">
                                            <i class="fas fa-calendar-alt"></i> 
                                            <?php echo htmlspecialchars($selected_lowongan['nama_periode']); ?> 
                                            (<?php echo $selected_lowongan['tahun_mulai']; ?>-<?php echo $selected_lowongan['tahun_selesai']; ?>)
                                        </span>
                                    </div>
                                    <?php endif; ?>

                                    <div class="callout callout-warning">
                                        <h6><i class="fas fa-clipboard-list"></i> Persyaratan:</h6>
                                        <p class="mb-0" style="white-space: pre-line;"><?php echo htmlspecialchars($selected_lowongan['persyaratan']); ?></p>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-sm-6">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-success">
                                                    <i class="fas fa-calendar-check"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Pendaftaran Buka</span>
                                                    <span class="info-box-number"><?php echo date('d M Y', strtotime($selected_lowongan['tgl_buka'])); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-danger">
                                                    <i class="fas fa-calendar-times"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Pendaftaran Tutup</span>
                                                    <span class="info-box-number"><?php echo date('d M Y', strtotime($selected_lowongan['tgl_tutup'])); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-primary">
                                                    <i class="fas fa-user-tie"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Interview</span>
                                                    <span class="info-box-number"><?php echo date('d M Y', strtotime($selected_lowongan['tgl_interview'])); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-info">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Tes TKD</span>
                                                    <span class="info-box-number"><?php echo date('d M Y', strtotime($selected_lowongan['tgl_tkd'])); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card bg-<?php echo $is_permanent ? 'success' : 'warning'; ?>">
                                        <div class="card-body text-center">
                                            <i class="fas fa-<?php echo $is_permanent ? 'check-circle' : 'clock'; ?> fa-5x mb-3"></i>
                                            <h4>Status: <?php echo $is_permanent ? 'Terdaftar' : 'Draft'; ?></h4>
                                            <p><?php echo $is_permanent ? 'Anda telah terdaftar untuk lowongan ini' : 'Klik "Pilih Permanen" untuk mengirim lamaran'; ?></p>
                                        </div>
                                    </div>
                                    
                                    <?php if (!$is_permanent): ?>
                                    <form method="POST" class="mb-2">
                                        <input type="hidden" name="id_pilihan" value="<?php echo $selected_lowongan['id_pilihan']; ?>">
                                        <button type="button" class="btn btn-success btn-lg btn-block" data-toggle="modal" data-target="#confirmModal">
                                            <i class="fas fa-check-double"></i> Pilih Permanen
                                        </button>
                                    </form>
                                    
                                    <button type="button" class="btn btn-danger btn-lg btn-block" data-toggle="modal" data-target="#deleteModal">
                                        <i class="fas fa-trash"></i> Hapus Pilihan
                                    </button>
                                    
                                    <p class="text-center text-muted mt-2">
                                        <small><i class="fas fa-info-circle"></i> Pilih permanen untuk mengirim lamaran atau hapus untuk memilih lowongan lain</small>
                                    </p>
                                    <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Pilihan Anda sudah permanen dan tidak dapat diubah
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="callout callout-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                        <p>Anda hanya dapat memilih <strong>1 lowongan</strong>. Pilih dengan hati-hati!</p>
                    </div>

                    <h4 class="mb-3"><i class="fas fa-list"></i> Lowongan Tersedia</h4>
                    
                    <?php if (!isset($lowongan_result) || mysqli_num_rows($lowongan_result) == 0): ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-inbox fa-5x text-muted mb-4"></i>
                                <h4 class="text-muted">Belum Ada Lowongan Tersedia</h4>
                                <p class="text-muted">Belum ada lowongan yang tersedia saat ini. Silakan cek kembali nanti.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php while ($lowongan = mysqli_fetch_assoc($lowongan_result)): ?>
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-briefcase"></i>
                                    <strong><?php echo htmlspecialchars($lowongan['posisi']); ?></strong>
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-success badge-lg">
                                        <i class="fas fa-check-circle"></i> Aktif
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-9">
                                        <?php if ($lowongan['nama_periode']): ?>
                                        <div class="alert alert-info mb-3">
                                            <h6 class="mb-1">
                                                <i class="fas fa-calendar-alt"></i> 
                                                <strong>Periode Rekrutmen</strong>
                                            </h6>
                                            <p class="mb-0">
                                                <?php echo htmlspecialchars($lowongan['nama_periode']); ?> 
                                                (<?php echo $lowongan['tahun_mulai']; ?> - <?php echo $lowongan['tahun_selesai']; ?>)
                                            </p>
                                        </div>
                                        <?php endif; ?>

                                        <div class="callout callout-warning">
                                            <h6><i class="fas fa-clipboard-list"></i> Persyaratan:</h6>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($lowongan['persyaratan'])); ?></p>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-6 col-sm-3">
                                                <div class="card bg-info text-white text-center mb-2" style="min-height: 100px; position: relative; overflow: hidden;">
                                                    <div class="card-body p-3" style="position: relative; z-index: 2;">
                                                        <p class="mb-1" style="font-size: 0.85rem; font-weight: 600;">Buka</p>
                                                        <h5 class="mb-0 font-weight-bold"><?php echo date('d M', strtotime($lowongan['tgl_buka'])); ?></h5>
                                                    </div>
                                                    <i class="fas fa-check-circle" style="position: absolute; right: -10px; bottom: -10px; font-size: 4rem; opacity: 0.2; z-index: 1;"></i>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-3">
                                                <div class="card bg-warning text-white text-center mb-2" style="min-height: 100px; position: relative; overflow: hidden;">
                                                    <div class="card-body p-3" style="position: relative; z-index: 2;">
                                                        <p class="mb-1" style="font-size: 0.85rem; font-weight: 600;">Tutup</p>
                                                        <h5 class="mb-0 font-weight-bold"><?php echo date('d M', strtotime($lowongan['tgl_tutup'])); ?></h5>
                                                    </div>
                                                    <i class="fas fa-times-circle" style="position: absolute; right: -10px; bottom: -10px; font-size: 4rem; opacity: 0.2; z-index: 1;"></i>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-3">
                                                <div class="card bg-primary text-white text-center mb-2" style="min-height: 100px; position: relative; overflow: hidden;">
                                                    <div class="card-body p-3" style="position: relative; z-index: 2;">
                                                        <p class="mb-1" style="font-size: 0.85rem; font-weight: 600;">Interview</p>
                                                        <h5 class="mb-0 font-weight-bold"><?php echo date('d M', strtotime($lowongan['tgl_interview'])); ?></h5>
                                                    </div>
                                                    <i class="fas fa-user-tie" style="position: absolute; right: -10px; bottom: -10px; font-size: 4rem; opacity: 0.2; z-index: 1;"></i>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-3">
                                                <div class="card bg-success text-white text-center mb-2" style="min-height: 100px; position: relative; overflow: hidden;">
                                                    <div class="card-body p-3" style="position: relative; z-index: 2;">
                                                        <p class="mb-1" style="font-size: 0.85rem; font-weight: 600;">TKD</p>
                                                        <h5 class="mb-0 font-weight-bold"><?php echo date('d M', strtotime($lowongan['tgl_tkd'])); ?></h5>
                                                    </div>
                                                    <i class="fas fa-edit" style="position: absolute; right: -10px; bottom: -10px; font-size: 4rem; opacity: 0.2; z-index: 1;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3 text-center d-flex align-items-center">
                                        <form method="POST" class="w-100">
                                            <input type="hidden" name="id_lowongan" value="<?php echo $lowongan['id_lowongan']; ?>">
                                            <button type="submit" name="pilih_lowongan" class="btn btn-primary btn-lg btn-block">
                                                <i class="fas fa-check-circle"></i> Pilih Lowongan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<!-- Modal Confirm Permanent -->
<?php if ($has_selected && !$is_permanent): ?>
<div class="modal fade" id="confirmModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id_pilihan" value="<?php echo $selected_lowongan['id_pilihan']; ?>">
                <div class="modal-header bg-success">
                    <h4 class="modal-title"><i class="fas fa-check-double"></i> Konfirmasi Pilihan Permanen</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Perhatian!</strong>
                    </div>
                    <p><strong>Apakah Anda yakin ingin mempermanenkan pilihan lowongan ini?</strong></p>
                    <p class="text-muted">Setelah dipermanenkan:</p>
                    <ul class="text-muted">
                        <li>Lamaran Anda akan dikirim ke admin</li>
                        <li>Anda TIDAK dapat mengubah pilihan lowongan</li>
                        <li>Anda TIDAK dapat menghapus pilihan ini</li>
                        <li>Status Anda akan berubah menjadi "Tersubmit"</li>
                    </ul>
                    <p class="text-danger"><strong>Pastikan data biodata Anda sudah lengkap dan benar!</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" name="confirm_permanent" class="btn btn-success">
                        <i class="fas fa-check-double"></i> Ya, Pilih Permanen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Delete -->
<?php if ($has_selected && !$is_permanent): ?>
<div class="modal fade" id="deleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Apakah Anda yakin ingin menghapus pilihan lowongan ini?</strong></p>
                <p class="text-muted">Setelah dihapus, Anda dapat memilih lowongan lain yang tersedia.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <a href="hapus_lowongan.php?id=<?php echo $selected_lowongan['id_pilihan']; ?>" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Ya, Hapus
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>