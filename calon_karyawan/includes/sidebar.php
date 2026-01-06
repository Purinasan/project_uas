<?php
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard_karyawan.php" class="brand-link bg-gradient-primary">
        <i class="fas fa-building ml-3"></i>
        <span class="brand-text font-weight-bold ml-2">PT Maju Mundur</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-white"></i>
            </div>
            <div class="info">
                <a href="#" class="d-block"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                <small class="text-muted">Calon Karyawan</small>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="dashboard_karyawan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_karyawan.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="biodata.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'biodata.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-id-card"></i>
                        <p>Biodata</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="pemilihan_lowongan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pemilihan_lowongan.php' || basename($_SERVER['PHP_SELF']) == 'hapus_lowongan.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-briefcase"></i>
                        <p>Pemilihan Lowongan</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="penilaian.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'penilaian.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>Penilaian</p>
                    </a>
                </li>
                
            </ul>
        </nav>
    </div>
</aside>