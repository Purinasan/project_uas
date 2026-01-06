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

$query = "SELECT pl.id_pilihan, b.id_biodata, b.nama, b.email, b.no_hp, b.jenis_kelamin, 
          b.status_akun, l.posisi, p.nilai_tkd, p.nilai_interview, p.status, p.status_pemberkasan
          FROM pemilihan_lowongan pl
          JOIN biodata b ON pl.id_biodata = b.id_biodata
          JOIN lowongan l ON pl.id_lowongan = l.id_lowongan
          LEFT JOIN penilaian p ON pl.id_pilihan = p.id_pilihan
          ORDER BY p.status_pemberkasan DESC, b.nama ASC";
$result = mysqli_query($conn, $query);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_nilai'])) {
    $id_pilihan = $_POST['id_pilihan'];
    $nilai_tkd = $_POST['nilai_tkd'];
    $nilai_interview = $_POST['nilai_interview'];
    $status = $_POST['status'];
    $status_pemberkasan = $_POST['status_pemberkasan'];
    
    if ($status_pemberkasan == 'Belum Lengkap') {
        $error = "Tidak dapat menyimpan nilai! Harap lengkapi pemberkasan terlebih dahulu.";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM penilaian WHERE id_pilihan = $id_pilihan");
        
        if (mysqli_num_rows($check) > 0) {
            $update = "UPDATE penilaian SET nilai_tkd=$nilai_tkd, nilai_interview=$nilai_interview, status='$status', status_pemberkasan='$status_pemberkasan' WHERE id_pilihan=$id_pilihan";
            if (mysqli_query($conn, $update)) {
                $success = "Nilai berhasil diupdate!";
                header("refresh:1");
            }
        } else {
            $insert = "INSERT INTO penilaian (id_pilihan, nilai_tkd, nilai_interview, status, status_pemberkasan) VALUES ($id_pilihan, $nilai_tkd, $nilai_interview, '$status', '$status_pemberkasan')";
            if (mysqli_query($conn, $insert)) {
                $success = "Nilai berhasil disimpan!";
                header("refresh:1");
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Nilai - Admin</title>
    
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
                <h1><i class="fas fa-clipboard-check"></i> Upload Nilai</h1>
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
                        <i class="icon fas fa-ban"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Calon Karyawan</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama</th>
                                    <th>Posisi</th>
                                    <th width="12%">Pemberkasan</th>
                                    <th width="10%">Nilai TKD</th>
                                    <th width="10%">Nilai Interview</th>
                                    <th width="12%">Status</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                mysqli_data_seek($result, 0);
                                while ($row = mysqli_fetch_assoc($result)): 
                                    $biodata_lengkap = ($row['status_akun'] == 1 || $row['status_akun'] == 2);
                                ?>
                                <tr <?php echo !$biodata_lengkap ? 'class="table-warning"' : ''; ?>>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['nama']); ?>
                                        <?php if (!$biodata_lengkap): ?>
                                            <br><small class="badge badge-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Biodata Belum Lengkap
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['posisi']); ?></td>
                                    <td>
                                        <?php if ($row['status_pemberkasan']): ?>
                                            <span class="badge badge-<?php echo ($row['status_pemberkasan'] == 'Lengkap') ? 'success' : 'warning'; ?>">
                                                <i class="fas fa-<?php echo ($row['status_pemberkasan'] == 'Lengkap') ? 'check' : 'exclamation-triangle'; ?>"></i>
                                                <?php echo $row['status_pemberkasan']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Belum Diperiksa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo $row['nilai_tkd'] ?? '-'; ?></td>
                                    <td class="text-center"><?php echo $row['nilai_interview'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($row['status']): ?>
                                            <span class="badge badge-<?php echo ($row['status'] == 'Lulus') ? 'success' : 'danger'; ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Belum Dinilai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick='viewDetail(<?php echo json_encode($row); ?>)'>
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="openModal(<?php echo $row['id_pilihan']; ?>, '<?php echo addslashes($row['nama']); ?>', '<?php echo addslashes($row['posisi']); ?>', <?php echo $row['nilai_tkd'] ?? 0; ?>, <?php echo $row['nilai_interview'] ?? 0; ?>, '<?php echo $row['status'] ?? ''; ?>', '<?php echo $row['status_pemberkasan'] ?? ''; ?>')"
                                                <?php echo !$biodata_lengkap ? 'disabled title="Biodata belum lengkap"' : ''; ?>>
                                            <i class="fas fa-edit"></i> Nilai
                                        </button>
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

<!-- Modal Detail Biodata -->
<div class="modal fade" id="detailModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title"><i class="fas fa-user"></i> Detail Biodata Karyawan</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nama:</strong> <span id="detail_nama"></span></p>
                        <p><strong>Email:</strong> <span id="detail_email"></span></p>
                        <p><strong>No. HP:</strong> <span id="detail_no_hp"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Jenis Kelamin:</strong> <span id="detail_jk"></span></p>
                        <p><strong>Status Biodata:</strong> <span id="detail_status_akun"></span></p>
                    </div>
                </div>
                <a href="#" id="detail_link" class="btn btn-primary" target="_blank">
                    <i class="fas fa-file-alt"></i> Lihat Biodata Lengkap
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input Nilai -->
<div class="modal fade" id="nilaiModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-edit"></i> Input Nilai</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="id_pilihan" id="id_pilihan">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Nama:</strong> <span id="modal_nama"></span><br>
                        <strong>Posisi:</strong> <span id="modal_posisi"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Status Pemberkasan *</label>
                        <select name="status_pemberkasan" id="status_pemberkasan" class="form-control" required onchange="checkPemberkasan()">
                            <option value="">Pilih Status</option>
                            <option value="Lengkap">Lengkap</option>
                            <option value="Belum Lengkap">Belum Lengkap</option>
                        </select>
                        <small class="text-danger" id="warning_pemberkasan" style="display:none;">
                            <i class="fas fa-exclamation-triangle"></i> Pemberkasan harus lengkap untuk input nilai!
                        </small>
                    </div>
                    
                    <div id="nilai_section">
                        <div class="form-group">
                            <label>Nilai TKD (0-100)</label>
                            <input type="number" name="nilai_tkd" id="nilai_tkd" class="form-control" min="0" max="100">
                        </div>
                        
                        <div class="form-group">
                            <label>Nilai Interview (0-100)</label>
                            <input type="number" name="nilai_interview" id="nilai_interview" class="form-control" min="0" max="100">
                        </div>
                        
                        <div class="form-group">
                            <label>Status Kelulusan</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Pilih Status</option>
                                <option value="Lulus">Lulus</option>
                                <option value="Tidak Lulus">Tidak Lulus</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" name="submit_nilai" id="btn_submit" class="btn btn-primary">
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

<script>
function checkPemberkasan() {
    var status = document.getElementById('status_pemberkasan').value;
    var nilaiSection = document.getElementById('nilai_section');
    var warning = document.getElementById('warning_pemberkasan');
    var btnSubmit = document.getElementById('btn_submit');
    
    if (status == 'Belum Lengkap') {
        nilaiSection.style.opacity = '0.5';
        nilaiSection.querySelectorAll('input, select').forEach(el => el.disabled = true);
        warning.style.display = 'block';
        btnSubmit.disabled = true;
    } else if (status == 'Lengkap') {
        nilaiSection.style.opacity = '1';
        nilaiSection.querySelectorAll('input, select').forEach(el => el.disabled = false);
        warning.style.display = 'none';
        btnSubmit.disabled = false;
    }
}

function viewDetail(data) {
    document.getElementById('detail_nama').textContent = data.nama;
    document.getElementById('detail_email').textContent = data.email || '-';
    document.getElementById('detail_no_hp').textContent = data.no_hp || '-';
    document.getElementById('detail_jk').textContent = data.jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan';
    
    var statusAkun = '';
    if (data.status_akun == 0) statusAkun = '<span class="badge badge-warning">Belum Lengkap</span>';
    else if (data.status_akun == 1) statusAkun = '<span class="badge badge-info">Tersubmit</span>';
    else statusAkun = '<span class="badge badge-success">Divalidasi</span>';
    document.getElementById('detail_status_akun').innerHTML = statusAkun;
    
    document.getElementById('detail_link').href = 'detail_karyawan.php?id=' + data.id_biodata;
    
    $('#detailModal').modal('show');
}

function openModal(id_pilihan, nama, posisi, nilai_tkd, nilai_interview, status, status_pemberkasan) {
    document.getElementById('id_pilihan').value = id_pilihan;
    document.getElementById('modal_nama').textContent = nama;
    document.getElementById('modal_posisi').textContent = posisi;
    document.getElementById('nilai_tkd').value = nilai_tkd || '';
    document.getElementById('nilai_interview').value = nilai_interview || '';
    document.getElementById('status').value = status || '';
    document.getElementById('status_pemberkasan').value = status_pemberkasan || '';
    
    checkPemberkasan();
    $('#nilaiModal').modal('show');
}
</script>
</body>
</html>