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

// Ambil data user
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
if (!$user_query) {
    die("Query gagal: " . mysqli_error($conn));
}
$user_data = mysqli_fetch_assoc($user_query);

// Ambil alamat default user
$default_address_query = mysqli_query($conn, "SELECT * FROM addresses WHERE user_id = '$user_id' AND is_default = 1");
$default_address = mysqli_num_rows($default_address_query) > 0 ? mysqli_fetch_assoc($default_address_query) : null;

// Proses Update Profile
if (isset($_POST['update_profile'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = "Format email tidak valid!";
    } else {
        // Cek apakah email sudah digunakan oleh user lain
        $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND id != '$user_id'");
        if (mysqli_num_rows($check_email) > 0) {
            $profile_error = "Email sudah digunakan oleh akun lain!";
        } else {
            $update_query = "UPDATE users SET username = '$username', email = '$email' WHERE id = '$user_id'";
            if (mysqli_query($conn, $update_query)) {
                $_SESSION['success_message'] = "Profil berhasil diperbarui!";
                header("Location: profile.php");
                exit;
            } else {
                $profile_error = "Gagal memperbarui profil: " . mysqli_error($conn);
            }
        }
    }
}

// Proses Update Password
if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verifikasi password saat ini
    if (!password_verify($current_password, $user_data['password'])) {
        $password_error = "Password saat ini salah!";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "Password baru dan konfirmasi password tidak cocok!";
    } elseif (strlen($new_password) < 6) {
        $password_error = "Password minimal 6 karakter!";
    } else {
        // Hash password baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['success_message'] = "Password berhasil diubah!";
            header("Location: profile.php");
            exit;
        } else {
            $password_error = "Gagal mengubah password: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya | Coffee Shop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .profile-section {
            padding: 2rem 7%;
            margin-top: 9.5rem;
            color: var(--white);
        }

        .profile-container {
            max-width: 1500px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 3rem;
        }

        .profile-sidebar {
            background: var(--black);
            border: var(--border);
            border-radius: .5rem;
            padding: 2rem;
        }

        .profile-content {
            background: var(--black);
            border: var(--border);
            border-radius: .5rem;
            padding: 2rem;
        }

        .profile-header {
            margin-bottom: 3rem;
            text-align: center;
        }

        .profile-header h1 {
            font-size: 3rem;
            color: var(--main-color);
            text-transform: uppercase;
        }

        .profile-nav {
            list-style: none;
            padding: 0;
        }

        .profile-nav li {
            margin-bottom: 1rem;
        }

        .profile-nav a {
            display: block;
            padding: 1rem;
            font-size: 1.6rem;
            color: var(--white);
            border-radius: .5rem;
            transition: all 0.3s ease;
        }

        .profile-nav a:hover,
        .profile-nav a.active {
            background: var(--main-color);
            color: var(--black);
        }

        .profile-nav a i {
            margin-right: 1rem;
        }

        .profile-info {
            margin-bottom: 3rem;
        }

        .profile-info h2 {
            font-size: 2rem;
            color: var(--main-color);
            margin-bottom: 1.5rem;
            text-transform: uppercase;
        }

        .info-group {
            display: flex;
            margin-bottom: 1.5rem;
        }

        .info-label {
            width: 150px;
            font-size: 1.6rem;
            color: var(--main-color);
        }

        .info-value {
            font-size: 1.6rem;
            color: var(--white);
        }

        .default-address {
            background: var(--bg);
            border: var(--border);
            border-radius: .5rem;
            padding: 2rem;
            margin-top: 2rem;
        }

        .default-address h3 {
            font-size: 1.8rem;
            color: var(--main-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .default-address h3 span {
            background: var(--main-color);
            color: var(--black);
            padding: 0.3rem 1rem;
            border-radius: 3rem;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .address-detail {
            font-size: 1.6rem;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .edit-btn {
            display: inline-block;
            background: var(--main-color);
            color: var(--black);
            padding: 0.8rem 1.5rem;
            font-size: 1.4rem;
            border-radius: .5rem;
            margin-top: 1rem;
        }

        .no-address {
            font-size: 1.6rem;
            color: var(--white);
            text-align: center;
            padding: 2rem;
        }

        .add-address-btn {
            display: inline-block;
            background: var(--main-color);
            color: var(--black);
            padding: 1rem 2rem;
            font-size: 1.6rem;
            font-weight: bold;
            border-radius: .5rem;
            margin-top: 1rem;
        }

        .add-address-btn:hover {
            background: var(--white);
            letter-spacing: .1rem;
        }

        /* Tambahan CSS untuk form */
        .profile-form {
            background: var(--bg);
            border: var(--border);
            border-radius: .5rem;
            padding: 2rem;
            margin-top: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 1.6rem;
            margin-bottom: .5rem;
            color: var(--main-color);
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            font-size: 1.6rem;
            background: var(--black);
            border: var(--border);
            color: var(--white);
            border-radius: .5rem;
        }

        .form-actions {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
        }

        .btn {
            padding: 1rem 2rem;
            font-size: 1.6rem;
            font-weight: bold;
            border-radius: .5rem;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--main-color);
            color: var(--black);
        }

        .error-message {
            color: var(--red);
            font-size: 1.4rem;
            margin-top: .5rem;
        }

        .success-message {
            color: var(--main-color);
            font-size: 1.4rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .tab-container {
            margin-bottom: 3rem;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid var(--main-color);
        }

        .tab-btn {
            padding: 1rem 2rem;
            font-size: 1.6rem;
            background: transparent;
            border: none;
            color: var(--white);
            cursor: pointer;
            position: relative;
        }

        .tab-btn.active {
            color: var(--main-color);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--main-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- header section -->
    <header class="header">
        <a href="../index.php" class="logo">
            <img src="../images/logo.png" alt="">
        </a>

        <nav class="navbar">
            <a href="../index.php#home">home</a>
            <a href="../index.php#about">about</a>
            <a href="../index.php#menu">menu</a>
            <a href="../index.php#products">products</a>
            <a href="../index.php#review">review</a>
            <a href="../index.php#contact">contact</a>
            <a href="../index.php#blogs">blogs</a>
            <a href="profile.php">profile</a>
        </nav>

        <div class="icons">
            <div class="fas fa-shopping-cart" id="cart-btn">
                <?php
                $select_rows = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
                $row_count = mysqli_num_rows($select_rows);
                ?>
                <a href="../cart.php" class="cart_row">
                    <span><?php echo $row_count; ?></span>
                </a>
            </div>
            <div class="fas fa-bars" id="menu-btn"></div>
        </div>
    </header>

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="profile-container">
            <!-- Sidebar Navigasi -->
            <div class="profile-sidebar">
                <ul class="profile-nav">
                    <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profil Saya</a></li>
                    <li><a href="address.php"><i class="fas fa-map-marker-alt"></i> Alamat Saya</a></li>
                    <li><a href="history.php"><i class="fas fa-history"></i> Riwayat Pesanan</a></li>
                    <li><a href="../login/logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a></li>
                </ul>
            </div>

            <!-- Konten Profil -->
            <div class="profile-content">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="success-message">
                        <?= $_SESSION['success_message'] ?>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <div class="tab-container">
                    <div class="tab-buttons">
                        <button class="tab-btn active" data-tab="profile-tab">Profil</button>
                        <button class="tab-btn" data-tab="password-tab">Password</button>
                        <button class="tab-btn" data-tab="address-tab">Alamat</button>
                    </div>

                    <!-- Tab Profile -->
                    <div id="profile-tab" class="tab-content active">
                        <div class="profile-info">
                            <h2>Informasi Pribadi</h2>
                            <form action="" method="POST" class="profile-form">
                                <?php if (isset($profile_error)): ?>
                                    <div class="error-message"><?= $profile_error ?></div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="username">Nama</label>
                                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user_data['username']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tab Password -->
                    <div id="password-tab" class="tab-content">
                        <div class="profile-info">
                            <h2>Ubah Password</h2>
                            <form action="" method="POST" class="profile-form">
                                <?php if (isset($password_error)): ?>
                                    <div class="error-message"><?= $password_error ?></div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="current_password">Password Saat Ini</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>

                                <div class="form-group">
                                    <label for="new_password">Password Baru</label>
                                    <input type="password" id="new_password" name="new_password" required>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Konfirmasi Password Baru</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" name="update_password" class="btn btn-primary">
                                        <i class="fas fa-key"></i> Ubah Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tab Alamat -->
                    <div id="address-tab" class="tab-content">
                        <div class="profile-info">
                            <h2>Alamat Utama</h2>
                            <?php if ($default_address): ?>
                                <div class="default-address">
                                    <h3>
                                        Alamat Utama
                                        <span>Default</span>
                                    </h3>
                                    <div class="address-detail">
                                        <p><strong>Email:</strong> <?= htmlspecialchars($default_address['email']) ?></p>
                                        <p><strong>Alamat:</strong></p>
                                        <p><?= htmlspecialchars($default_address['jalan']) ?></p>
                                        <p><?= htmlspecialchars($default_address['alamat']) ?></p>
                                        <p><?= htmlspecialchars($default_address['kota']) ?>, <?= htmlspecialchars($default_address['provinsi']) ?></p>
                                        <p><?= htmlspecialchars($default_address['negara']) ?> - <?= htmlspecialchars($default_address['pos_kode']) ?></p>
                                    </div>
                                    <a href="edit_address.php?id=<?= $default_address['id'] ?>" class="edit-btn">
                                        <i class="fas fa-edit"></i> Edit Alamat
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="no-address">
                                    <p>Anda belum memiliki alamat utama</p>
                                    <a href="add_address.php" class="add-address-btn">
                                        <i class="fas fa-plus"></i> Tambah Alamat
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- footer section -->
    <section class="footer">
        <div class="share">
            <a href="#" class="fab fa-facebook-f"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
            <a href="#" class="fab fa-pinterest"></a>
        </div>

        <div class="links">
            <a href="../index.php#home">home</a>
            <a href="../index.php#about">about</a>
            <a href="../index.php#menu">menu</a>
            <a href="../index.php#products">products</a>
            <a href="../index.php#review">review</a>
            <a href="../index.php#contact">contact</a>
            <a href="../index.php#blogs">blogs</a>
        </div>
    </section>

    <script src="../js/script.js"></script>
    <script>
        // Tab Navigation
        document.addEventListener('DOMContentLoaded', function() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Remove active class from all buttons and contents
                    tabBtns.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Add active class to clicked button and corresponding content
                    btn.classList.add('active');
                    const tabId = btn.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>