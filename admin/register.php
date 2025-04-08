<?php
session_start();

// Redirect jika sudah login
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit();
}

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require '../config/database.php';
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    // Validasi
    if (empty($username) || empty($password) || empty($confirm_password) || empty($name) || empty($email)) {
        $error = "Semua field harus diisi";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok";
    } elseif (strlen($password) < 8) {
        $error = "Password minimal 8 karakter";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid";
    } else {
        try {
            $pdo = Database::getInstance()->getConnection();
            
            // Cek apakah username atau email sudah ada
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = "Username atau email sudah digunakan";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert admin baru
                $stmt = $pdo->prepare("INSERT INTO admins (username, password, name, email, role, created_at) 
                                      VALUES (?, ?, ?, ?, 'admin', NOW())");
                $stmt->execute([$username, $hashed_password, $name, $email]);
                
                // Set session untuk notifikasi
                $_SESSION['registration_success'] = true;
                $_SESSION['registered_username'] = $username;
                
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | KopiUy</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Tambahan style khusus untuk halaman login */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            padding: 2rem;
        }
        
        .login-box {
            background: var(--black);
            border: var(--border);
            border-radius: .5rem;
            padding: 3rem;
            width: 100%;
            max-width: 50rem;
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.3);
        }
        
        .login-box h2 {
            color: var(--main-color);
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 2rem;
            text-transform: uppercase;
        }
        
        .input-group {
            margin-bottom: 2rem;
        }
        
        .input-group label {
            display: block;
            color: var(--white);
            font-size: 1.6rem;
            margin-bottom: .5rem;
        }
        
        .input-group input {
            width: 100%;
            padding: 1.2rem 1.5rem;
            font-size: 1.6rem;
            color: var(--white);
            background: var(--bg);
            border: var(--border);
            border-radius: .5rem;
        }
        
        .input-group input:focus {
            border-color: var(--main-color);
        }
        
        .btn-login {
            width: 100%;
            padding: 1.2rem;
            font-size: 1.8rem;
            margin-top: 1rem;
        }
        
        .error-message {
            color: var(--red);
            font-size: 1.4rem;
            text-align: center;
            margin-bottom: 1.5rem;
            display: <?= isset($error) ? 'block' : 'none' ?>;
        }
        .auth-links {
            text-align: center;
            margin-top: 2rem;
            color: var(--white);
            font-size: 1.4rem;
        }

        .auth-links a {
            color: var(--main-color);
            text-decoration: none;
            transition: color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .auth-links a:hover {
            color: var(--main-color-hover);
            text-decoration: underline;
        }

        .auth-links a i {
            font-size: 1.2rem;
        }

        /* Jika ingin menambahkan variabel warna hover */
        :root {
            --main-color-hover: #d4a762; /* Warna yang sedikit lebih terang/muda */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Register Admin</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form action="register.php" method="post">
                <div class="input-group">
                    <label for="name"><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="input-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="input-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="input-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                
                <div class="input-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
            <div class="auth-links">
                Sudah punya akun? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login disini</a>
            </div>
        </div>
    </div>

    <!-- Font Awesome untuk ikon -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>