<?php
session_start();
require '../config/database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Ambil data produk
try {
    $pdo = Database::getInstance()->getConnection();
    
    // Query untuk data produk
    $stmt = $pdo->query("SELECT id, name, price, stock, image FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk | KopiUy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --main-color: #d3ad7f;
            --black: #13131a;
            --border: .1rem solid rgba(255,255,255,.3);
        }
        
        .products-container {
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
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background: #13131a;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .products-table th, .products-table td {
            padding: 1.5rem;
            text-align: left;
            border-bottom: var(--border);
            color: #fff;
        }
        
        .products-table th {
            background-color: var(--main-color);
            color: var(--black);
            font-size: 1.8rem;
        }
        
        .products-table td {
            font-size: 1.6rem;
        }
        
        .products-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
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
            font-size: 1.8rem;
        }
        
        .btn-primary {
            background: var(--main-color);
            color: var(--black);
        }
        
        .btn-primary:hover {
            background: #e0b070;
        }
        
        .btn-secondary {
            background: #333;
            color: #fff;
        }
        
        .btn-secondary:hover {
            background: #444;
        }
        
        .btn-danger {
            background: #f44336;
            color: #fff;
        }
        
        .btn-danger:hover {
            background: #e53935;
        }
        
        .add-product {
            display: inline-block;
            margin-bottom: 2rem;
            padding: 1rem 2rem;
            background: var(--main-color);
            color: var(--black);
            border-radius: 0.5rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 1.8rem;
        }
        
        .add-product:hover {
            background: #e0b070;
            transform: translateY(-2px);
        }
        
        .empty-products {
            text-align: center;
            padding: 3rem;
            color: #aaa;
            font-size: 1.8rem;
        }
        
        /* Notification Popup */
        #notification-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateX(100%);
            visibility: hidden;
        }
        
        #notification-popup.show {
            opacity: 1;
            transform: translateX(0);
            visibility: visible;
        }
        
        .notification-content {
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .notification-success {
            background-color: #4CAF50;
        }
        
        .notification-error {
            background-color: #F44336;
        }
        
        .notification-content i {
            margin-right: 10px;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="dashboard.php" class="logo">
            <img src="../images/logo.png" alt="KopiUy Logo" height="60">
        </a>
        
        <nav class="navbar">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="products.php"><i class="fas fa-coffee"></i> Produk</a>
            <a href="orders.php"><i class="fas fa-shopping-cart"></i> Pesanan</a>
            <a href="customers.php"><i class="fas fa-users"></i> Pelanggan</a>
            <a href="revenue_report.php" class="active"><i class="fas fa-money-bill-wave"></i> Pendapatan</a>
        </nav>
        
        <div class="icons">
            <a href="logout.php" class="fas fa-sign-out-alt"></a>
        </div>
    </header>

    <section class="products-container">
        <h1 class="heading">Kelola <span>Produk</span></h1>
        
        <a href="product_add.php" class="add-product">
            <i class="fas fa-plus"></i> Tambah Produk Baru
        </a>
        
        <table class="products-table">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($products)): ?>
                    <tr>
                        <td colspan="6" class="empty-products">Belum ada produk yang tersedia</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($products as $product): ?>
                    <tr>
                        <td>
                            <img src="../images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                        </td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                        <td><?= $product['stock'] ?></td>
                        <td>
                            <a href="product_edit.php?id=<?= $product['id'] ?>" class="btn btn-primary">Edit</a>
                            <a href="product_delete.php?id=<?= $product['id'] ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <!-- Notification Popup -->
    <div id="notification-popup"></div>

    <section class="footer">
        <div class="credit">
            <p>KopiUy Admin Panel &copy; <?= date('Y') ?></p>
        </div>
    </section>

    <script src="../js/admin.js"></script>
    <script>
    // Tampilkan notifikasi jika ada dari URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('success')) {
        showNotification('success', urlParams.get('success'));
    }
    if(urlParams.has('error')) {
        showNotification('error', urlParams.get('error'));
    }
    
    // Fungsi untuk menampilkan notifikasi
    function showNotification(type, message) {
        const popup = document.getElementById('notification-popup');
        popup.innerHTML = `
            <div class="notification-content notification-${type}">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                ${message}
            </div>
        `;
        
        // Tampilkan popup
        popup.classList.add('show');
        
        // Sembunyikan setelah 3 detik
        setTimeout(() => {
            popup.classList.remove('show');
        }, 3000);
    }
    </script>
</body>
</html>