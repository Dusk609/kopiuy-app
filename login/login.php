<?php
session_start();



// Debug: Lihat session yang ada
// echo "<pre>"; print_r($_SESSION); echo "</pre>"; exit;

// Periksa apakah sudah login
if (isset($_SESSION["login"]) && $_SESSION["login"] === true) {
    header("Location: ../index.php");
    exit;
}

require '../proses/functions.php';

if (isset($_POST["login"])) {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = $_POST["password"]; // Tidak perlu escape untuk password

    // Gunakan prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row["password"])) {
            // Set session
            $_SESSION = []; // Clear existing session
            $_SESSION["login"] = true;
            $_SESSION["id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["email"] = $row["email"];
            
            // Regenerate session ID untuk security
            session_regenerate_id(true);
            
            // Debug: Cek session sebelum redirect
            // echo "<pre>"; print_r($_SESSION); echo "</pre>"; exit;
            
            header("Location: ../index.php");
            exit;
        }
    }
    
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,300,0,0" />
</head>

<body>
    <div class="login-card-container">
        <div class="login-card">
            <div class="login-card-logo">
                <img src="coffee-beans.png" alt="logo">
            </div>
            <div class="login-card-header">
                <h1>Sign In</h1>
                <div>Please Sign In to use platform</div>
            </div>
            <form class="login-card-form" action="" method="post">
                <div class="form-item">
                    <span class="form-item-icon material-symbols-rounded">mail</span>
                    <input type="text" placeholder="Username" name="username" required autofocus>
                </div>
                <div class="form-item">
                    <span class="form-item-icon material-symbols-rounded">lock</span>
                    <input type="password" placeholder="Password" name="password" required>
                </div>
                <div class="error">
                    <?php if (isset($error)) : ?>
                        <p>Username / Password <br> salah</p>
                    <?php endif ?>
                </div>
                <div class="form-item-other">
                    <div class="checkbox">
                        <input type="checkbox" id="rememberMeCheckbox">
                        <label for="rememberMeCheckbox">Remember Me</label>
                    </div>
                    <a href="#">I forgot my password</a>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
            <div class="login-card-footer">
                Don't have an account? <a href="signup.php">Sign Up</a>
            </div>
            <div class="back-dusk-station">
                Back to <a href="../index.php">Home Page</a>
            </div>
        </div>
        <div class="login-card-social">
            <div>other Sign-in Platform</div>
            <div class="login-card-social-btns">
                <a href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-facebook" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3"></path>
                    </svg>
                </a>
                <a href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-google" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M17.788 5.108a9 9 0 1 0 3.212 6.892h-8"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</body>
</html>