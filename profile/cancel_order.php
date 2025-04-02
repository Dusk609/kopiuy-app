<?php
require '../proses/functions.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION["login"]) || !isset($_SESSION['id'])) {
    header("Location: ../login/login.php");
    exit;
}

// Check if order ID is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
} else {
    $_SESSION['error'] = "Permintaan tidak valid";
    header("Location: history.php");
    exit;
}

$user_id = $_SESSION['id'];

// Validate and cancel the order
mysqli_begin_transaction($conn);
try {
    // 1. Check if order belongs to user and is cancellable
    $order_query = mysqli_query($conn, 
        "SELECT * FROM `order` 
         WHERE id = '$order_id' AND user_id = '$user_id' AND status = 'pending'");
    
    if (mysqli_num_rows($order_query) == 0) {
        throw new Exception("Pesanan tidak ditemukan atau tidak dapat dibatalkan");
    }

    // 2. Update order status to cancelled
    $update_order = mysqli_query($conn, 
        "UPDATE `order` 
         SET status = 'cancelled', updated_at = NOW() 
         WHERE id = '$order_id'");
    
    if (!$update_order) {
        throw new Exception("Gagal membatalkan pesanan: " . mysqli_error($conn));
    }

    // 3. Optionally restore product stock
    $restore_stock = true; // Set to false if you don't want to restore stock
    
    if ($restore_stock) {
        $items_query = mysqli_query($conn, 
            "SELECT product_id, quantity FROM order_items 
             WHERE order_id = '$order_id'");
        
        while ($item = mysqli_fetch_assoc($items_query)) {
            $update_stock = mysqli_query($conn, 
                "UPDATE products 
                 SET stock = stock + {$item['quantity']} 
                 WHERE id = {$item['product_id']}");
            
            if (!$update_stock) {
                throw new Exception("Gagal mengembalikan stok produk");
            }
        }
    }

    // 4. Commit transaction
    mysqli_commit($conn);
    
    $_SESSION['success'] = "Pesanan #$order_id berhasil dibatalkan";
    header("Location: history.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = $e->getMessage();
    header("Location: history.php");
    exit;
}
?>