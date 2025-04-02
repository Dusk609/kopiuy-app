<?php
session_start();
require 'proses/functions.php';

if (!isset($_SESSION["login"])) {
    header("Location: login/login.php");
    exit;
}

$user_id = $_SESSION['id'];

if (isset($_POST['order_btn'])) {
    // Pilihan 1: Gunakan alamat yang sudah ada
    if (isset($_POST['address_id']) && !empty($_POST['address_id'])) {
        $address_id = $_POST['address_id'];
        $address = mysqli_fetch_assoc(mysqli_query($conn, 
            "SELECT * FROM addresses WHERE id = '$address_id' AND user_id = '$user_id'"));
        
        $name = $address['name'];
        $number = $address['number'];
        $jalan = $address['jalan'];
        $alamat = $address['alamat'];
        $kota = $address['kota'];
        $provinsi = $address['provinsi'];
        $negara = $address['negara'];
        $pos_kode = $address['pos_kode'];
    } 
    // Pilihan 2: Gunakan alamat baru
    else {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $number = mysqli_real_escape_string($conn, $_POST['number']);
        $jalan = mysqli_real_escape_string($conn, $_POST['jalan']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        $kota = mysqli_real_escape_string($conn, $_POST['kota']);
        $provinsi = mysqli_real_escape_string($conn, $_POST['provinsi']);
        $negara = mysqli_real_escape_string($conn, $_POST['negara']);
        $pos_kode = mysqli_real_escape_string($conn, $_POST['pos_kode']);
        
        // Simpan sebagai alamat baru
        mysqli_query($conn, "INSERT INTO addresses (user_id, name, number, jalan, alamat, kota, provinsi, negara, pos_kode) 
                            VALUES ('$user_id', '$name', '$number', '$jalan', '$alamat', '$kota', '$provinsi', '$negara', '$pos_kode')");
        $address_id = mysqli_insert_id($conn);
    }
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $metode = mysqli_real_escape_string($conn, $_POST['metode']);
    
    // Proses cart
    $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'");
    $price_total = 0;
    $product_name = array();
    
    if (mysqli_num_rows($cart_query) > 0) {
        while ($product_item = mysqli_fetch_assoc($cart_query)) {
            $product_name[] = $product_item['name'] . ' (' . $product_item['quantity'] . ') ';
            $product_price = $product_item['price'] * $product_item['quantity'];
            $price_total += $product_price;
        }
    }
    
    $total_product = implode(', ', $product_name);
    
    // Simpan order
    $detail_query = mysqli_query($conn, 
      "INSERT INTO `order` (address_id, name, number, email, metode, jalan, alamat, kota, provinsi, negara, pos_kode, total_products, total_price, user_id) 
      VALUES ('$address_id', '$name', '$number', '$email', '$metode', '$jalan', '$alamat', '$kota', '$provinsi', '$negara', '$pos_kode', '$total_product', '$price_total', '$user_id')");

    
    if ($detail_query) {
        // Kosongkan cart
        mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'");
        
        echo "
        <div class='order-message-container'>
        <div class='message-container'>
           <h3>Terima Kasih Telah Membeli!</h3>
           <div class='order-detail'>
              <span>" . $total_product . "</span>
              <span class='total'> total : Rp" . number_format($price_total, 0, ',', '.') . " </span>
           </div>
           <div class='customer-details'>
              <p> Nama : <span>" . $name . "</span> </p>
              <p> Nomer HP : <span>" . $number . "</span> </p>
              <p> Email : <span>" . $email . "</span> </p>
              <p> Alamat : <span>" . $jalan . ", " . $alamat . ", " . $kota . ", " . $provinsi . ", " . $negara . " - " . $pos_kode . "</span> </p>
              <p> Tipe Pembayaran : <span>" . $metode . "</span> </p>
           </div>
              <a href='index.php' class='btn'>Kembali ke Toko</a>
           </div>
        </div>
        ";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>


?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="css/checkout.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
   <title>Coffee Shop</title>

   <style>
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
       }
       .address-option {
           padding: 1rem;
       }
       .use-new-address {
           margin-top: 1rem;
           display: block;
           color: var(--main-color);
           cursor: pointer;
       }
       .new-address-form {
           display: none;
           margin-top: 2rem;
       }
   </style>

</head>

<body>

   <!-- header section starts  -->

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
         <div class="fas fa-search" id="search-btn"></div>
         <div class="fas fa-shopping-cart" id="cart-btn">
            <?php

            $select_rows = mysqli_query($conn, "SELECT * FROM `cart`") or die('query failed');
            $row_count = mysqli_num_rows($select_rows);

            ?>

            <a href="cart.php" class="cart_row">
               <span>
                  <?php echo $row_count; ?>
               </span>
            </a>
         </div>
         <div class="fas fa-bars" id="menu-btn"></div>
         <div class="cart">

         </div>
      </div>

      <div class="search-form">
         <input type="search" id="search-box" placeholder="search here...">
         <label for="search-box" class="fas fa-search"></label>
      </div>
   </header>

   <!-- header section ends -->

   <!-- Checkout Start -->
   <br><br><br><br><br><br><br><br>
   <div class="container">
      <section class="checkout-form">
         <h1 class="heading">isi <span>data diri</span> pembeli</h1>

         <form action="" method="post">
            <!-- Tampilkan produk di cart -->
            <div class="display-order">
               <?php
               $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'");
               $grand_total = 0;
               if (mysqli_num_rows($select_cart) > 0) {
                  while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                     $total_price = $fetch_cart['price'] * $fetch_cart['quantity'];
                     $grand_total += $total_price;
               ?>
                     <span><?= $fetch_cart['name']; ?>(<?= $fetch_cart['quantity']; ?>)</span>
               <?php
                  }
               } else {
                  echo "<div class='display-order'><span>Keranjang anda kosong!</span></div>";
               }
               ?>
               <span class="grand-total"> Total Harga : Rp<?= number_format($grand_total, 0, ',', '.'); ?> </span>
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
                        <option value="<?= $address['id'] ?>" class="address-option">
                           <?= $address['name'] ?> - <?= $address['jalan'] ?>, <?= $address['kota'] ?>
                        </option>
                     <?php endwhile; ?>
                  </select>
                  <span class="use-new-address" onclick="showNewAddressForm()">Gunakan alamat baru</span>
               <?php } ?>
            </div>

            <!-- Form alamat baru (awalnya tersembunyi) -->
            <div id="new-address-form" class="new-address-form">
               <div class="flex">
                  <div class="inputBox">
                     <span>Nama Penerima</span>
                     <input type="text" name="name">
                  </div>
                  <div class="inputBox">
                     <span>Nomer HP</span>
                     <input type="text" name="number">
                  </div>
                  <div class="inputBox">
                     <span>Email</span>
                     <input type="email" name="email" value="<?= $_SESSION['email'] ?? '' ?>" required>
                  </div>
                  <div class="inputBox">
                     <span>Tipe Pembayaran</span>
                     <select name="metode">
                        <option value="COD" selected>COD</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="GoPay">GoPay</option>
                     </select>
                  </div>
                  <div class="inputBox">
                     <span>Jalan</span>
                     <input type="text" name="jalan">
                  </div>
                  <div class="inputBox">
                     <span>Alamat Lengkap</span>
                     <input type="text" name="alamat">
                  </div>
                  <div class="inputBox">
                     <span>Kota</span>
                     <input type="text" name="kota">
                  </div>
                  <div class="inputBox">
                     <span>Provinsi</span>
                     <input type="text" name="provinsi">
                  </div>
                  <div class="inputBox">
                     <span>Negara</span>
                     <input type="text" name="negara" value="Indonesia">
                  </div>
                  <div class="inputBox">
                     <span>Kode Pos</span>
                     <input type="text" name="pos_kode">
                  </div>
               </div>
            </div>

            <input type="submit" value="Order Sekarang" name="order_btn" id="orderBtn" class="btn">
         </form>
      </section>
   </div>

   <!-- Checkout End -->

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
           document.getElementById('address-select').value = '';
       }
       
       // Tampilkan form baru jika tidak ada alamat yang tersimpan
       <?php if (mysqli_num_rows($addresses) == 0): ?>
           document.getElementById('new-address-form').style.display = 'block';
       <?php endif; ?>
   </script>

</body>

</html>