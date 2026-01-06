<?php
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard_admin.php" class="brand-link bg-gradient-primary">
        <i class="fas fa-building ml-3"></i>
        <span class="brand-text font-weight-bold ml-2">PT Maju Mundur</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                
                <li class="nav-item">
                    <a href="dashboard_admin.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_admin.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="tampilkan_karyawan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tampilkan_karyawan.php' || basename($_SERVER['PHP_SELF']) == 'detail_karyawan.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Karyawan</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="upload_nilai.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'upload_nilai.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-clipboard-check"></i>
                        <p>Upload Nilai</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="kelola_lowongan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kelola_lowongan.php' || basename($_SERVER['PHP_SELF']) == 'edit_lowongan.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-briefcase"></i>
                        <p>Kelola Lowongan</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="kelola_periode.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kelola_periode.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Kelola Periode</p>
                    </a>
                </li>
                
            </ul>
        </nav>
    </div>
</aside>

<?php
