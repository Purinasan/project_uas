<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

$query = "SELECT u.id_user, u.username, b.id_biodata, b.nama, b.email, b.no_hp, 
          b.status_akun, b.jenis_kelamin, b.ttl,
          (SELECT COUNT(*) FROM pemilihan_lowongan pl 
           WHERE pl.id_biodata = b.id_biodata AND pl.status_pilihan = 'permanen') as has_permanent_selection
          FROM users u
          LEFT JOIN biodata b ON u.id_user = b.id_user
          WHERE u.role = 'calon_karyawan'
          ORDER BY u.id_user DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tampilkan Karyawan - PT Maju Mundur</title>

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
                        <h1 class="m-0">Data Calon Karyawan</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard_admin.php">Home</a></li>
                            <li class="breadcrumb-item active">Karyawan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Daftar Calon Karyawan</h3>
                    </div>
                    <div class="card-body">
                        <table id="karyawanTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>No. HP</th>
                                    <th>JK</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo $row['nama'] ? htmlspecialchars($row['nama']) : '-'; ?></td>
                                    <td><?php echo $row['email'] ? htmlspecialchars($row['email']) : '-'; ?></td>
                                    <td><?php echo $row['no_hp'] ? htmlspecialchars($row['no_hp']) : '-'; ?></td>
                                    <td><?php echo $row['jenis_kelamin'] ?? '-'; ?></td>
                                    <td>
                                        <?php 
                                        $has_permanent = $row['has_permanent_selection'] > 0;
                                        
                                        if (!$row['id_biodata']) {
                                            echo '<span class="badge badge-secondary">Belum Ada Data</span>';
                                        } elseif (!$has_permanent) {
                                            // Has biodata but no permanent selection - no status shown
                                            echo '<span class="badge badge-light text-muted">-</span>';
                                        } else {
                                            // Has permanent selection - show tersubmit
                                            echo '<span class="badge badge-info">Tersubmit</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($row['id_biodata']): ?>
                                        <a href="detail_karyawan.php?id=<?php echo $row['id_biodata']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">Belum ada data</span>
                                        <?php endif; ?>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $('#karyawanTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
        }
    });
});
</script>
</body>
</html>