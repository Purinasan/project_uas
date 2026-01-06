<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'calon_karyawan') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$biodata_query = "SELECT * FROM biodata WHERE id_user = $user_id";
$biodata_result = mysqli_query($conn, $biodata_query);
$biodata = mysqli_fetch_assoc($biodata_result);

if (!$biodata) {
    header("Location: biodata.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: pemilihan_lowongan.php?error=unauthorized");
    exit();
}

$id_pilihan = (int)$_GET['id'];

$verify_query = "SELECT pl.* FROM pemilihan_lowongan pl
                 WHERE pl.id_pilihan = $id_pilihan 
                 AND pl.id_biodata = '{$biodata['id_biodata']}'";
$verify_result = mysqli_query($conn, $verify_query);

if (mysqli_num_rows($verify_result) == 0) {
    header("Location: pemilihan_lowongan.php?error=unauthorized");
    exit();
}

$pilihan = mysqli_fetch_assoc($verify_result);

if ($pilihan['status_pilihan'] == 'permanen') {
    header("Location: pemilihan_lowongan.php?error=already_confirmed");
    exit();
}

$check_penilaian = mysqli_query($conn, "SELECT * FROM penilaian WHERE id_pilihan = $id_pilihan");

if (mysqli_num_rows($check_penilaian) > 0) {
    header("Location: pemilihan_lowongan.php?error=has_assessment");
    exit();
}

$delete_query = "DELETE FROM pemilihan_lowongan WHERE id_pilihan = $id_pilihan";

if (mysqli_query($conn, $delete_query)) {
    header("Location: pemilihan_lowongan.php?msg=deleted");
    exit();
} else {
    header("Location: pemilihan_lowongan.php?error=delete_failed");
    exit();
}
?>