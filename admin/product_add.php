<?php
session_start();
require '../config/database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$notification = null;

// Proses form tambah produk
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $description = $_POST['description'] ?? '';
    
    try {
        $pdo = Database::getInstance()->getConnection();
        
        // Handle file upload
        $image = 'default.jpg'; // default image
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../images/';
            $fileExt = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;
            
            // Validasi file
            $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
            if(in_array(strtolower($fileExt), $allowedTypes)) {
                if(move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = $fileName;
                } else {
                    throw new Exception("Gagal mengupload gambar");
                }
            } else {
                throw new Exception("Format file tidak didukung. Gunakan JPG, PNG, atau WEBP");
            }
        }
        
        // Insert ke database
        $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, description, image) 
                              VALUES (:name, :price, :stock, :description, :image)");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        
        if($stmt->execute()) {
            $notification = [
                'type' => 'success',
                'message' => 'Produk berhasil ditambahkan'
            ];
            // Reset form
            $_POST = [];
        } else {
            $notification = [
                'type' => 'error',
                'message' => 'Gagal menambahkan produk'
            ];
        }
    } catch (Exception $e) {
        $notification = [
            'type' => 'error',
            'message' => $e->getMessage()
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk Baru | KopiUy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (gunakan style yang sama dengan order_edit.php) ... */
        .image-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 0.5rem;
            border: var(--border);
            margin-bottom: 1rem;
            display: none;
        }
    </style>
</head>
<body>
    <header class="header">
        <!-- ... (sama dengan products.php) ... -->
    </header>

    <section class="order-edit-container">
        <h1 class="heading">Tambah <span>Produk Baru</span></h1>
        
        <form action="" method="POST" class="edit-form" enctype="multipart/form-data" id="product-form">
            <div class="form-group">
                <label for="name" class="form-label">Nama Produk</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="price" class="form-label">Harga</label>
                <input type="number" id="price" name="price" class="form-control" 
                       value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="stock" class="form-label">Stok</label>
                <input type="number" id="stock" name="stock" class="form-control" 
                       value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea name="description" id="description" class="form-control"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="image" class="form-label">Gambar Produk</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <img id="imagePreview" class="image-preview" src="#" alt="Preview Gambar">
            </div>
            
            <div class="form-group" style="text-align: center; margin-top: 3rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Produk
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
                const preview = document.getElementById('imagePreview');
                preview.src = event.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>