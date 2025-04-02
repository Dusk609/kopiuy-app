<?php
require '../proses/functions.php';
session_start();

if (!isset($_SESSION["login"]) || !isset($_SESSION['id'])) {
    header("Location: ../login/login.php");
    exit;
}

$user_id = $_SESSION['id'];
$user_id = mysqli_real_escape_string($conn, $user_id);

// Get orders with address information
$orders_query = mysqli_query($conn, "
    SELECT o.*, 
           a.jalan, a.alamat, a.kota, a.provinsi, a.negara, a.pos_kode,
           CONCAT(a.jalan, ', ', a.alamat, ', ', a.kota, ', ', a.provinsi, ', ', a.negara, ' ', a.pos_kode) as shipping_address
    FROM `order` o
    LEFT JOIN addresses a ON o.address_id = a.id
    WHERE o.user_id = '$user_id'
    ORDER BY o.created_at DESC
");

if (!$orders_query) {
    die("Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan | Coffee Shop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .history-section {
            padding: 2rem 7%;
            margin-top: 9.5rem;
            color: var(--white);
        }

        .history-container {
            max-width: 1500px;
            margin: 0 auto;
        }

        .history-header {
            margin-bottom: 3rem;
            text-align: center;
        }

        .history-header h1 {
            font-size: 3rem;
            color: var(--main-color);
            text-transform: uppercase;
        }

        .order-list {
            display: grid;
            gap: 2rem;
        }

        .order-card {
            background: var(--black);
            border: var(--border);
            border-radius: .5rem;
            padding: 2rem;
            position: relative;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--main-color);
        }

        .order-id {
            font-size: 1.8rem;
            color: var(--main-color);
        }

        .order-date {
            font-size: 1.4rem;
            color: var(--white);
        }

        .order-status {
            position: absolute;
            top: 2rem;
            right: 2rem;
            padding: 0.5rem 1.5rem;
            border-radius: 3rem;
            font-size: 1.4rem;
            font-weight: bold;
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

        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .order-summary {
            font-size: 1.6rem;
        }

        .order-summary p {
            margin-bottom: 0.8rem;
        }

        .order-summary strong {
            color: var(--main-color);
        }

        .order-items {
            font-size: 1.6rem;
        }

        .order-items h3 {
            color: var(--main-color);
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }

        .item-list {
            list-style: none;
            padding: 0;
        }

        .item-list li {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px dashed var(--bg);
        }

        .order-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            font-size: 1.4rem;
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

        .empty-history {
            text-align: center;
            padding: 5rem 0;
            font-size: 1.8rem;
            color: var(--white);
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
            <a href="../profile/profile.php">profile</a>
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

    <!-- History Section -->
    <section class="history-section">
        <div class="history-container">
            <div class="history-header">
                <h1>Riwayat Pesanan</h1>
            </div>
            
            <div class="order-list">
                <?php if (mysqli_num_rows($orders_query) > 0): ?>
                    <?php while($order = mysqli_fetch_assoc($orders_query)): ?>
                        <div class="order-card">
                            <!-- Status Pesanan -->
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
                            
                            <div class="order-header">
                                <div class="order-id">Order #<?= htmlspecialchars($order['id'] ?? '') ?></div>
                                <div class="order-date"><?= isset($order['order_date']) ? date('d M Y H:i', strtotime($order['order_date'])) : 'N/A' ?></div>
                            </div>
                            
                            <div class="order-details">
                                <div class="order-summary">
                                    <p><strong>Jumlah Item:</strong> <?= htmlspecialchars($order['item_count'] ?? '0') ?></p>
                                    <p><strong>Total Harga:</strong> Rp <?= isset($order['total_price']) ? number_format($order['total_price'], 0, ',', '.') : '0' ?></p>
                                    <p><strong>Metode Pembayaran:</strong> <?= isset($order['payment_method']) ? ucfirst($order['payment_method']) : 'N/A' ?></p>
                                    <p><strong>Alamat Pengiriman:</strong></p>
                                    <p><?= isset($order['shipping_address']) ? htmlspecialchars($order['shipping_address']) : 'Alamat tidak tersedia' ?></p>
                                </div>
                                
                                <div class="order-items">
                                    <h3>Item Pesanan</h3>
                                    <ul class="item-list">
                                        <?php 
                                        // Ambil detail item pesanan
                                        $items_query = mysqli_query($conn, "
                                            SELECT oi.*, p.name as product_name 
                                            FROM order_items oi
                                            LEFT JOIN products p ON oi.product_id = p.id
                                            WHERE oi.order_id = '{$order['id']}'
                                        ");
                                        
                                        while($item = mysqli_fetch_assoc($items_query)):
                                        ?>
                                            <li>
                                                <span><?= htmlspecialchars($item['product_name']) ?> (x<?= $item['quantity'] ?>)</span>
                                                <span>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></span>
                                            </li>
                                        <?php endwhile; ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="order-actions">
                                <?php if ($order['status'] == 'pending'): ?>
                                    <a href="cancel_order.php?id=<?= $order['id'] ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin membatalkan pesanan ini?');">
                                        <i class="fas fa-times"></i> Batalkan
                                    </a>
                                <?php endif; ?>
                                
                                <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-history">
                        <p>Anda belum memiliki riwayat pesanan</p>
                        <a href="../index.php#menu" class="btn btn-primary" style="margin-top: 2rem;">
                            <i class="fas fa-shopping-cart"></i> Belanja Sekarang
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