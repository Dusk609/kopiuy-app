<?php
session_start();
require 'proses/functions.php';

if (!isset($_SESSION["login"])) {
    header("Location: login/login.php");
    exit;
}

$user_id = $_SESSION['id'];
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// Initialize variables
$name = $number = $email = $metode = '';
$address_id = 0;
$grand_total = 0;

// Calculate cart total
$cart_query = mysqli_query($conn, 
    "SELECT c.*, p.price as product_price 
     FROM `cart` c 
     JOIN `products` p ON c.product_id = p.id 
     WHERE c.user_id = '$user_id'");

while ($item = mysqli_fetch_assoc($cart_query)) {
    $grand_total += $item['product_price'] * $item['quantity'];
}

// Process order if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_btn'])) {
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Handle address
        if (!empty($_POST['address_id'])) {
            $address_id = (int)$_POST['address_id'];
            if ($address_id > 0) {
                $address_query = mysqli_query($conn, "SELECT * FROM addresses WHERE id = '$address_id' AND user_id = '$user_id'");
                $address = mysqli_fetch_assoc($address_query);
                
                if (!$address) {
                    throw new Exception("Alamat tidak valid");
                }
                $name = $address['name'];
                $number = $address['number'];
                $email = $address['email'];
            }
        } 
        
        // If no address selected or new address provided
        if (empty($address_id)) {
            $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
            $number = mysqli_real_escape_string($conn, $_POST['number'] ?? '');
            $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
            $jalan = mysqli_real_escape_string($conn, $_POST['jalan'] ?? '');
            $alamat = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');
            $kota = mysqli_real_escape_string($conn, $_POST['kota'] ?? '');
            $provinsi = mysqli_real_escape_string($conn, $_POST['provinsi'] ?? '');
            $negara = mysqli_real_escape_string($conn, $_POST['negara'] ?? 'Indonesia');
            $pos_kode = mysqli_real_escape_string($conn, $_POST['pos_kode'] ?? '');
            
            // Insert new address
            $insert_address = mysqli_query($conn, 
                "INSERT INTO addresses (user_id, name, number, email, jalan, alamat, kota, provinsi, negara, pos_kode) 
                 VALUES ('$user_id', '$name', '$number', '$email', '$jalan', '$alamat', '$kota', '$provinsi', '$negara', '$pos_kode')");
            
            if (!$insert_address) {
                throw new Exception("Gagal menyimpan alamat: " . mysqli_error($conn));
            }
            $address_id = mysqli_insert_id($conn);
        }

        // 2. Get payment method
        $metode = mysqli_real_escape_string($conn, $_POST['metode'] ?? 'COD');
        
        // 3. Validate cart is not empty
        $cart_query = mysqli_query($conn, 
            "SELECT c.*, p.name as product_name, p.price as product_price, p.image as product_image 
             FROM `cart` c 
             JOIN `products` p ON c.product_id = p.id 
             WHERE c.user_id = '$user_id'");

        if (mysqli_num_rows($cart_query) == 0) {
            throw new Exception("Keranjang belanja kosong");
        }

        // Calculate totals
        $price_total = 0;
        $item_count = 0;
        $product_details = [];
        
        while ($product_item = mysqli_fetch_assoc($cart_query)) {
            $product_details[] = $product_item;
            $price_total += $product_item['product_price'] * $product_item['quantity'];
            $item_count += $product_item['quantity'];
        }

        // 4. Create order
        $order_query = mysqli_query($conn, 
            "INSERT INTO `order` (user_id, address_id, item_count, total_price, payment_method, status) 
             VALUES ('$user_id', '$address_id', '$item_count', '$price_total', '$metode', 'pending')");
        
        if (!$order_query) {
            throw new Exception("Gagal membuat pesanan: " . mysqli_error($conn));
        }
        $order_id = mysqli_insert_id($conn);
        
        // 5. Save order items
        foreach ($product_details as $product_item) {
            $insert_item = mysqli_query($conn, 
                "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, image) 
                 VALUES (
                    '$order_id', 
                    '{$product_item['product_id']}', 
                    '{$product_item['product_name']}', 
                    '{$product_item['product_price']}', 
                    '{$product_item['quantity']}',
                    '{$product_item['product_image']}'
                 )");
            
            if (!$insert_item) {
                throw new Exception("Gagal menyimpan item pesanan: " . mysqli_error($conn));
            }
        }
        
        // 6. Clear cart
        mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'");
        
        // 7. Commit transaction
        mysqli_commit($conn);

        // Redirect to success page
        echo "<script>alert('Pesanan berhasil! Nomor Pesanan: $order_id'); window.location.href='index.php';</script>";
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("Location: checkout.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Checkout - Coffee Shop</title>
    <style>
        .error-message {
            color: red;
            margin-bottom: 20px;
            padding: 10px;
            background: rgba(255,0,0,0.1);
            border-radius: 5px;
        }
        .address-select {
            color: var(--white);
            margin-bottom: 2rem;
        }
        .address-select select {
            width: 100%;
            padding: 1rem;
            border: var(--border);
            background: var(--bg);
            color: var(--white);
            font-size: 1.6rem;
        }
        .use-new-address {
            margin-top: 1rem;
            display: block;
            color: var(--main-color);
            cursor: pointer;
            text-decoration: underline;
        }
        .new-address-form {
            display: none;
            margin-top: 2rem;
            padding: 2rem;
            background: rgba(255,255,255,0.05);
            border-radius: 0.5rem;
        }
        #orderBtn {
            width: 100%;
            margin-top: 2rem;
            padding: 1.5rem;
            font-size: 1.8rem;
        }
        #orderBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
                <?php
                $cart_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM `cart` WHERE user_id = '$user_id'");
                $row_count = mysqli_fetch_assoc($cart_count)['count'];
                
                if ($row_count == 0) {
                    $_SESSION['error'] = "Keranjang belanja kosong!";
                    header("Location: cart.php");
                    exit;
                }
                ?>
                <a href="cart.php" class="cart_row">
                    <span><?php echo $row_count; ?></span>
                </a>
            </div>
            <div class="fas fa-bars" id="menu-btn"></div>
        </div>
    </header>
    <!-- header section ends -->

    <!-- Checkout Section -->
    <div class="container">
        <section class="checkout-form">
            <h1 class="heading">isi <span>data diri</span> pembeli</h1>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="checkout.php" method="post" id="checkoutForm">
                <!-- Display cart items -->
                <div class="display-order">
                    <?php
                    $select_cart = mysqli_query($conn, 
                        "SELECT c.*, p.name as product_name, p.price as product_price 
                         FROM `cart` c 
                         JOIN `products` p ON c.product_id = p.id 
                         WHERE c.user_id = '$user_id'");
                    $grand_total = 0;
                    if (mysqli_num_rows($select_cart) > 0) {
                        while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                            $total_price = $fetch_cart['product_price'] * $fetch_cart['quantity'];
                            $grand_total += $total_price;
                    ?>
                            <span><?= htmlspecialchars($fetch_cart['product_name']); ?> 
                            (<?= $fetch_cart['quantity']; ?>) - Rp<?= number_format($total_price, 0, ',', '.'); ?></span>
                    <?php
                        }
                        echo "<span class='grand-total'>Total Harga: Rp" . number_format($grand_total, 0, ',', '.') . "</span>";
                    } else {
                        echo "<div class='display-order'><span>Keranjang anda kosong!</span></div>";
                    }
                    ?>
                </div>

                <!-- Address selection -->
                <div class="address-select">
                    <h3>Pilih Alamat Pengiriman</h3>
                    <?php
                    $addresses = mysqli_query($conn, "SELECT * FROM addresses WHERE user_id = '$user_id'");
                    if (mysqli_num_rows($addresses) > 0) {
                    ?>
                        <select name="address_id" id="address-select">
                            <option value="">Pilih Alamat</option>
                            <?php while($address = mysqli_fetch_assoc($addresses)): ?>
                                <option value="<?= $address['id'] ?>">
                                    <?= htmlspecialchars($address['name']) ?> - <?= htmlspecialchars($address['jalan']) ?>, <?= htmlspecialchars($address['kota']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    <?php } else { ?>
                        <input type="hidden" name="address_id" value="0">
                        <p>Tidak ada alamat tersimpan</p>
                    <?php } ?>
                </div>

                <input type="submit" value="Order Sekarang" name="order_btn" id="orderBtn" class="btn" <?= ($grand_total <= 0) ? 'disabled' : '' ?>>
            </form>
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
    </section>
    <!-- footer section ends -->

    <script>
        function showNewAddressForm() {
            document.getElementById('new-address-form').style.display = 'block';
            document.getElementById('address-select').required = false;
        }
        
        // Simple form validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const grandTotal = <?= $grand_total ?>;
            
            // Jika keranjang kosong
            if (grandTotal == 0) {
                e.preventDefault();
                alert('Keranjang belanja kosong!');
                return;
            }
            
            const addressSelect = document.getElementById('address-select');
            const newAddressForm = document.getElementById('new-address-form');
            
            // Jika menggunakan alamat baru
            if (newAddressForm.style.display === 'block') {
                const requiredInputs = newAddressForm.querySelectorAll('input[required]');
                let isValid = true;
                
                requiredInputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.style.borderColor = 'red';
                    } else {
                        input.style.borderColor = '';
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Silakan lengkapi semua field alamat baru');
                    return;
                }
            } 
            // Jika memilih alamat yang sudah ada
            else if (addressSelect && addressSelect.value === '') {
                e.preventDefault();
                alert('Silakan pilih alamat atau isi alamat baru');
                return;
            }
        });
    </script>
</body>
</html>