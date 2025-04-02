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
    header("Location: history.php");
    exit;
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// Cek apakah pesanan milik user yang login dan statusnya pending
$check_query = mysqli_query($conn, "SELECT * FROM orders WHERE id = '$order_id' AND user_id = '$user_id' AND status = 'pending'");
if (mysqli_num_rows($check_query) == 0) {
    $_SESSION['error_message'] = "Pesanan tidak ditemukan atau tidak dapat dibatalkan";
    header("Location: history.php");
    exit;
}

// Update status pesanan menjadi cancelled
$update_query = mysqli_query($conn, "UPDATE orders SET status = 'cancelled' WHERE id = '$order_id'");

if ($update_query) {
    $_SESSION['success_message'] = "Pesanan berhasil dibatalkan";
} else {
    $_SESSION['error_message'] = "Gagal membatalkan pesanan: " . mysqli_error($conn);
}

header("Location: history.php");
exit;
?>