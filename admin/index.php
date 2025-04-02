<?php
require_once(__DIR__.'/auth.php');

// Redirect berdasarkan status login
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

header("Location: dashboard.php");
exit();
?>