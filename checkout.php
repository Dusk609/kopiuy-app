<?php
require 'proses/functions.php';
session_start();

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION["login"]) || !isset($_SESSION['id'])) {
    header("Location: login/login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Debug info
error_log("User ID: " . $user_id);

// Hitung total belanja
$grand_total = 0;
$cart_items = [];
$select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'");
if (mysqli_num_rows($select_cart) > 0) {
    while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
        $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
        $grand_total += $sub_total;
        $cart_items[] = $fetch_cart['name'] . ' (' . $fetch_cart['quantity'] . ')';
    }
}

// Proses order jika tombol ditekan
if (isset($_POST['order_btn']) && $grand_total > 0) {
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // Proses alamat pengiriman
        if (isset($_POST['address_id']) && !empty($_POST['address_id'])) {
            $address_id = mysqli_real_escape_string($conn, $_POST['address_id']);
            $address = mysqli_fetch_assoc(mysqli_query($conn, 
                "SELECT * FROM addresses WHERE id = '$address_id' AND user_id = '$user_id'"));
            
            $name = $address['name'];
            $number = $address['number'];
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $metode = mysqli_real_escape_string($conn, $_POST['metode']);
            $jalan = $address['jalan'];
            $alamat = $address['alamat'];
            $kota = $address['kota'];
            $provinsi = $address['provinsi'];
            $negara = $address['negara'];
            $pos_kode = $address['pos_kode'];
        } else {
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $number = mysqli_real_escape_string($conn, $_POST['number']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $metode = mysqli_real_escape_string($conn, $_POST['metode']);
            $jalan = mysqli_real_escape_string($conn, $_POST['jalan']);
            $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
            $kota = mysqli_real_escape_string($conn, $_POST['kota']);
            $provinsi = mysqli_real_escape_string($conn, $_POST['provinsi']);
            $negara = mysqli_real_escape_string($conn, $_POST['negara']);
            $pos_kode = mysqli_real_escape_string($conn, $_POST['pos_kode']);
            
            // Simpan alamat baru
            mysqli_query($conn, "INSERT INTO addresses (user_id, name, number, jalan, alamat, kota, provinsi, negara, pos_kode) 
                                VALUES ('$user_id', '$name', '$number', '$jalan', '$alamat', '$kota', '$provinsi', '$negara', '$pos_kode')");
            $address_id = mysqli_insert_id($conn);
        }
        
        // Simpan order
        $total_products = implode(', ', $cart_items);
        $order_query = mysqli_query($conn, 
            "INSERT INTO `order` (user_id, name, number, email, metode, jalan, alamat, kota, provinsi, negara, pos_kode, total_products, total_price) 
            VALUES ('$user_id', '$name', '$number', '$email', '$metode', '$jalan', '$alamat', '$kota', '$provinsi', '$negara', '$pos_kode', '$total_products', '$grand_total')");
        
        if (!$order_query) {
            throw new Exception("Gagal menyimpan order: " . mysqli_error($conn));
        }
        
        $order_id = mysqli_insert_id($conn);
        
        // Simpan order items
        $cart_items_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'");
        while ($item = mysqli_fetch_assoc($cart_items_query)) {
            $insert_item = mysqli_query($conn, 
                "INSERT INTO order_items (order_id, product_id, product_name, price, quantity) 
                VALUES ('$order_id', '{$item['product_id']}', '{$item['name']}', '{$item['price']}', '{$item['quantity']}')");
            
            if (!$insert_item) {
                throw new Exception("Gagal menyimpan item order: " . mysqli_error($conn));
            }
        }
        
        // Kosongkan cart
        mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'");
        
        // Commit transaksi
        mysqli_commit($conn);
        
        // Tampilkan pesan sukses
        $success_message = "
        <div class='order-message-container'>
            <div class='message-container'>
               <h3>Terima Kasih Telah Membeli!</h3>
               <div class='order-detail'>
                  <span>".$total_products."</span>
                  <span class='total'>Total: Rp".number_format($grand_total, 0, ',', '.')."</span>
               </div>
               <div class='customer-details'>
                  <p>Nama: <span>".$name."</span></p>
                  <p>No. HP: <span>".$number."</span></p>
                  <p>Email: <span>".$email."</span></p>
                  <p>Alamat: <span>".$jalan.", ".$alamat.", ".$kota.", ".$provinsi.", ".$negara." - ".$pos_kode."</span></p>
                  <p>Metode Pembayaran: <span>".$metode."</span></p>
               </div>
               <a href='index.php' class='btn'>Kembali ke Toko</a>
            </div>
        </div>";
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        $error_message = "<div class='error-message'>".$e->getMessage()."</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .checkout-form {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .heading {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            color: var(--main-color);
        }
        .display-order {
            background: var(--black);
            padding: 1.5rem;
            border-radius: .5rem;
            margin-bottom: 2rem;
            border: var(--border);
        }
        .display-order span {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 1.6rem;
        }
        .grand-total {
            font-weight: bold;
            color: var(--main-color);
            margin-top: 1rem;
        }
        .address-select {
            margin-bottom: 2rem;
        }
        .address-select select {
            width: 100%;
            padding: 1rem;
            border: var(--border);
            background: var(--bg);
            color: var(--white);
            font-size: 1.6rem;
            margin-bottom: 1rem;
        }
        .use-new-address {
            color: var(--main-color);
            cursor: pointer;
            text-decoration: underline;
        }
        .new-address-form {
            display: none;
            margin-top: 2rem;
        }
        .flex {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .inputBox {
            margin-bottom: 1.5rem;
        }
        .inputBox span {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 1.6rem;
            color: var(--main-color);
        }
        .inputBox input, .inputBox select {
            width: 100%;
            padding: 1rem;
            border: var(--border);
            background: var(--bg);
            color: var(--white);
            font-size: 1.6rem;
            border-radius: .5rem;
        }
        .btn {
            display: inline-block;
            padding: 1rem 3rem;
            background: var(--main-color);
            color: var(--black);
            font-size: 1.6rem;
            font-weight: bold;
            border-radius: .5rem;
            cursor: pointer;
            margin-top: 2rem;
            text-align: center;
            width: 100%;
            border: none;
        }
        .btn:hover {
            background: var(--white);
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .order-message-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .message-container {
            background: var(--black);
            padding: 3rem;
            border-radius: .5rem;
            max-width: 600px;
            width: 90%;
            border: var(--border);
        }
        .error-message {
            color: #f00;
            font-size: 1.6rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(255,0,0,.1);
            border-radius: .5rem;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
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
        </nav>
        <div class="icons">
            <div class="fas fa-shopping-cart" id="cart-btn">
                <?php
                $select_rows = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
                $row_count = mysqli_num_rows($select_rows);
                ?>
                <a href="cart.php" class="cart_row">
                    <span><?php echo $row_count; ?></span>
                </a>
            </div>
            <div class="fas fa-bars" id="menu-btn"></div>
        </div>
    </header>

    <!-- Checkout Section -->
    <section class="checkout-form">
        <h1 class="heading">Checkout <span>Pesanan</span></h1>

        <?php if(isset($error_message)) echo $error_message; ?>
        <?php if(isset($success_message)) { 
            echo $success_message; 
        } else { ?>
        
        <form action="" method="post">
            <!-- Tampilkan produk di cart -->
            <div class="display-order">
                <?php
                if ($grand_total > 0) {
                    foreach ($cart_items as $item) {
                        echo "<span>$item</span>";
                    }
                    echo "<span class='grand-total'>Total Harga: Rp".number_format($grand_total, 0, ',', '.')."</span>";
                } else {
                    echo "<span>Keranjang belanja Anda kosong!</span>";
                }
                ?>
            </div>

            <!-- Pilih alamat yang sudah ada -->
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
                                <?= $address['name'] ?> - <?= $address['jalan'] ?>, <?= $address['kota'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <span class="use-new-address" onclick="document.getElementById('new-address-form').style.display='block'; document.getElementById('address-select').value=''">
                        Gunakan alamat baru
                    </span>
                <?php } else { ?>
                    <div id="new-address-form" style="display:block;"></div>
                <?php } ?>
            </div>

            <!-- Form alamat baru -->
            <div id="new-address-form" class="new-address-form" <?= (mysqli_num_rows($addresses) == 0 ? 'style="display:block;"' : '') ?>>
                <div class="flex">
                    <div class="inputBox">
                        <span>Nama Penerima</span>
                        <input type="text" name="name" required>
                    </div>
                    <div class="inputBox">
                        <span>Nomor HP</span>
                        <input type="text" name="number" required>
                    </div>
                    <div class="inputBox">
                        <span>Email</span>
                        <input type="email" name="email" value="<?= $_SESSION['email'] ?? '' ?>" required>
                    </div>
                    <div class="inputBox">
                        <span>Metode Pembayaran</span>
                        <select name="metode" required>
                            <option value="COD" selected>COD</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="E-Wallet">E-Wallet</option>
                        </select>
                    </div>
                    <div class="inputBox">
                        <span>Jalan</span>
                        <input type="text" name="jalan" required>
                    </div>
                    <div class="inputBox">
                        <span>Alamat Lengkap</span>
                        <input type="text" name="alamat" required>
                    </div>
                    <div class="inputBox">
                        <span>Kota</span>
                        <input type="text" name="kota" required>
                    </div>
                    <div class="inputBox">
                        <span>Provinsi</span>
                        <input type="text" name="provinsi" required>
                    </div>
                    <div class="inputBox">
                        <span>Negara</span>
                        <input type="text" name="negara" value="Indonesia" required>
                    </div>
                    <div class="inputBox">
                        <span>Kode Pos</span>
                        <input type="text" name="pos_kode" required>
                    </div>
                </div>
            </div>

            <input type="submit" value="Order Sekarang" name="order_btn" class="btn" <?= ($grand_total <= 0) ? 'disabled' : '' ?>>
        </form>
        
        <?php } ?>
    </section>

    <!-- Footer Section -->
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

    <script>
        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const grandTotal = <?= $grand_total ?>;
            if (grandTotal <= 0) {
                e.preventDefault();
                alert('Keranjang belanja Anda kosong!');
                return false;
            }
            
            // Validasi alamat
            const addressSelect = document.getElementById('address-select');
            const newAddressForm = document.getElementById('new-address-form');
            
            if (!addressSelect.value && newAddressForm.style.display !== 'block') {
                e.preventDefault();
                alert('Silakan pilih atau tambahkan alamat pengiriman!');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>