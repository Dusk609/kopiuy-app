<?php
session_start();
require '../config/database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Ambil dan hapus session messages
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;

// Hapus messages dari session setelah disimpan ke variabel
unset($_SESSION['success']);
unset($_SESSION['error']);

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
    $stmt = $pdo->prepare("SELECT o.*, u.username as customer_name 
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
    
    // Proses update jika form disubmit
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Validasi status
        $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
        if(!in_array($new_status, $allowed_statuses)) {
            $_SESSION['error'] = "Status tidak valid";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE `order` SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
                $stmt->bindParam(':id', $order_id, PDO::PARAM_INT);
                
                if($stmt->execute()) {
                    $_SESSION['success'] = "Pesanan berhasil diperbarui";
                    header("Location: order_detail.php?id=$order_id");
                    exit;
                } else {
                    $_SESSION['error'] = "Gagal memperbarui pesanan";
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Gagal memperbarui pesanan: " . $e->getMessage();
            }
        }
    }
    
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

    <section class="order-edit-container">
        <h1 class="heading">Edit <span>Pesanan</span> #<?= $order_id ?></h1>
        
        <?php if($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST" class="edit-form">
            <div class="form-group">
                <label for="customer" class="form-label">Pelanggan</label>
                <input type="text" id="customer" class="form-control" value="<?= htmlspecialchars($order['customer_name']) ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="total" class="form-label">Total Pesanan</label>
                <input type="text" id="total" class="form-control" value="Rp <?= number_format($order['total_price'], 0, ',', '.') ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="date" class="form-label">Tanggal Pesanan</label>
                <input type="text" id="date" class="form-control" value="<?= date('d M Y H:i', strtotime($order['created_at'])) ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="status" class="form-label">Status Pesanan</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="notes" class="form-label">Catatan</label>
                <textarea name="notes" id="notes" class="form-control"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group" style="text-align: center; margin-top: 3rem;">
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
        
        <div style="text-align: center;">
            <a href="order_detail.php?id=<?= $order_id ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Detail Pesanan
            </a>
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