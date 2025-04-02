<?php
require '../proses/functions.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION["login"])) {
    header("Location: ../login/login.php");
    exit;
}

// Pastikan session user ID ada
if (!isset($_SESSION['id'])) {
    die("User ID tidak ditemukan di session!");
}

$user_id = $_SESSION['id'];
$user_id = mysqli_real_escape_string($conn, $user_id);

// Pastikan parameter ID ada
if (!isset($_GET['id'])) {
    header("Location: address.php");
    exit;
}

$address_id = mysqli_real_escape_string($conn, $_GET['id']);

// Cek apakah alamat milik user yang login
$check_query = mysqli_query($conn, "SELECT * FROM addresses WHERE id = '$address_id' AND user_id = '$user_id'");
if (mysqli_num_rows($check_query) == 0) {
    die("Alamat tidak ditemukan atau tidak memiliki akses!");
}

// Hapus alamat
$delete_query = mysqli_query($conn, "DELETE FROM addresses WHERE id = '$address_id' AND user_id = '$user_id'");

if ($delete_query) {
    $_SESSION['success_message'] = "Alamat berhasil dihapus!";
} else {
    $_SESSION['error_message'] = "Gagal menghapus alamat: " . mysqli_error($conn);
}

header("Location: address.php");
exit;
?>