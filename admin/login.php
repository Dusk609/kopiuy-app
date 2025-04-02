<?php
session_start();

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require '../config/database.php';
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['last_login'] = date('Y-m-d H:i:s');
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Username atau password salah";
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan sistem";
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Login Admin</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="post">
                <div class="input-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="input-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>

    <!-- Font Awesome untuk ikon -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>