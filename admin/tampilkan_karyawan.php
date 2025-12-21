<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// Get all calon karyawan with biodata
$query = "SELECT u.id_user, u.username, b.id_biodata, b.nama, b.email, b.no_hp, b.status_akun, b.jenis_kelamin, b.ttl
          FROM users u
          LEFT JOIN biodata b ON u.id_user = b.id_user
          WHERE u.role = 'calon_karyawan'
          ORDER BY u.id_user DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tampilkan Karyawan - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .table-container { background: white; padding: 25px; border-radius: 10px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 15px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .status-badge { padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: 600; }
        .status-0 { background: #fff3cd; color: #856404; }
        .status-1 { background: #cce5ff; color: #004085; }
        .status-2 { background: #d4edda; color: #155724; }
        .btn-view { padding: 6px 15px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; font-size: 13px; }
        .btn-view:hover { background: #764ba2; }
        .search-box { margin-bottom: 20px; }
        .search-box input { padding: 12px; width: 100%; max-width: 400px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="logo"><h2>PT Maju Mundur</h2></div>
            <ul class="nav-menu">
                <li><a href="dashboard_admin.php">🏠 Dashboard</a></li>
                <li class="active"><a href="tampilkan_karyawan.php">👥 Tampilkan Karyawan</a></li>
                <li><a href="upload_nilai.php">📝 Upload Nilai</a></li>
                <li><a href="kelola_lowongan.php">💼 Kelola Lowongan</a></li>
                <li><a href="kelola_periode.php">📅 Kelola Periode</a></li>
            </ul>
            <div class="nav-footer">
                <a href="../logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </nav>
        
        <main class="main-content">
            <header class="top-bar">
                <h1>Tampilkan Karyawan</h1>
                <div class="user-info">
                    <span class="user-role">Admin</span>
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                </div>
            </header>
            
            <div class="content">
                <div class="table-container">
                    <div class="search-box">
                        <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="🔍 Cari nama, email, atau username...">
                    </div>
                    
                    <table id="karyawanTable">
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
                                    $status = $row['status_akun'] ?? null;
                                    if ($status === null) {
                                        echo '<span class="status-badge status-0">Belum Ada Data</span>';
                                    } elseif ($status == 0) {
                                        echo '<span class="status-badge status-0">Belum Lengkap</span>';
                                    } elseif ($status == 1) {
                                        echo '<span class="status-badge status-1">Tersubmit</span>';
                                    } else {
                                        echo '<span class="status-badge status-2">Divalidasi</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($row['id_biodata']): ?>
                                    <a href="detail_karyawan.php?id=<?php echo $row['id_biodata']; ?>" class="btn-view">👁️ Lihat Detail</a>
                                    <?php else: ?>
                                    <span style="color: #999; font-size: 13px;">Belum ada data</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function searchTable() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("karyawanTable");
            var tr = table.getElementsByTagName("tr");
            
            for (var i = 1; i < tr.length; i++) {
                var tdArray = tr[i].getElementsByTagName("td");
                var found = false;
                
                for (var j = 0; j < tdArray.length; j++) {
                    var td = tdArray[j];
                    if (td) {
                        var txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                if (found) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>