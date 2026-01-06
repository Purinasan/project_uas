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

$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM lowongan WHERE id_periode = p.id_periode) as total_lowongan
          FROM periode p 
          ORDER BY p.id_periode DESC";
$result = mysqli_query($conn, $query);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_periode'])) {
    $nama_periode = mysqli_real_escape_string($conn, $_POST['nama_periode']);
    $tahun_mulai = $_POST['tahun_mulai'];
    $tahun_selesai = $_POST['tahun_selesai'];
    $status = $_POST['status'];

    if ($status == 'Aktif') {
        mysqli_query($conn, "UPDATE periode SET status = 'Non Aktif'");
    }
    
    $insert = "INSERT INTO periode (nama_periode, tahun_mulai, tahun_selesai, status) 
               VALUES ('$nama_periode', '$tahun_mulai', '$tahun_selesai', '$status')";
    
    if (mysqli_query($conn, $insert)) {
        $success = "Periode berhasil ditambahkan!";
        header("refresh:1");
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_periode'])) {
    $id_periode = $_POST['id_periode'];
    $nama_periode = mysqli_real_escape_string($conn, $_POST['nama_periode']);
    $tahun_mulai = $_POST['tahun_mulai'];
    $tahun_selesai = $_POST['tahun_selesai'];
    $status = $_POST['status'];
    
    if ($status == 'Aktif') {
        mysqli_query($conn, "UPDATE periode SET status = 'Non Aktif'");
    }
    
    $update = "UPDATE periode SET 
               nama_periode='$nama_periode', 
               tahun_mulai='$tahun_mulai', 
               tahun_selesai='$tahun_selesai', 
               status='$status' 
               WHERE id_periode=$id_periode";
    
    if (mysqli_query($conn, $update)) {
        $success = "Periode berhasil diupdate!";
        header("refresh:1");
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

if (isset($_GET['activate'])) {
    $id = (int)$_GET['activate'];
    // Deactivate all
    mysqli_query($conn, "UPDATE periode SET status = 'Non Aktif'");
    // Activate selected
    mysqli_query($conn, "UPDATE periode SET status = 'Aktif' WHERE id_periode = $id");
    $success = "Periode berhasil diaktifkan!";
    header("Location: kelola_periode.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM lowongan WHERE id_periode = $id");
    $check_result = mysqli_fetch_assoc($check);
    
    if ($check_result['total'] > 0) {
        $error = "Tidak dapat menghapus periode karena masih memiliki " . $check_result['total'] . " lowongan terkait!";
    } else {
        $delete = "DELETE FROM periode WHERE id_periode = $id";
        if (mysqli_query($conn, $delete)) {
            $success = "Periode berhasil dihapus!";
            header("Location: kelola_periode.php");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Periode - PT Maju Mundur</title>

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
                        <h1 class="m-0">Kelola Periode</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard_admin.php">Home</a></li>
                            <li class="breadcrumb-item active">Kelola Periode</li>
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
                        <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Daftar Periode Rekrutmen</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                                <i class="fas fa-plus"></i> Tambah Periode
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="periodeTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Periode</th>
                                    <th width="12%">Tahun Mulai</th>
                                    <th width="12%">Tahun Selesai</th>
                                    <th width="10%">Lowongan</th>
                                    <th width="12%">Status</th>
                                    <th width="18%">Aksi</th>
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
                                    <td><?php echo htmlspecialchars($row['nama_periode']); ?></td>
                                    <td class="text-center"><?php echo $row['tahun_mulai']; ?></td>
                                    <td class="text-center"><?php echo $row['tahun_selesai']; ?></td>
                                    <td class="text-center">
                                        <span class="badge badge-info"><?php echo $row['total_lowongan']; ?> Lowongan</span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'Aktif'): ?>
                                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aktif</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><i class="fas fa-times-circle"></i> Non Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'Non Aktif'): ?>
                                        <a href="kelola_periode.php?activate=<?php echo $row['id_periode']; ?>" 
                                           class="btn btn-sm btn-success" 
                                           onclick="return confirm('Aktifkan periode ini?')">
                                            <i class="fas fa-check"></i> Aktifkan
                                        </a>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-sm btn-warning" onclick='editPeriode(<?php echo json_encode($row); ?>)'>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        
                                        <a href="kelola_periode.php?delete=<?php echo $row['id_periode']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Hapus periode ini?')">
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

<!-- Modal Tambah Periode -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">Tambah Periode Baru</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Periode <span class="text-danger">*</span></label>
                        <input type="text" name="nama_periode" class="form-control" required 
                               placeholder="Contoh: Periode Rekrutmen 2025 - Batch 1">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tahun Mulai <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_mulai" class="form-control" required 
                                       min="2020" max="2050" value="<?php echo date('Y'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tahun Selesai <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_selesai" class="form-control" required 
                                       min="2020" max="2050" value="<?php echo date('Y') + 1; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Non Aktif">Non Aktif</option>
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Hanya satu periode yang bisa aktif pada satu waktu
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="add_periode" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Periode
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Periode -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id_periode" id="edit_id_periode">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title">Edit Periode</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Periode <span class="text-danger">*</span></label>
                        <input type="text" name="nama_periode" id="edit_nama_periode" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tahun Mulai <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_mulai" id="edit_tahun_mulai" class="form-control" required min="2020" max="2050">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tahun Selesai <span class="text-danger">*</span></label>
                                <input type="number" name="tahun_selesai" id="edit_tahun_selesai" class="form-control" required min="2020" max="2050">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" class="form-control" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Non Aktif">Non Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="update_periode" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Periode
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
    $('#periodeTable').DataTable({
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
        }
    });
});

function editPeriode(data) {
    $('#edit_id_periode').val(data.id_periode);
    $('#edit_nama_periode').val(data.nama_periode);
    $('#edit_tahun_mulai').val(data.tahun_mulai);
    $('#edit_tahun_selesai').val(data.tahun_selesai);
    $('#edit_status').val(data.status);
    $('#editModal').modal('show');
}
</script>
</body>
</html>