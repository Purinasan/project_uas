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

// Get all calon karyawan yang sudah memilih lowongan
$query = "SELECT pl.id_pilihan, b.nama, l.posisi, p.nilai_tkd, p.nilai_interview, p.status, p.status_pemberkasan
          FROM pemilihan_lowongan pl
          JOIN biodata b ON pl.id_biodata = b.id_biodata
          JOIN lowongan l ON pl.id_lowongan = l.id_lowongan
          LEFT JOIN penilaian p ON pl.id_pilihan = p.id_pilihan
          ORDER BY b.nama ASC";
$result = mysqli_query($conn, $query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_nilai'])) {
    $id_pilihan = $_POST['id_pilihan'];
    $nilai_tkd = $_POST['nilai_tkd'];
    $nilai_interview = $_POST['nilai_interview'];
    $status = $_POST['status'];
    $status_pemberkasan = $_POST['status_pemberkasan'];
    
    // Check if nilai already exists
    $check = mysqli_query($conn, "SELECT * FROM penilaian WHERE id_pilihan = $id_pilihan");
    
    if (mysqli_num_rows($check) > 0) {
        // Update
        $update = "UPDATE penilaian SET nilai_tkd=$nilai_tkd, nilai_interview=$nilai_interview, status='$status', status_pemberkasan='$status_pemberkasan' WHERE id_pilihan=$id_pilihan";
        if (mysqli_query($conn, $update)) {
            $success = "Nilai berhasil diupdate!";
            header("refresh:1");
        }
    } else {
        // Insert
        $insert = "INSERT INTO penilaian (id_pilihan, nilai_tkd, nilai_interview, status, status_pemberkasan) VALUES ($id_pilihan, $nilai_tkd, $nilai_interview, '$status', '$status_pemberkasan')";
        if (mysqli_query($conn, $insert)) {
            $success = "Nilai berhasil disimpan!";
            header("refresh:1");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Nilai - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .table-container { background: white; padding: 25px; border-radius: 10px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { padding: 15px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .btn-edit { padding: 6px 15px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-size: 13px; border: none; cursor: pointer; }
        .btn-edit:hover { background: #764ba2; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 30px; width: 90%; max-width: 600px; border-radius: 10px; }
        .close { float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .status-badge { padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: 600; display: inline-block; }
        .status-lulus { background: #d4edda; color: #155724; }
        .status-tidak-lulus { background: #f8d7da; color: #721c24; }
        .status-pending { background: #fff3cd; color: #856404; }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo"><h2>PT Maju Mundur</h2></div>
            <ul class="nav-menu">
                <li><a href="dashboard_admin.php">🏠 Dashboard</a></li>
                <li><a href="tampilkan_karyawan.php">👥 Tampilkan Karyawan</a></li>
                <li class="active"><a href="upload_nilai.php">📝 Upload Nilai</a></li>
                <li><a href="kelola_lowongan.php">💼 Kelola Lowongan</a></li>
                <li><a href="kelola_periode.php">📅 Kelola Periode</a></li>
            </ul>
            <div class="nav-footer">
                <a href="../logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Upload Nilai</h1>
                <div class="user-info">
                    <span class="user-role">Admin</span>
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </header>
            
            <div class="content">
                <?php if ($success): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="table-container">
                    <h3 style="margin-bottom: 20px;">Daftar Calon Karyawan</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Posisi</th>
                                <th>Nilai TKD</th>
                                <th>Nilai Interview</th>
                                <th>Status</th>
                                <th>Pemberkasan</th>
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
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo htmlspecialchars($row['posisi']); ?></td>
                                <td><?php echo $row['nilai_tkd'] ?? '-'; ?></td>
                                <td><?php echo $row['nilai_interview'] ?? '-'; ?></td>
                                <td>
                                    <?php if ($row['status']): ?>
                                        <span class="status-badge status-<?php echo ($row['status'] == 'Lulus') ? 'lulus' : 'tidak-lulus'; ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">Belum Dinilai</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['status_pemberkasan'] ?? '-'; ?></td>
                                <td>
                                    <button class="btn-edit" onclick="openModal(<?php echo $row['id_pilihan']; ?>, '<?php echo addslashes($row['nama']); ?>', '<?php echo addslashes($row['posisi']); ?>', <?php echo $row['nilai_tkd'] ?? 0; ?>, <?php echo $row['nilai_interview'] ?? 0; ?>, '<?php echo $row['status'] ?? ''; ?>', '<?php echo $row['status_pemberkasan'] ?? ''; ?>')">
                                        ✏️ Input Nilai
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal Input Nilai -->
    <div id="nilaiModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('nilaiModal').style.display='none'">&times;</span>
            <h3 style="margin-bottom: 20px;">Input Nilai</h3>
            <form method="POST">
                <input type="hidden" name="id_pilihan" id="id_pilihan">
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <p style="margin: 0;"><strong>Nama:</strong> <span id="modal_nama"></span></p>
                    <p style="margin: 5px 0 0 0;"><strong>Posisi:</strong> <span id="modal_posisi"></span></p>
                </div>
                
                <div class="form-group">
                    <label>Nilai TKD (0-100)</label>
                    <input type="number" name="nilai_tkd" id="nilai_tkd" min="0" max="100" required>
                </div>
                
                <div class="form-group">
                    <label>Nilai Interview (0-100)</label>
                    <input type="number" name="nilai_interview" id="nilai_interview" min="0" max="100" required>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="status" required>
                        <option value="">Pilih Status</option>
                        <option value="Lulus">Lulus</option>
                        <option value="Tidak Lulus">Tidak Lulus</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status Pemberkasan</label>
                    <select name="status_pemberkasan" id="status_pemberkasan" required>
                        <option value="">Pilih Status</option>
                        <option value="Lengkap">Lengkap</option>
                        <option value="Belum Lengkap">Belum Lengkap</option>
                    </select>
                </div>
                
                <button type="submit" name="submit_nilai" class="btn-edit" style="width: 100%; padding: 12px; font-size: 16px;">
                    💾 Simpan Nilai
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(id_pilihan, nama, posisi, nilai_tkd, nilai_interview, status, status_pemberkasan) {
            document.getElementById('nilaiModal').style.display = 'block';
            document.getElementById('id_pilihan').value = id_pilihan;
            document.getElementById('modal_nama').textContent = nama;
            document.getElementById('modal_posisi').textContent = posisi;
            document.getElementById('nilai_tkd').value = nilai_tkd || '';
            document.getElementById('nilai_interview').value = nilai_interview || '';
            document.getElementById('status').value = status || '';
            document.getElementById('status_pemberkasan').value = status_pemberkasan || '';
        }
        
        window.onclick = function(event) {
            var modal = document.getElementById('nilaiModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>