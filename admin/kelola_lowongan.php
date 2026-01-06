<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];
$success = "";
$error = "";

$query = "SELECT l.*, p.nama_periode 
          FROM lowongan l 
          LEFT JOIN periode p ON l.id_periode = p.id_periode 
          ORDER BY l.tgl_buka DESC";
$result = mysqli_query($conn, $query);

$periode_query = "SELECT * FROM periode ORDER BY id_periode DESC";
$periode_result = mysqli_query($conn, $periode_query);

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

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete = "DELETE FROM lowongan WHERE id_lowongan = $id";
    if (mysqli_query($conn, $delete)) {
        $success = "Lowongan berhasil dihapus!";
        header("Location: kelola_lowongan.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Lowongan - PT Maju Mundur</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
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
                        <h1 class="m-0">Kelola Lowongan</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard_admin.php">Home</a></li>
                            <li class="breadcrumb-item active">Kelola Lowongan</li>
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

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-briefcase mr-2"></i>Daftar Lowongan</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                                <i class="fas fa-plus"></i> Tambah Lowongan
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="lowonganTable" class="table table-bordered table-striped">
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
                                        <a href="edit_lowongan.php?id=<?php echo $row['id_lowongan']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="kelola_lowongan.php?delete=<?php echo $row['id_lowongan']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus lowongan ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<!-- Modal Tambah Lowongan -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">Tambah Lowongan Baru</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Posisi <span class="text-danger">*</span></label>
                        <input type="text" name="posisi" class="form-control" required placeholder="Contoh: Software Engineer">
                    </div>
                    
                    <div class="form-group">
                        <label>Periode <span class="text-danger">*</span></label>
                        <select name="id_periode" class="form-control" required>
                            <option value="">Pilih Periode</option>
                            <?php 
                            mysqli_data_seek($periode_result, 0);
                            while ($periode = mysqli_fetch_assoc($periode_result)): 
                            ?>
                            <option value="<?php echo $periode['id_periode']; ?>"><?php echo $periode['nama_periode']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Persyaratan <span class="text-danger">*</span></label>
                        <textarea name="persyaratan" class="form-control" rows="4" required placeholder="Masukkan persyaratan lowongan..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Buka <span class="text-danger">*</span></label>
                                <input type="date" name="tgl_buka" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Tutup <span class="text-danger">*</span></label>
                                <input type="date" name="tgl_tutup" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Interview <span class="text-danger">*</span></label>
                                <input type="date" name="tgl_interview" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal TKD <span class="text-danger">*</span></label>
                                <input type="date" name="tgl_tkd" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal Pengumuman Hasil <span class="text-danger">*</span></label>
                        <input type="date" name="pengumuman_hasil" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="add_lowongan" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Lowongan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $('#lowonganTable').DataTable({
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
        }
    });
});
</script>
</body>
</html>