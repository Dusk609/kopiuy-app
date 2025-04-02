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

// Ambil data alamat yang akan diedit
$address_query = mysqli_query($conn, "SELECT * FROM addresses WHERE id = '$address_id' AND user_id = '$user_id'");
if (mysqli_num_rows($address_query) == 0) {
    die("Alamat tidak ditemukan atau tidak memiliki akses!");
}

$address_data = mysqli_fetch_assoc($address_query);

// Ambil data user
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
if (!$user_query) {
    die("Query gagal: " . mysqli_error($conn));
}
$user_data = mysqli_fetch_assoc($user_query);

// Proses form edit alamat
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $number = mysqli_real_escape_string($conn, $_POST['number']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $jalan = mysqli_real_escape_string($conn, $_POST['jalan']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kota = mysqli_real_escape_string($conn, $_POST['kota']);
    $provinsi = mysqli_real_escape_string($conn, $_POST['provinsi']);
    $negara = mysqli_real_escape_string($conn, $_POST['negara']);
    $pos_kode = mysqli_real_escape_string($conn, $_POST['pos_kode']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // Jika alamat ini dijadikan default, update semua alamat lain menjadi tidak default
    if ($is_default == 1) {
        mysqli_query($conn, "UPDATE addresses SET is_default = 0 WHERE user_id = '$user_id'");
    }

    // Update alamat
    $update_query = "UPDATE addresses SET 
                    number = '$number',
                    email = '$email',
                    jalan = '$jalan',
                    alamat = '$alamat',
                    kota = '$kota',
                    provinsi = '$provinsi',
                    negara = '$negara',
                    pos_kode = '$pos_kode',
                    is_default = '$is_default'
                    WHERE id = '$address_id' AND user_id = '$user_id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success_message'] = "Alamat berhasil diperbarui!";
        header("Location: address.php");
        exit;
    } else {
        $error = "Gagal memperbarui alamat: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Alamat | Coffee Shop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .address-form-section {
            padding: 2rem 7%;
            margin-top: 9.5rem;
            color: var(--white);
        }

        .address-form-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--black);
            padding: 3rem;
            border-radius: .5rem;
            border: var(--border);
        }

        .address-form-header {
            margin-bottom: 3rem;
            text-align: center;
        }

        .address-form-header h1 {
            font-size: 3rem;
            color: var(--main-color);
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-group label {
            display: block;
            font-size: 1.6rem;
            margin-bottom: .5rem;
            color: var(--main-color);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 1rem;
            font-size: 1.6rem;
            background: var(--bg);
            border: var(--border);
            color: var(--white);
            border-radius: .5rem;
        }

        .form-group textarea {
            height: 10rem;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
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

        .btn-secondary {
            background: var(--red);
            color: var(--white);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .checkbox-group input {
            width: auto;
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

    <!-- Address Form Section -->
    <section class="address-form-section">
        <div class="address-form-container">
            <div class="address-form-header">
                <h1>Edit Alamat</h1>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="number">Nomor Telepon</label>
                    <input type="text" id="number" name="number" value="<?= htmlspecialchars($address_data['number']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($address_data['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="jalan">Nama Jalan</label>
                    <input type="text" id="jalan" name="jalan" value="<?= htmlspecialchars($address_data['jalan']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat Lengkap</label>
                    <textarea id="alamat" name="alamat" required><?= htmlspecialchars($address_data['alamat']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="kota">Kota</label>
                    <input type="text" id="kota" name="kota" value="<?= htmlspecialchars($address_data['kota']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="provinsi">Provinsi</label>
                    <input type="text" id="provinsi" name="provinsi" value="<?= htmlspecialchars($address_data['provinsi']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="negara">Negara</label>
                    <input type="text" id="negara" name="negara" value="<?= htmlspecialchars($address_data['negara']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="pos_kode">Kode Pos</label>
                    <input type="text" id="pos_kode" name="pos_kode" value="<?= htmlspecialchars($address_data['pos_kode']) ?>" required>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_default" name="is_default" value="1" <?= $address_data['is_default'] == 1 ? 'checked' : '' ?>>
                        <label for="is_default">Jadikan sebagai alamat utama</label>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="address.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
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
</body>
</html>