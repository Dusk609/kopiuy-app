<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../config/database.php';

// Verifikasi login admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: customers.php');
    exit;
}

$customer_id = (int)$_GET['id'];
$customer = [];
$address = [];
$error_message = '';

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Ambil data user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        throw new Exception("Pelanggan tidak ditemukan");
    }

    // Cek apakah tabel alamat ada dan ambil data
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'addresses'");
    $stmt->execute();
    $addressTableExists = $stmt->fetch();

    if (!$addressTableExists) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'address'");
        $stmt->execute();
        $addressTableExists = $stmt->fetch();
    }

    if ($addressTableExists) {
        $tableName = $addressTableExists[0]; // ambil nama tabel yang ada
        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE user_id = ?");
        $stmt->execute([$customer_id]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_message = "Terjadi kesalahan sistem.";
    error_log("Error di customer_detail.php: " . $e->getMessage());
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pelanggan | KopiUy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .customer-detail-container {
            margin-top: 9.5rem;
            padding: 2rem 7%;
        }
        
        .customer-detail-card {
            background: var(--black);
            border: var(--border);
            border-radius: 0.5rem;
            padding: 3rem;
            margin-bottom: 3rem;
        }
        
        .customer-detail-card h2 {
            font-size: 3rem;
            color: var(--main-color);
            margin-bottom: 2rem;
            text-transform: uppercase;
        }
        
        .detail-group {
            margin-bottom: 2rem;
        }
        
        .detail-label {
            font-size: 1.6rem;
            color: var(--main-color);
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .detail-value {
            font-size: 1.6rem;
            color: var(--white);
            padding: 1rem;
            background: var(--bg);
            border-radius: 0.3rem;
            display: block;
            border: var(--border);
        }
        
        .address-section {
            margin-top: 3rem;
            padding-top: 3rem;
            border-top: var(--border);
        }
        
        .address-section h3 {
            font-size: 2rem;
            color: var(--main-color);
            margin-bottom: 1.5rem;
            text-transform: uppercase;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: var(--main-color);
            font-size: 1.6rem;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .alert-error {
            padding: 1rem;
            background: rgba(244, 67, 54, 0.2);
            color: var(--red);
            border: 1px solid var(--red);
            border-radius: 0.5rem;
            margin-bottom: 2rem;
            font-size: 1.6rem;
        }
        
        .email-small {
            font-size: 1.3rem;
            text-transform: lowercase;
        }
        
        .no-address {
            color: #aaa;
            font-style: italic;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="dashboard.php" class="logo">
            <img src="../images/logo.png" alt="KopiUy Logo" height="60">
        </a>
        
        <nav class="navbar">
            <a href="dashboard.php">Dashboard</a>
            <a href="products.php">Produk</a>
            <a href="orders.php">Pesanan</a>
            <a href="customers.php" class="active">Pelanggan</a>
            <a href="reports.php">Laporan</a>
        </nav>
        
        <div class="icons">
            <a href="logout.php" class="fas fa-sign-out-alt"></a>
        </div>
    </header>

    <section class="customer-detail-container">
        <h1 class="heading">Detail <span>Pelanggan</span></h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php else: ?>
            <div class="customer-detail-card">
                <h2>Informasi Pelanggan</h2>
                
                <div class="detail-group">
                    <span class="detail-label">ID Pelanggan</span>
                    <span class="detail-value">#<?= htmlspecialchars($customer['id']) ?></span>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Username</span>
                    <span class="detail-value"><?= htmlspecialchars($customer['username']) ?></span>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Email</span>
                    <span class="detail-value email-small"><?= strtolower(htmlspecialchars($customer['email'])) ?></span>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Tanggal Daftar</span>
                    <span class="detail-value"><?= date('d M Y H:i', strtotime($customer['created_at'])) ?></span>
                </div>
                
                <div class="address-section">
                    <h3>Informasi Alamat</h3>
                    
                    <?php if (!empty($address)): ?>
                        <div class="detail-group">
                            <span class="detail-label">Telepon</span>
                            <span class="detail-value"><?= !empty($address['phone']) ? htmlspecialchars($address['phone']) : '<span class="no-address">-</span>' ?></span>
                        </div>
                        
                        <div class="detail-group">
                            <span class="detail-label">Alamat Lengkap</span>
                            <span class="detail-value"><?= !empty($address['address']) ? htmlspecialchars($address['address']) : '<span class="no-address">-</span>' ?></span>
                        </div>
                    <?php else: ?>
                        <div class="detail-group">
                            <span class="no-address">Pelanggan belum menambahkan alamat</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <a href="customers.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pelanggan
                </a>
            </div>
        <?php endif; ?>
    </section>

    <section class="footer">
        <div class="credit">
            <p>KopiUy Admin Panel &copy; <?= date('Y') ?></p>
        </div>
    </section>

    <script src="../js/admin.js"></script>
</body>
</html>