<?php
require '../proses/functions.php';
session_start();

if (!isset($_SESSION["login"])) {
    header("Location: ../login/login.php");
    exit;
}

$user_id = $_POST['user_id'];
$username = mysqli_real_escape_string($conn, $_POST['username']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Validasi data
$errors = [];

// Validasi username
if (empty($username)) {
    $errors[] = "Username tidak boleh kosong";
} elseif (strlen($username) > 50) {
    $errors[] = "Username maksimal 50 karakter";
}

// Validasi email
if (empty($email)) {
    $errors[] = "Email tidak boleh kosong";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Format email tidak valid";
} elseif (strlen($email) > 50) {
    $errors[] = "Email maksimal 50 karakter";
}

// Jika ada input password baru
if (!empty($new_password)) {
    // Validasi password saat ini
    if (empty($current_password)) {
        $errors[] = "Password saat ini harus diisi untuk mengubah password";
    } else {
        // Verifikasi password saat ini
        $query = "SELECT password FROM users WHERE id = '$user_id'";
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);
        
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Password saat ini salah";
        }
    }
    
    // Validasi password baru
    if ($new_password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak sesuai";
    } elseif (strlen($new_password) > 255) {
        $errors[] = "Password maksimal 255 karakter";
    }
}

if (empty($errors)) {
    // Update data user
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET 
                  username = '$username', 
                  email = '$email', 
                  password = '$hashed_password' 
                  WHERE id = '$user_id'";
    } else {
        $query = "UPDATE users SET 
                  username = '$username', 
                  email = '$email' 
                  WHERE id = '$user_id'";
    }
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Profil berhasil diperbarui";
    } else {
        $_SESSION['errors'] = ["Gagal memperbarui profil: " . mysqli_error($conn)];
    }
    
    header("Location: ../profile.php");
    exit;
} else {
    $_SESSION['errors'] = $errors;
    header("Location: ../profile.php?edit=true");
    exit;
}
?>