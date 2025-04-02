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
    header("Location: history.php");
    exit;
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil data pesanan
$order_query = mysqli_query($conn, "
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.price * oi.quantity) as total_price
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id = '$order_id' AND o.user_id = '$user_id'
    GROUP BY o.id
");

if (mysqli_num_rows($order_query) == 0) {
    die("Pesanan tidak ditemukan atau tidak memiliki akses!");
}

$order = mysqli_fetch_assoc($order_query);

// Ambil item pesanan
$items_query = mysqli_query($conn, "
    SELECT oi.*, p.name as product_name, p.image as product_image 
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = '$order_id'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $order['id'] ?> | Coffee Shop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .order-detail-section {
            padding: 2rem 7%;
            margin-top: 9.5rem;
            color: var(--white);
        }

        .order-detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .order-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .order-detail-header h1 {
            font-size: 3rem;
            color: var(--main-color);
            text-transform: uppercase;
        }

        .back-btn {
            background: var(--main-color);
            color: var(--black);
            padding: 1rem 2rem;
            font-size: 1.6rem;
            font-weight: bold;
            border-radius: .5rem;
        }

        .order-detail-card {
            background: var(--black);
            border: var(--border);
            border-radius: .5rem;
            padding: 2rem;
            margin-bottom: 3rem;
        }

        .order-status {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 3rem;
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }

        .status-pending {
            background: #FFA500;
            color: var(--black);
        }

        .status-accepted {
            background: #4CAF50;
            color: var(--white);
        }

        .status-rejected {
            background: #F44336;
            color: var(--white);
        }

        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .order-info-group {
            margin-bottom: 1.5rem;
        }

        .order-info-group h3 {
            font-size: 1.8rem;
            color: var(--main-color);
            margin-bottom: 1rem;
        }

        .order-info-group p {
            font-size: 1.6rem;
            line-height: 1.6;
        }

        .order-items {
            margin-top: 3rem;
        }

        .order-items h2 {
            font-size: 2rem;
            color: var(--main-color);
            margin-bottom: 2rem;
            text-transform: uppercase;
            border-bottom: 1px solid var(--main-color);
            padding-bottom: 1rem;
        }

        .item-card {
            display: flex;
            gap: 2rem;
            padding: 1.5rem 0;
            border-bottom: 1px dashed var(--bg);
        }

        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: .5rem;
            border: var(--border);
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-size: 1.8rem;
            color: var(--main-color);
            margin-bottom: 0.5rem;
        }

        .item-price {
            font-size: 1.6rem;
            margin-bottom: 0.5rem;
        }

        .item-quantity {
            font-size: 1.4rem;
            color: var(--light-color);
        }

        .order-summary {
            margin-top: 3rem;
            text-align: right;
        }

        .order-total {
            font-size: 2rem;
            color: var(--main-color);
        }

        .order-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 3rem;
        }

        .btn {
            padding: 1rem 2rem;
            font-size: 1.6rem;
            border-radius: .5rem;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--main-color);
            color: var(--black);
        }

        .btn-danger {
            background: var(--red);
            color: var(--white);
        }
    </style>
</head>
<body>
    <!-- header section -->
    <header class="header">
        <!-- ... (header sama seperti sebelumnya) ... -->
    </header>

    <!-- Order Detail Section -->
    <section class="order-detail-section">
        <div class="order-detail-container">
            <div class="order-detail-header">
                <h1>Detail Pesanan #<?= $order['id'] ?></h1>
                <a href="history.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
            
            <div class="order-detail-card">
                <?php 
                $status_class = '';
                $status_text = '';
                if ($order['status'] == 'pending') {
                    $status_class = 'status-pending';
                    $status_text = 'Menunggu Respon Admin';
                } elseif ($order['status'] == 'accepted') {
                    $status_class = 'status-accepted';
                    $status_text = 'Diterima';
                } elseif ($order['status'] == 'rejected') {
                    $status_class = 'status-rejected';
                    $status_text = 'Ditolak';
                }
                ?>
                <span class="order-status <?= $status_class ?>"><?= $status_text ?></span>
                
                <div class="order-info">
                    <div>
                        <div class="order-info-group">
                            <h3>Informasi Pesanan</h3>
                            <p><strong>Tanggal:</strong> <?= date('d M Y H:i', strtotime($order['order_date'])) ?></p>
                            <p><strong>Jumlah Item:</strong> <?= $order['item_count'] ?></p>
                            <p><strong>Metode Pembayaran:</strong> <?= ucfirst($order['payment_method']) ?></p>
                        </div>
                    </div>
                    
                    <div>
                        <div class="order-info-group">
                            <h3>Alamat Pengiriman</h3>
                            <p><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="order-items">
                    <h2>Item Pesanan</h2>
                    
                    <?php while($item = mysqli_fetch_assoc($items_query)): ?>
                        <div class="item-card">
                            <img src="../images/<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="item-image">
                            <div class="item-details">
                                <h3 class="item-name"><?= htmlspecialchars($item['product_name']) ?></h3>
                                <p class="item-price">Rp <?= number_format($item['price'], 0, ',', '.') ?> x <?= $item['quantity'] ?></p>
                                <p class="item-quantity">Subtotal: Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <div class="order-summary">
                        <p class="order-total">Total: Rp <?= number_format($order['total_price'], 0, ',', '.') ?></p>
                    </div>
                </div>
                
                <div class="order-actions">
                    <?php if ($order['status'] == 'pending'): ?>
                        <a href="cancel_order.php?id=<?= $order['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin membatalkan pesanan ini?');">
                            <i class="fas fa-times"></i> Batalkan Pesanan
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- footer section -->
    <section class="footer">
        <!-- ... (footer sama seperti sebelumnya) ... -->
    </section>

    <script src="../js/script.js"></script>
</body>
</html>