-- Insert Lowongan for Periode 1 (Aktif) - One at a time to avoid errors
INSERT INTO lowongan (id_periode, posisi, persyaratan, tgl_buka, tgl_tutup, tgl_interview, tgl_tkd, pengumuman_hasil) 
VALUES (1, 'Software Engineer', CONCAT('Minimal S1 Teknik Informatika', CHAR(10), 'Pengalaman 1-2 tahun', CHAR(10), 'Menguasai PHP, MySQL, JavaScript'), '2025-01-01', '2025-01-31', '2025-02-05', '2025-02-10', '2025-02-15');

INSERT INTO lowongan (id_periode, posisi, persyaratan, tgl_buka, tgl_tutup, tgl_interview, tgl_tkd, pengumuman_hasil) 
VALUES (1, 'Marketing Manager', CONCAT('Minimal S1 Marketing/Manajemen', CHAR(10), 'Pengalaman minimal 3 tahun', CHAR(10), 'Memiliki leadership yang baik'), '2025-01-01', '2025-01-31', '2025-02-06', '2025-02-11', '2025-02-16');

INSERT INTO lowongan (id_periode, posisi, persyaratan, tgl_buka, tgl_tutup, tgl_interview, tgl_tkd, pengumuman_hasil) 
VALUES (1, 'Graphic Designer', CONCAT('Minimal D3 Desain Grafis', CHAR(10), 'Menguasai Adobe Photoshop, Illustrator', CHAR(10), 'Portofolio yang menarik'), '2025-01-01', '2025-01-31', '2025-02-07', '2025-02-12', '2025-02-17');

-- Insert Lowongan for Periode 2 (Non Aktif)
INSERT INTO lowongan (id_periode, posisi, persyaratan, tgl_buka, tgl_tutup, tgl_interview, tgl_tkd, pengumuman_hasil) 
VALUES (2, 'Accountant', CONCAT('Minimal S1 Akuntansi', CHAR(10), 'Memiliki sertifikat Brevet A&B', CHAR(10), 'Teliti dan jujur'), '2024-11-01', '2024-11-30', '2024-12-05', '2024-12-10', '2024-12-15');

INSERT INTO lowongan (id_periode, posisi, persyaratan, tgl_buka, tgl_tutup, tgl_interview, tgl_tkd, pengumuman_hasil) 
VALUES (2, 'Human Resources Staff', CONCAT('Minimal S1 Psikologi/Manajemen SDM', CHAR(10), 'Pengalaman di bidang recruitment', CHAR(10), 'Komunikasi yang baik'), '2024-11-01', '2024-11-30', '2024-12-06', '2024-12-11', '2024-12-16');

-- Insert Lowongan for Periode 3 (Non Aktif)
INSERT INTO lowongan (id_periode, posisi, persyaratan, tgl_buka, tgl_tutup, tgl_interview, tgl_tkd, pengumuman_hasil) 
VALUES (3, 'Web Developer', CONCAT('Minimal S1 Teknik Informatika', CHAR(10), 'Menguasai HTML, CSS, JavaScript, React', CHAR(10), 'Pengalaman 2 tahun'), '2024-09-01', '2024-09-30', '2024-10-05', '2024-10-10', '2024-10-15');

INSERT INTO lowongan (id_periode, posisi, persyaratan, tgl_buka, tgl_tutup, tgl_interview, tgl_tkd, pengumuman_hasil) 
VALUES (3, 'Data Analyst', CONCAT('Minimal S1 Statistika/Matematika', CHAR(10), 'Menguasai Python, SQL, Excel', CHAR(10), 'Pengalaman analisis data'), '2024-09-01', '2024-09-30', '2024-10-06', '2024-10-11', '2024-10-16');

-- ========================================
-- Verify Users
-- ========================================
-- Check all users in database
SELECT id_user, username, role FROM users;

-- Check only admin users
SELECT id_user, username, role FROM users WHERE role = 'admin';

-- Check only calon karyawan users
SELECT id_user, username, role FROM users WHERE role = 'calon_karyawan';

-- ========================================
-- IMPORTANT NOTES:
-- ========================================
/*
1. All passwords above are hashed with bcrypt
2. The plaintext passwords are:
   - "admin123" for the first admin
   - "password" for all others
   
3. To generate your own password hash, you can use this PHP code:
   <?php echo password_hash("your_password", PASSWORD_DEFAULT); ?>
   
4. Run these SQL commands in phpMyAdmin:
   - Click on sdm_db database
   - Go to SQL tab
   - Paste the commands
   - Click "Go"
   
5. After inserting, you can login with:
   Username: admin
   Password: admin123
*/