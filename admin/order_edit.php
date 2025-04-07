<?php
session_start();
require '../config/database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fungsi untuk mengurangi stok produk
function reduceProductStock($pdo, $order_id) {
    try {
        // Mulai transaksi
        $pdo->beginTransaction();
        
        // Dapatkan semua item pesanan
        $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Kurangi stok untuk setiap produk
        foreach ($items as $item) {
            $update_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
            $update_stmt->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
            
            if ($update_stmt->rowCount() == 0) {
                throw new Exception("Stok tidak mencukupi untuk produk ID: " . $item['product_id']);
            }
        }
        
        // Commit transaksi jika semua berhasil
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        // Rollback jika ada error
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
        return false;
    }
}

// Proses update status jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    try {
        $pdo = Database::getInstance()->getConnection();
        
        // Dapatkan status sebelumnya untuk pengecekan
        $stmt = $pdo->prepare("SELECT status FROM `order` WHERE id = ?");
        $stmt->execute([$order_id]);
        $current_status = $stmt->fetchColumn();
        
        // Update status pesanan
        $update_stmt = $pdo->prepare("UPDATE `order` SET status = ? WHERE id = ?");
        $update_stmt->execute([$new_status, $order_id]);
        
        // Jika status baru adalah 'completed' dan sebelumnya bukan 'completed', kurangi stok
        if ($new_status == 'completed' && $current_status != 'completed') {
            if (!reduceProductStock($pdo, $order_id)) {
                throw new Exception("Gagal mengurangi stok produk");
            }
        }
        
        $_SESSION['success'] = "Status pesanan berhasil diperbarui";
        header("Location: order_edit.php?id=" . $order_id);
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: order_edit.php?id=" . $order_id);
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: order_edit.php?id=" . $order_id);
        exit;
    }
}

// Ambil data pesanan
try {
    $pdo = Database::getInstance()->getConnection();
    
    // Query untuk data pesanan
    $stmt = $pdo->prepare("SELECT o.*, u.username as customer_name 
                          FROM `order` o
                          JOIN `users` u ON o.user_id = u.id
                          WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        $_SESSION['error'] = "Pesanan tidak ditemukan";
        header("Location: orders.php");
        exit;
    }
    
    // Query untuk item pesanan
    $items_stmt = $pdo->prepare("SELECT oi.*, p.stock as product_stock 
                                FROM order_items oi
                                JOIN products p ON oi.product_id = p.id
                                WHERE oi.order_id = ?");
    $items_stmt->execute([$order_id]);
    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pesanan #<?= $order_id ?> | KopiUy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --main-color: #d3ad7f;
            --black: #13131a;
            --border: .1rem solid rgba(255,255,255,.3);
        }
        
        .order-edit-container {
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
        
        .edit-form {
            background: #13131a;
            border-radius: 0.5rem;
            padding: 2rem;
            margin-bottom: 2rem;
            border: var(--border);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 2rem;
        }
        
        .form-label {
            display: block;
            color: var(--main-color);
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem;
            background: rgba(255,255,255,0.1);
            border: var(--border);
            border-radius: 0.5rem;
            color: #fff;
            font-size: 1.6rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--main-color);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: var(--main-color);
            color: var(--black);
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1.6rem;
        }
        
        .btn:hover {
            background: #e0b070;
            transform: translateY(-2px);
        }
        
        .btn-back {
            display: inline-block;
            margin-top: 2rem;
            padding: 1rem 2rem;
            background: #333;
            color: #fff;
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 1.8rem;
        }
        
        .btn-back:hover {
            background: #444;
        }
        
        .alert {
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
            font-size: 1.6rem;
        }
        
        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        .alert-error {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
            border: 1px solid #F44336;
        }

        .alert i {
            margin-right: 10px;
        }

        .alert-success i {
            color: #4CAF50;
        }

        .alert-error i {
            color: #F44336;
        }

        .order-details {
            background: var(--black);
            border: var(--border);
            border-radius: 0.5rem;
            padding: 2rem;
            margin-bottom: 3rem;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 1.5rem;
            font-size: 1.6rem;
        }
        
        .detail-label {
            font-weight: bold;
            color: var(--main-color);
            width: 200px;
        }
        
        .detail-value {
            color: #fff;
        }
        
        .status-form {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: var(--border);
        }
        
        .status-select {
            padding: 0.8rem 1rem;
            border: var(--border);
            background: var(--bg);
            color: #fff;
            border-radius: 0.5rem;
            font-size: 1.6rem;
        }
        
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3rem;
        }
        
        .order-items-table th, 
        .order-items-table td {
            padding: 1.5rem;
            text-align: left;
            border-bottom: var(--border);
            color: #fff;
        }
        
        .order-items-table th {
            background-color: var(--main-color);
            color: var(--black);
        }
        
        .notification {
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
            font-size: 1.6rem;
        }
        
        .notification.success {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        .notification.error {
            background: rgba(244, 67, 54, 0.2);
            color: #F44336;
            border: 1px solid #F44336;
        }
        
        .sub-heading {
            color: var(--main-color);
            font-size: 2.5rem;
            margin: 3rem 0 1.5rem;
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

    <section class="orders-container">
        <h1 class="heading">Edit <span>Pesanan</span> #<?= $order['id'] ?></h1>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="notification success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="notification error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="order-details">
            <div class="detail-row">
                <span class="detail-label">Pelanggan:</span>
                <span class="detail-value"><?= htmlspecialchars($order['customer_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Tanggal Pesanan:</span>
                <span class="detail-value"><?= date('d M Y H:i', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Harga:</span>
                <span class="detail-value">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Metode Pembayaran:</span>
                <span class="detail-value"><?= strtoupper($order['payment_method']) ?></span>
            </div>
            
            <form method="post" class="status-form">
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <select name="status" class="status-select">
                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
            </form>
        </div>
        
        <h2 class="sub-heading">Item Pesanan</h2>
        <table class="order-items-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Stok Tersedia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($order_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
                    <td><?= $item['product_stock'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="action-buttons">
            <a href="orders.php" class="btn btn-secondary">Kembali ke Daftar Pesanan</a>
        </div>
    </section>

    <section class="footer">
        <div class="credit">
            <p>KopiUy Admin Panel &copy; <?= date('Y') ?></p>
        </div>
    </section>

    <script src="../js/admin.js"></script>
</body>
</html>