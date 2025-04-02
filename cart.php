<?php
require 'proses/functions.php';
session_start();

if (!isset($_SESSION["login"])) {
    header("Location: login/login.php");
    exit;
}

$user_id = $_SESSION['id']; // Ambil user_id dari session

// Update quantity
if (isset($_POST['update_update_btn'])) {
    $update_value = (int)$_POST['update_quantity'];
    $update_id = (int)$_POST['update_quantity_id'];
    
    if ($update_value > 0) {
        $update_quantity_query = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ? AND user_id = ?");
        $update_quantity_query->bind_param("iii", $update_value, $update_id, $user_id);
        $update_quantity_query->execute();
    }
    header('location:cart.php');
}

// Remove item
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    $delete_query = $conn->prepare("DELETE FROM `cart` WHERE id = ? AND user_id = ?");
    $delete_query->bind_param("ii", $remove_id, $user_id);
    $delete_query->execute();
    header('location:cart.php');
}

// Clear cart
if (isset($_GET['delete_all'])) {
    $delete_all_query = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_all_query->bind_param("i", $user_id);
    $delete_all_query->execute();
    header('location:cart.php');
}

// Hitung jumlah item di cart untuk user ini
$cart_count_query = $conn->prepare("SELECT COUNT(*) as count FROM `cart` WHERE user_id = ?");
$cart_count_query->bind_param("i", $user_id);
$cart_count_query->execute();
$cart_count = $cart_count_query->get_result()->fetch_assoc()['count'];
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
        .shopping-cart {
            padding: 2rem;
            margin-top: 9.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        
        th, td {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: var(--main-color);
            color: var(--black);
        }
        
        img {
            max-width: 100px;
            height: auto;
        }
        
        .delete-btn {
            color: var(--red);
        }
        
        .disabled {
            opacity: 0.5;
            pointer-events: none;
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
                    <th>hapus barang</th>
                </thead>

                <tbody>
                    <?php
                    $select_cart = $conn->prepare("
                        SELECT c.id, c.quantity, p.name, p.price, p.image 
                        FROM `cart` c
                        JOIN `products` p ON c.product_id = p.id
                        WHERE c.user_id = ?
                    ");
                    $select_cart->bind_param("i", $user_id);
                    $select_cart->execute();
                    $cart_items = $select_cart->get_result();
                    
                    $grand_total = 0;
                    
                    if ($cart_items->num_rows > 0) {
                        while ($fetch_cart = $cart_items->fetch_assoc()) {
                            $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
                            $grand_total += $sub_total;
                    ?>
                            <tr>
                                <td><img src="images/<?php echo htmlspecialchars($fetch_cart['image']); ?>" height="100" alt=""></td>
                                <td><?php echo htmlspecialchars($fetch_cart['name']); ?></td>
                                <td>Rp<?php echo number_format($fetch_cart['price'], 0, ',', '.'); ?></td>
                                <td>
                                    <form action="" method="post">
                                        <input type="hidden" name="update_quantity_id" value="<?php echo $fetch_cart['id']; ?>">
                                        <input type="number" name="update_quantity" min="1" value="<?php echo $fetch_cart['quantity']; ?>">
                                        <input type="submit" value="update" name="update_update_btn" class="btn">
                                    </form>
                                </td>
                                <td>Rp<?php echo number_format($sub_total, 0, ',', '.'); ?></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $fetch_cart['id']; ?>" 
                                       onclick="return confirm('remove item from cart?')" 
                                       class="delete-btn">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="6" style="text-align:center;">Keranjang belanja kosong</td></tr>';
                    }
                    ?>
                    <tr class="table-bottom">
                        <td><a href="index.php#menu" class="option-btn" style="margin-top: 0;">Kembali ke Toko</a></td>
                        <td colspan="3">Total Harga</td>
                        <td>Rp<?php echo number_format($grand_total, 0, ',', '.'); ?></td>
                        <td>
                            <a href="cart.php?delete_all" 
                               onclick="return confirm('Apakah anda ingin mengosongkan keranjang?');" 
                               class="delete-btn">
                                <i class="fas fa-trash"></i> Hapus Semua
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="checkout-btn">
                <a href="checkout.php" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>">Checkout</a>
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
    </section>
    <!-- footer section ends -->

    <script src="js/script.js"></script>
</body>
</html>