<?php

require 'proses/functions.php';

session_start();


if (!isset($_SESSION["login"])) {
    header("Location: login/login.php");
    exit;
}

if (isset($_POST['update_update_btn'])) {
    $update_value = $_POST['update_quantity'];
    $update_id = $_POST['update_quantity_id'];
    $update_quantity_query = mysqli_query($conn, "UPDATE `cart` SET quantity = '$update_value' WHERE id = '$update_id'");
    if ($update_quantity_query) {
        header('location:cart.php');
    };
};

if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$remove_id'");
    header('location:cart.php');
};

if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM `cart`");
    header('location:cart.php');
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

    <!-- Cart Start -->
    <div class="container">

        <section class="shopping-cart">

            <br><br><br><br><br><br><br><br><br><br>
            <h1 class="heading">shopping <span>cart</span></h1>

            <table>

                <thead>
                    <th>gambar</th>
                    <th>nama</th>
                    <th>harga</th>
                    <th>jumlah</th>
                    <th>total harga</th>
                    <th>hapus barang</th>
                </thead>

                <tbody>

                    <?php

                    $select_cart = mysqli_query($conn, "SELECT * FROM `cart`");
                    $grand_total = 0;
                    if (mysqli_num_rows($select_cart) > 0) {
                        while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                    ?>

                            <tr>
                                <td><img src="images/<?php echo $fetch_cart['image']; ?>" height="100" alt=""></td>
                                <td><?php echo $fetch_cart['name']; ?></td>
                                <td>Rp<?php echo number_format($fetch_cart['price']); ?>/-</td>
                                <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="update_quantity_id" value="<?php echo $fetch_cart['id']; ?>">
                                        <input type="number" name="update_quantity" min="1" value="<?php echo $fetch_cart['quantity']; ?>">
                                        <input type="submit" value="update" name="update_update_btn">
                                    </form>
                                </td>
                                <td>Rp<?php echo $sub_total = number_format($fetch_cart['price'] * $fetch_cart['quantity'], 0, ',', '.'); ?>/-</td>
                                <td><a href="cart.php?remove=<?php echo $fetch_cart['id']; ?>" onclick="return confirm('remove item from cart?')" class="delete-btn"> <i class="fas fa-trash"></i> Hapus</a></td>
                            </tr>
                    <?php
                            $grand_total += $sub_total;
                        };
                    };
                    ?>
                    <tr class="table-bottom">
                        <td><a href="products.php" class="option-btn" style="margin-top: 0;">Kembali ke Toko</a></td>
                        <td colspan="3">Total Harga</td>
                        <td>Rp<?php echo $grand_total; ?>.000/-</td>
                        <td><a href="cart.php?delete_all" onclick="return confirm('Apakah anda ingin mengulang pesanan?');" class="delete-btn"> <i class="fas fa-trash"></i> Hapus Semua </a></td>
                    </tr>

                </tbody>

            </table>

            <div class="checkout-btn">
                <a href="checkout.php" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>">Checkout</a>
            </div>

        </section>

    </div>
    <!-- Cart End -->

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