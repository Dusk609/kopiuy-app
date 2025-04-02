<?php
session_start();
require '../config/database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Ambil ID pesanan dari URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($order_id <= 0) {
    header('Location: orders.php');
    exit;
}

// Ambil detail pesanan
try {
    $pdo = Database::getInstance()->getConnection();
    
    // Query untuk data pesanan
    $stmt = $pdo->prepare("SELECT o.*, u.username as customer_name, u.email as customer_email 
                          FROM `order` o
                          JOIN `users` u ON o.user_id = u.id
                          WHERE o.id = :id");
    $stmt->bindParam(':id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$order) {
        header('Location: orders.php');
        exit;
    }
    
    // Query untuk item pesanan
    $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image as product_image
                          FROM order_items oi
                          JOIN products p ON oi.product_id = p.id
                          WHERE oi.order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $order_id ?> | KopiUy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --main-color: #d3ad7f;
            --black: #13131a;
            --border: .1rem solid rgba(255,255,255,.3);
        }
        
        .order-detail-container {
            margin-top: 9.5rem;
            padding: 2rem 5%;
            min-height: calc(100vh - 9.5rem);
        }
        
        .heading {
            text-align: center;
            color: #fff;
            text-transform: uppercase;
            font-size: 4rem;
            margin-bottom: 3rem;
        }
        
        .heading span {
            color: var(--main-color);
        }
        
        .order-info {
            background: #13131a;
            border-radius: 0.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            border: var(--border);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .info-item {
            margin-bottom: 1rem;
        }
        
        .info-label {
            color: var(--main-color);
            font-size: 1.6rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .info-value {
            color: #fff;
            font-size: 1.8rem;
        }
        
        .status-badge {
            padding: 0.5rem 1.5rem;
            border-radius: 2rem;
            font-size: 1.4rem;
            font-weight: 600;
            text-transform: capitalize;
            display: inline-block;
        }
        
        .status-pending {
            background: rgba(255, 165, 0, 0.1);
            color: #ffa500;
            border: 1px solid #ffa500;
        }
        
        .status-processing {
            background: rgba(33, 150, 243, 0.1);
            color: #2196F3;
            border: 1px solid #2196F3;
        }
        
        .status-completed {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        [class*="status-cancel"] {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
            border: 1px solid #F44336;
        }
        
        .order-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: #13131a;
            border-radius: 0.5rem;
            overflow: hidden;
            border: var(--border);
        }
        
        .order-items th, .order-items td {
            padding: 1.5rem;
            text-align: left;
            border-bottom: var(--border);
            color: #fff;
        }
        
        .order-items th {
            background-color: var(--main-color);
            color: var(--black);
            font-size: 1.8rem;
        }
        
        .order-items td {
            font-size: 1.6rem;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
        
        .back-btn {
            display: inline-block;
            margin-top: 2rem;
            padding: 1rem 2rem;
            background: var(--main-color);
            color: var(--black);
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 1.8rem;
        }
        
        .back-btn:hover {
            background: #e0b070;
            transform: translateY(-2px);
        }
        
        .total-section {
            text-align: right;
            margin-top: 2rem;
            font-size: 1.8rem;
            color: #fff;
        }
        
        .total-amount {
            font-size: 2.2rem;
            color: var(--main-color);
            font-weight: 700;
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
            <a href="orders.php" class="active">Pesanan</a>
            <a href="customers.php">Pelanggan</a>
            <a href="reports.php">Laporan</a>
        </nav>
        
        <div class="icons">
            <a href="logout.php" class="fas fa-sign-out-alt"></a>
        </div>
    </header>

    <section class="order-detail-container">
        <h1 class="heading">Detail <span>Pesanan</span> #<?= $order_id ?></h1>
        
        <div class="order-info">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Status Pesanan</span>
                    <span class="info-value">
                        <span class="status-badge status-<?= strtolower($order['status']) ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Tanggal Pesanan</span>
                    <span class="info-value"><?= date('d M Y H:i', strtotime($order['created_at'])) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Pelanggan</span>
                    <span class="info-value"><?= htmlspecialchars($order['customer_name']) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Email Pelanggan</span>
                    <span class="info-value"><?= htmlspecialchars($order['customer_email']) ?></span>
                </div>
            </div>
            
            <div class="info-item">
                <span class="info-label">Catatan Pesanan</span>
                <span class="info-value"><?= !empty($order['notes']) ? htmlspecialchars($order['notes']) : 'Tidak ada catatan' ?></span>
            </div>
        </div>
        
        <h2 class="heading" style="font-size: 2.8rem; text-align: left; margin-bottom: 1rem;">Item Pesanan</h2>
        
        <table class="order-items">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($order_items as $item): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <img src="../images/<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="product-image">
                            <span><?= htmlspecialchars($item['product_name']) ?></span>
                        </div>
                    </td>
                    <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="total-section">
            <div style="margin-bottom: 1rem;">
                <span>Subtotal: </span>
                <span>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
            </div>
            <div>
                <span>Total: </span>
                <span class="total-amount">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
            </div>
        </div>
        
        <a href="orders.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Pesanan
        </a>
    </section>

    <section class="footer">
        <div class="credit">
            <p>KopiUy Admin Panel &copy; <?= date('Y') ?></p>
        </div>
    </section>

    <script src="../js/admin.js"></script>
</body>
</html>