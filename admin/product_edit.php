<?php
session_start();
require '../config/database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$notification = null;

if($product_id <= 0) {
    header('Location: products.php');
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Ambil data produk
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$product) {
        header('Location: products.php');
        exit;
    }
    
    // Proses update produk
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        $description = $_POST['description'] ?? '';
        $image = $product['image']; // default ke gambar lama
        
        // Handle file upload jika ada gambar baru
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../images/';
            $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;
            
            $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
            if(in_array(strtolower($fileExt), $allowedTypes)) {
                if(move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    // Hapus gambar lama jika bukan default
                    if($product['image'] !== 'default.jpg') {
                        @unlink($uploadDir . $product['image']);
                    }
                    $image = $fileName;
                } else {
                    throw new Exception("Gagal mengupload gambar");
                }
            } else {
                throw new Exception("Format file tidak didukung. Gunakan JPG, PNG, atau WEBP");
            }
        }
        
        // Update database
        $stmt = $pdo->prepare("UPDATE products SET 
                              name = :name, 
                              price = :price, 
                              stock = :stock, 
                              description = :description, 
                              image = :image 
                              WHERE id = :id");
        
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            $notification = [
                'type' => 'success',
                'message' => 'Produk berhasil diperbarui'
            ];
            // Update data produk yang ditampilkan
            $product = array_merge($product, [
                'name' => $name,
                'price' => $price,
                'stock' => $stock,
                'description' => $description,
                'image' => $image
            ]);
        } else {
            $notification = [
                'type' => 'error',
                'message' => 'Gagal memperbarui produk'
            ];
        }
    }
    
} catch (Exception $e) {
    $notification = [
        'type' => 'error',
        'message' => $e->getMessage()
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk #<?= $product_id ?> | KopiUy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (gunakan style yang sama dengan product_add.php) ... */
    </style>
</head>
<body>
    <header class="header">
        <!-- ... (sama dengan products.php) ... -->
    </header>

    <section class="order-edit-container">
        <h1 class="heading">Edit <span>Produk</span> #<?= $product_id ?></h1>
        
        <form action="" method="POST" class="edit-form" enctype="multipart/form-data" id="product-form">
            <div class="form-group">
                <label for="name" class="form-label">Nama Produk</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="price" class="form-label">Harga</label>
                <input type="number" id="price" name="price" class="form-control" 
                       value="<?= htmlspecialchars($product['price']) ?>" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="stock" class="form-label">Stok</label>
                <input type="number" id="stock" name="stock" class="form-control" 
                       value="<?= htmlspecialchars($product['stock']) ?>" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea name="description" id="description" class="form-control"><?= isset($product['description']) ? htmlspecialchars($product['description']) : '' ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="image" class="form-label">Gambar Produk</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <img id="imagePreview" class="image-preview" 
                     src="../images/<?= htmlspecialchars($product['image']) ?>" 
                     alt="Preview Gambar" style="display: block;">
                <small>Biarkan kosong jika tidak ingin mengubah gambar</small>
            </div>
            
            <div class="form-group" style="text-align: center; margin-top: 3rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
        
        <div style="text-align: center; margin-top: 1rem;">
            <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Produk
            </a>
        </div>
    </section>

    <!-- Notification Popup -->
    <div id="notification-popup"></div>

    <section class="footer">
        <div class="credit">
            <p>KopiUy Admin Panel &copy; <?= date('Y') ?></p>
        </div>
    </section>

    <script>
    // Tampilkan notifikasi jika ada
    <?php if(isset($notification)): ?>
        showNotification('<?= $notification['type'] ?>', '<?= addslashes($notification['message']) ?>');
    <?php endif; ?>
    
    // Fungsi untuk menampilkan notifikasi
    function showNotification(type, message) {
        const popup = document.getElementById('notification-popup');
        popup.innerHTML = `
            <div class="notification-content notification-${type}">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                ${message}
            </div>
        `;
        
        popup.classList.add('show');
        setTimeout(() => {
            popup.classList.remove('show');
        }, 3000);
    }
    
    // Preview gambar sebelum upload
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if(file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('imagePreview').src = event.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>