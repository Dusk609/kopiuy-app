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

$customers = [];
$error_message = '';

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Query dasar hanya dari tabel users
    $query = "SELECT id, username, email, created_at FROM users ORDER BY created_at DESC";
    $customers = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
    error_log("Error di customers.php: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pelanggan | KopiUy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .customers-container {
            margin-top: 9.5rem;
            padding: 2rem 7%;
        }
        
        .customers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: var(--black);
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .customers-table th, .customers-table td {
            padding: 1.5rem;
            text-align: left;
            border-bottom: var(--border);
            color: var(--white);
        }
        
        .customers-table th {
            background-color: var(--main-color);
            color: var(--black);
            font-size: 1.8rem;
        }
        
        .customers-table td {
            font-size: 1.6rem;
        }
        
        .customers-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .email-small {
            font-size: 1.4rem;
            text-transform: lowercase;
        }
        
        .empty-customers {
            text-align: center;
            padding: 3rem;
            color: #aaa;
            font-size: 1.8rem;
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

    <section class="customers-container">
        <h1 class="heading">Daftar <span>Pelanggan</span></h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <table class="customers-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="5" class="empty-customers">Belum ada pelanggan terdaftar</td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($customer['id']) ?></td>
                        <td><?= htmlspecialchars($customer['username']) ?></td>
                        <td class="email-small"><?= htmlspecialchars(strtolower($customer['email'])) ?></td>

                        <td><?= date('d M Y', strtotime($customer['created_at'])) ?></td>
                        <td>
                            <a href="customer_detail.php?id=<?= $customer['id'] ?>" class="btn" style="padding: 0.8rem 1.5rem; font-size: 1.4rem;">
                                <i class="fas fa-info-circle"></i> Detail
                            </a>
                            <!-- <a href="customer_delete.php?id=<?= $customer['id'] ?>" 
                               class="btn" 
                               style="background: var(--red); padding: 0.8rem 1.5rem; font-size: 1.4rem;"
                               onclick="return confirm('Hapus pelanggan ini?')">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </a> -->
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section class="footer">
        <div class="credit">
            <p>KopiUy Admin Panel &copy; <?= date('Y') ?></p>
        </div>
    </section>

    <script src="../js/admin.js"></script>
</body>
</html>