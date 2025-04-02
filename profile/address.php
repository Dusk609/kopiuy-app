<?php
require '../proses/functions.php';
session_start(); // Pastikan session_start() ada di awal

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

// Hindari SQL Injection
$user_id = mysqli_real_escape_string($conn, $user_id);

// Cek koneksi database
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil data user dari session
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");

if (!$user_query) {
    die("Query gagal: " . mysqli_error($conn));
}

$user_data = mysqli_fetch_assoc($user_query);

// Ambil data alamat berdasarkan user_id
$address_query = mysqli_query($conn, "SELECT * FROM addresses WHERE user_id = '$user_id' ORDER BY id DESC");

if (!$address_query) {
    die("Query alamat gagal: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alamat Saya | Coffee Shop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Tambahan CSS khusus untuk halaman alamat */
        .address-section {
            padding: 2rem 7%;
            margin-top: 9.5rem;
            color: var(--white);
        }

        .address-container {
            max-width: 1500px;
            margin: 0 auto;
        }

        .address-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .address-header h1 {
            font-size: 3rem;
            color: var(--main-color);
            text-transform: uppercase;
        }

        .add-address-btn {
            background: var(--main-color);
            color: var(--black);
            padding: 1rem 2rem;
            font-size: 1.6rem;
            font-weight: bold;
            border-radius: .5rem;
        }

        .add-address-btn:hover {
            background: var(--white);
            letter-spacing: .1rem;
        }

        .address-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .address-card {
            background: var(--black);
            border: var(--border);
            border-radius: .5rem;
            padding: 2rem;
            position: relative;
        }

        .address-card h3 {
            font-size: 2rem;
            color: var(--main-color);
            margin-bottom: 1.5rem;
            text-transform: uppercase;
        }

        .address-detail {
            font-size: 1.6rem;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .address-actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }

        .edit-btn, .delete-btn {
            padding: 0.8rem 1.5rem;
            font-size: 1.4rem;
            border-radius: .5rem;
        }

        .edit-btn {
            background: var(--main-color);
            color: var(--black);
        }

        .delete-btn {
            background: var(--red);
            color: var(--white);
        }

        .default-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--main-color);
            color: var(--black);
            padding: 0.3rem 1rem;
            border-radius: 3rem;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .empty-address {
            text-align: center;
            padding: 5rem 0;
            font-size: 1.8rem;
            color: var(--white);
            grid-column: 1 / -1;
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

    <!-- Address Section -->
    <section class="address-section">
        <div class="address-container">
            <div class="address-header">
                <h1>Alamat Saya</h1>
                <a href="add_address.php" class="add-address-btn">
                    <i class="fas fa-plus"></i> Tambah Alamat Baru
                </a>
            </div>
            
            <div class="address-grid">
                <?php if (mysqli_num_rows($address_query) > 0): ?>
                    <?php while($address = mysqli_fetch_assoc($address_query)): ?>
                        <div class="address-card">
                            <?php if ($address['is_default'] == 1): ?>
                                <span class="default-badge">Default</span>
                            <?php endif; ?>
                            
                            <h3><?= htmlspecialchars($user_data['username']) ?></h3>
                            
                            <div class="address-detail">
                                <p><strong>No. HP:</strong> <?= htmlspecialchars($address['number']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($address['email']) ?></p>
                                <p><strong>Alamat:</strong></p>
                                <p><?= htmlspecialchars($address['jalan']) ?></p>
                                <p><?= htmlspecialchars($address['alamat']) ?></p>
                                <p><?= htmlspecialchars($address['kota']) ?>, <?= htmlspecialchars($address['provinsi']) ?></p>
                                <p><?= htmlspecialchars($address['negara']) ?> - <?= htmlspecialchars($address['pos_kode']) ?></p>
                            </div>
                            
                            <div class="address-actions">
                                <a href="edit_address.php?id=<?= $address['id'] ?>" class="edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_address.php?id=<?= $address['id'] ?>" class="delete-btn" onclick="return confirm('Yakin ingin menghapus alamat ini?');">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-address">
                        <p>Anda belum memiliki alamat yang tersimpan</p>
                        <a href="add_address.php" class="add-address-btn" style="display: inline-block; margin-top: 2rem;">
                            <i class="fas fa-plus"></i> Tambah Alamat Pertama
                        </a>
                    </div>
                <?php endif; ?>
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
</body>
</html>
