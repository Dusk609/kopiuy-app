<?php

require 'proses/functions.php';


session_start();

if (isset($_POST['order_btn'])) {

   $name = $_POST['name'];
   $number = $_POST['number'];
   $email = $_POST['email'];
   $metode = $_POST['metode'];
   $jalan = $_POST['jalan'];
   $alamat = $_POST['alamat'];
   $kota = $_POST['kota'];
   $provinsi = $_POST['provinsi'];
   $negara = $_POST['negara'];
   $pos_kode = $_POST['pos_kode'];

   $cart_query = mysqli_query($conn, "SELECT * FROM `cart`");
   $price_total = 0;
   if (mysqli_num_rows($cart_query) > 0) {
      while ($product_item = mysqli_fetch_assoc($cart_query)) {
         $product_name[] = $product_item['name'] . ' (' . $product_item['quantity'] . ') ';
         $product_price = number_format($product_item['price'] * $product_item['quantity']);
         $price_total += $product_price;
      };
   };

   $total_product = implode(', ', $product_name);
   $detail_query = mysqli_query($conn, "INSERT INTO `order`(name, number, email, metode, jalan, alamat, kota, provinsi, negara, pos_kode, total_products, total_price) VALUES('$name','$number','$email','$metode','$jalan','$alamat','$kota','$provinsi','$negara','$pos_kode','$total_product','$price_total')") or die('query failed');

   if ($cart_query && $detail_query) {
      echo "
      <div class='order-message-container'>
      <div class='message-container'>
         <h3>Terima Kasih Telah Membeli!</h3>
         <div class='order-detail'>
            <span>" . $total_product . "</span>
            <span class='total'> total : Rp" . $price_total . ".000/-  </span>
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

            <div class="display-order">
               <?php
               $select_cart = mysqli_query($conn, "SELECT * FROM `cart`");
               $total = 0;
               $grand_total = 0;
               if (mysqli_num_rows($select_cart) > 0) {
                  while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                     $total_price = number_format($fetch_cart['price'] * $fetch_cart['quantity'], 0, ',', '.');
                     $grand_total = $total += $total_price;
               ?>
                     <span><?= $fetch_cart['name']; ?>(<?= $fetch_cart['quantity']; ?>)</span>
               <?php
                  }
               } else {
                  echo "<div class='display-order'><span>your cart is empty!</span></div>";
               }
               ?>
               <span class="grand-total"> Total Harga : Rp<?= $grand_total; ?>.000/- </span>
            </div>

            <div class="flex">
               <div class="inputBox">
                  <span>Nama</span>
                  <input type="text" name="name" required>
               </div>
               <div class="inputBox">
                  <span>Nomer HP</span>
                  <input type="text" name="number" required>
               </div>
               <div class="inputBox">
                  <span>Email</span>
                  <input type="email" name="email" required>
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
                  <input type="text" name="jalan" required>
               </div>
               <div class="inputBox">
                  <span>Alamat</span>
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
                  <input type="text" name="negara" required>
               </div>
               <div class="inputBox">
                  <span>Kode Pos</span>
                  <input type="text" name="pos_kode" required>
               </div>
            </div>
            <input type="submit" value="Order Sekarang" name="order_btn" class="btn">
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

</body>

</html>