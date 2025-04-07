<?php
require 'proses/functions.php';
session_start();

if (!isset($_SESSION["login"])) {
    header("Location: login/login.php");
    exit;
}

$user_id = $_SESSION['id'];
$notifications = [];

// Update quantity with stock validation
if (isset($_POST['update_update_btn'])) {
    $update_value = (int)$_POST['update_quantity'];
    $update_id = (int)$_POST['update_quantity_id'];
    
    // Get product info
    $product_query = $conn->prepare("
        SELECT p.id, p.name, p.stock, c.quantity 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.id = ? AND c.user_id = ?
    ");
    $product_query->bind_param("ii", $update_id, $user_id);
    $product_query->execute();
    $product = $product_query->get_result()->fetch_assoc();
    
    if ($update_value > 0) {
        // Check if requested quantity exceeds stock
        if ($update_value > $product['stock']) {
            $notifications[] = [
                'type' => 'error',
                'message' => 'Stok tidak mencukupi untuk ' . htmlspecialchars($product['name']) . '. Stok tersisa: ' . $product['stock']
            ];
        } 
        // Check if exceeds max limit (5)
        elseif ($update_value > 5) {
            $notifications[] = [
                'type' => 'error',
                'message' => 'Maksimal pembelian 5 item per produk'
            ];
        }
        else {
            $update_quantity_query = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ? AND user_id = ?");
            $update_quantity_query->bind_param("iii", $update_value, $update_id, $user_id);
            $update_quantity_query->execute();
            $notifications[] = [
                'type' => 'success',
                'message' => 'Jumlah produk diperbarui'
            ];
        }
    }
}

// Remove item
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    $delete_query = $conn->prepare("DELETE FROM `cart` WHERE id = ? AND user_id = ?");
    $delete_query->bind_param("ii", $remove_id, $user_id);
    $delete_query->execute();
    $notifications[] = [
        'type' => 'success',
        'message' => 'Produk dihapus dari keranjang'
    ];
}

// Clear cart
if (isset($_GET['delete_all'])) {
    $delete_all_query = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_all_query->bind_param("i", $user_id);
    $delete_all_query->execute();
    $notifications[] = [
        'type' => 'success',
        'message' => 'Keranjang belanja dikosongkan'
    ];
}

// Hitung jumlah item di cart untuk user ini
$cart_count_query = $conn->prepare("SELECT COUNT(*) as count FROM `cart` WHERE user_id = ?");
$cart_count_query->bind_param("i", $user_id);
$cart_count_query->execute();
$cart_count = $cart_count_query->get_result()->fetch_assoc()['count'];

// Add low stock notifications
$low_stock_items = $conn->query("
    SELECT p.name, p.stock 
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = $user_id AND p.stock <= 2
");

while ($item = $low_stock_items->fetch_assoc()) {
    $notifications[] = [
        'type' => 'warning',
        'message' => 'Stok ' . htmlspecialchars($item['name']) . ' hampir habis! Tersisa: ' . $item['stock']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Shopping Cart</title>
    <style>
        :root {
            --main-color: #d3ad7f;
            --black: #13131a;
            --white: #ffffff;
            --red: #ff0000;
            --border: .1rem solid rgba(255,255,255,.3);
        }

        .shopping-cart {
            padding: 2rem;
            margin-top: 9.5rem;
            min-height: calc(100vh - 9.5rem);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            background: var(--black);
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        th, td {
            padding: 1.5rem;
            text-align: center;
            border-bottom: var(--border);
            color: var(--white);
        }
        
        th {
            background-color: var(--main-color);
            color: var(--black);
            font-size: 1.8rem;
        }
        
        td {
            font-size: 1.6rem;
            
        }
        
        img {
            max-width: 100px;
            height: auto;
            border-radius: 0.5rem;
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
        
        .delete-btn {
            background: var(--red);
            color: var(--white);
        }
        
        .delete-btn:hover {
            background: #e53935;
        }
        
        .disabled {
            opacity: 0.5;
            pointer-events: none;
        }
        
        .notification {
            position: fixed;
            top: 100px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            animation: slideIn 0.5s, fadeOut 0.5s 2.5s;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .notification i {
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        .notification.success {
            background-color: #4CAF50;
        }
        
        .notification.error {
            background-color: #F44336;
        }
        
        .notification.warning {
            background-color: #FF9800;
        }
        
        @keyframes slideIn {
            from { right: -300px; opacity: 0; }
            to { right: 20px; opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        input[type="number"] {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: var(--border);
            color: var(--white);
            border-radius: 0.5rem;
        }
        
        .stock-warning {
            color: #FF9800;
            font-size: 1.2rem;
            margin-top: 5px;
            display: block;
        }
        
        .checkout-message {
            color: var(--red);
            margin-top: 10px;
            font-size: 1.4rem;
        }
        
        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: #aaa;
            font-size: 1.8rem;
        }
    </style>
</head>
<body>
    <!-- header section starts -->
    <header class="header">
        <a href="index.php" class="logo">
            <img src="images/logo.png" alt="">
        </a>

        <nav class="navbar">
            <a href="index.php#home">home</a>
            <a href="index.php#about">about</a>
            <a href="index.php#menu">menu</a>
            <a href="index.php#products">products</a>
            <a href="index.php#review">review</a>
            <a href="index.php#contact">contact</a>
            <a href="index.php#blogs">blogs</a>
            <a href="profile/profile.php">profile</a>
        </nav>

        <div class="icons">
            <div class="fas fa-shopping-cart" id="cart-btn">
                <a href="cart.php" class="cart_row">
                    <span><?php echo $cart_count; ?></span>
                </a>
            </div>
            <div class="fas fa-bars" id="menu-btn"></div>
        </div>
    </header>
    <!-- header section ends -->

    <!-- Notification Area -->
    <?php foreach ($notifications as $notification): ?>
        <div class="notification <?= $notification['type'] ?>">
            <i class="fas <?= 
                $notification['type'] === 'success' ? 'fa-check-circle' : 
                ($notification['type'] === 'error' ? 'fa-times-circle' : 'fa-exclamation-triangle') 
            ?>"></i>
            <?= $notification['message'] ?>
        </div>
    <?php endforeach; ?>

    <!-- Cart Section -->
    <div class="container">
        <section class="shopping-cart">
            <h1 class="heading">shopping <span>cart</span></h1>

            <table>
                <thead>
                    <th>gambar</th>
                    <th>nama</th>
                    <th>harga</th>
                    <th>jumlah</th>
                    <th>total harga</th>
                    <th>aksi</th>
                </thead>

                <tbody>
                    <?php
                    $select_cart = $conn->prepare("
                        SELECT c.id, c.quantity, p.name, p.price, p.image, p.stock 
                        FROM `cart` c
                        JOIN `products` p ON c.product_id = p.id
                        WHERE c.user_id = ?
                    ");
                    $select_cart->bind_param("i", $user_id);
                    $select_cart->execute();
                    $cart_items = $select_cart->get_result();
                    
                    $grand_total = 0;
                    $can_checkout = true;
                    
                    if ($cart_items->num_rows > 0) {
                        while ($fetch_cart = $cart_items->fetch_assoc()) {
                            $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
                            $grand_total += $sub_total;
                            $max_quantity = min(5, $fetch_cart['stock']); // Limit to 5 or available stock
                            
                            // Check if any item exceeds stock
                            if ($fetch_cart['quantity'] > $fetch_cart['stock']) {
                                $can_checkout = false;
                            }
                    ?>
                            <tr>
                                <td><img src="images/<?= htmlspecialchars($fetch_cart['image']) ?>" height="100" alt=""></td>
                                <td><?= htmlspecialchars($fetch_cart['name']) ?></td>
                                <td>Rp<?= number_format($fetch_cart['price'], 0, ',', '.') ?></td>
                                <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="update_quantity_id" value="<?= $fetch_cart['id'] ?>">
                                        <input type="number" name="update_quantity" min="1" max="<?= $max_quantity ?>" 
                                               value="<?= min($fetch_cart['quantity'], $max_quantity) ?>">
                                        <input type="submit" value="update" name="update_update_btn" class="btn btn-primary">
                                    </form>
                                    <?php if($fetch_cart['stock'] <= 2): ?>
                                        <span class="stock-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Stok tersisa: <?= $fetch_cart['stock'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>Rp<?= number_format($sub_total, 0, ',', '.') ?></td>
                                <td>
                                    <a href="cart.php?remove=<?= $fetch_cart['id'] ?>" 
                                       onclick="return confirm('Hapus item dari keranjang?')" 
                                       class="btn delete-btn">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="6" class="empty-cart">Keranjang belanja kosong</td></tr>';
                    }
                    ?>
                    <tr class="table-bottom">
                        <td><a href="index.php#menu" class="btn btn-primary" style="margin-top: 0;">Lanjut Belanja</a></td>
                        <td colspan="3">Total Harga</td>
                        <td>Rp<?= number_format($grand_total, 0, ',', '.') ?></td>
                        <td>
                            <a href="cart.php?delete_all" 
                               onclick="return confirm('Apakah anda yakin ingin mengosongkan keranjang?');" 
                               class="btn delete-btn">
                                <i class="fas fa-trash"></i> Kosongkan
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="checkout-btn" style="text-align: center;">
                <a href="checkout.php" class="btn btn-primary <?= ($grand_total > 0 && $can_checkout) ? '' : 'disabled' ?>" style="padding: 1rem 3rem; font-size: 1.8rem;">
                    Proses Checkout <i class="fas fa-arrow-right"></i>
                </a>
                <?php if ($grand_total > 0 && !$can_checkout): ?>
                    <p class="checkout-message">
                        <i class="fas fa-exclamation-circle"></i> Tidak dapat checkout karena stok tidak mencukupi
                    </p>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- footer section starts -->
    <section class="footer">
        <div class="share">
            <a href="#" class="fab fa-facebook-f"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
            <a href="#" class="fab fa-pinterest"></a>
        </div>

        <div class="links">
            <a href="index.php#home">home</a>
            <a href="index.php#about">about</a>
            <a href="index.php#menu">menu</a>
            <a href="index.php#products">products</a>
            <a href="index.php#review">review</a>
            <a href="index.php#contact">contact</a>
            <a href="index.php#blogs">blogs</a>
        </div>

        <div class="credit">
            <p>KopiUy &copy; <?= date('Y') ?> | All Rights Reserved</p>
        </div>
    </section>
    <!-- footer section ends -->

    <script src="js/script.js"></script>
    <script>
        // Auto-close notifications after 3 seconds
        setTimeout(() => {
            document.querySelectorAll('.notification').forEach(notification => {
                notification.style.display = 'none';
            });
        }, 3000);
    </script>
</body>
</html>