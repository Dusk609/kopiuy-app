<?php
session_start();
require '../config/database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($product_id <= 0) {
    header('Location: products.php');
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Ambil nama gambar untuk dihapus
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = :id");
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($product) {
        // Hapus dari database
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            // Hapus gambar jika bukan default
            if($product['image'] !== 'default.jpg') {
                @unlink('../images/' . $product['image']);
            }
            $_SESSION['success'] = 'Produk berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Gagal menghapus produk';
        }
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
}

header('Location: products.php');
exit;
?>