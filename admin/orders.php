<?php
session_start();
require '../config/database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Ambil parameter filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Ambil data pesanan dengan filter status
try {
    $pdo = Database::getInstance()->getConnection();
    
    // Query dasar
    $sql = "SELECT o.id, o.total_price, o.status, o.created_at, 
            u.username as customer_name 
            FROM `order` o
            JOIN `users` u ON o.user_id = u.id";
    
    // Tambahkan filter status jika ada
    if (!empty($status_filter)) {
        $sql .= " WHERE o.status = :status";
    }    
    
    $sql .= " ORDER BY o.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind parameter jika ada filter
    if (!empty($status_filter)) {
        $stmt->bindParam(':status', $status_filter, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KopiUy - Kelola Pesanan</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --main-color: #d3ad7f;
            --black: #13131a;
            --border: .1rem solid rgba(255,255,255,.3);
        }
        
        .orders-container {
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
        
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: #13131a;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .order-table th, .order-table td {
            padding: 1.5rem;
            text-align: left;
            border-bottom: var(--border);
            color: #fff;
        }
        
        .order-table th {
            background-color: var(--main-color);
            color: var(--black);
            font-size: 1.8rem;
        }
        
        .order-table td {
            font-size: 1.6rem;
        }
        
        .order-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .status-filter {
            margin-bottom: 3rem;
            display: flex;
            justify-content: flex-end;
        }
        
        .status-filter select {
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            border: var(--border);
            background: #13131a;
            color: #fff;
            font-size: 1.6rem;
            cursor: pointer;
        }
        
        .status-filter select:focus {
            outline: none;
            border-color: var(--main-color);
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
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            margin: 0 0.5rem;
            border-radius: 0.5rem;
            font-size: 1.4rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--main-color);
            color: var(--black);
        }
        
        .btn-primary:hover {
            background: #e0b070;
        }
        
        .btn-secondary {
            background: #333;
            color: #fff;
        }
        
        .btn-secondary:hover {
            background: #444;
        }
        
        .empty-orders {
            text-align: center;
            padding: 3rem;
            color: #aaa;
            font-size: 1.8rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="dashboard.php" class="logo">
            <img src="../images/logo.png" alt="KopiUy Logo" height="60">
        </a>
        
        <nav class="navbar">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="products.php"><i class="fas fa-coffee"></i> Produk</a>
            <a href="orders.php"><i class="fas fa-shopping-cart"></i> Pesanan</a>
            <a href="customers.php"><i class="fas fa-users"></i> Pelanggan</a>
            <a href="revenue_report.php" class="active"><i class="fas fa-money-bill-wave"></i> Pendapatan</a>
        </nav>
        
        <div class="icons">
            <a href="logout.php" class="fas fa-sign-out-alt"></a>
        </div>
    </header>

    <section class="orders-container">
        <h1 class="heading"><span>Kelola</span> Pesanan</h1>
        
        <div class="status-filter">
            <form action="" method="get">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $status_filter == 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </form>
        </div>
        
        <table class="order-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="empty-orders">Tidak ada pesanan yang ditemukan</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($orders as $order): ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
                        <td>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary">Detail</a>
                            <a href="order_edit.php?id=<?= $order['id'] ?>" class="btn btn-secondary">Edit</a>
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