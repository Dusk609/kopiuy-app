<?php
function isAdminLoggedIn() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    // Cek hanya status login admin
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function redirectIfNotLoggedIn() {
    if (!isAdminLoggedIn()) {
        $base_url = 'http://localhost/Laravel/kopiuy-app/admin/';
        header("Location: " . $base_url . "login.php");
        exit();
    }
}
?>