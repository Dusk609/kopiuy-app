<?php

$conn = mysqli_connect("localhost", "root", "", "kopiuy");

function query($query)
{
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// kerja signup
function signup($data)
{
    global $conn;
    $username = strtolower(stripslashes($data["username"]));
    $email = strtolower($data["email"]);
    $password = mysqli_real_escape_string($conn, $data["password"]);
    $password2 = mysqli_real_escape_string($conn, $data["password2"]);

    // cek ulang konfirmasi password
    if ($password !== $password2) {
        echo "<script>alert('Konfirmasi password salah');</script>";
        return false;
    }

    // cek username sudah ada atau belum
    $result = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>alert('Username sudah terdaftar');</script>";
        return false;
    }

    // enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Tambah user baru ke db
    mysqli_query($conn, "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')");
    return mysqli_affected_rows($conn);
}

function search($keyword)
{
    $query = "SELECT * FROM cart WHERE
                name LIKE '%$keyword%'  OR
                price LIKE '%$keyword%' OR
                quantity LIKE '%$keyword%'
    ";
    return query($query);
}

function login($username, $password) {
    global $conn;
    
    $username = mysqli_real_escape_string($conn, $username);
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['login'] = true;
            $_SESSION['id'] = $user['id']; // Pastikan ini sesuai dengan kolom di tabel
            $_SESSION['username'] = $user['username'];
            return true;
        }
    }
    return false;
}

function is_logged_in() {
    session_start();
    return isset($_SESSION["login"]) && 
          (isset($_SESSION["user_id"]) || isset($_SESSION["id"]));
}

function get_user_id() {
    return $_SESSION["user_id"] ?? $_SESSION["id"] ?? null;
}

$conn->set_charset("utf8mb4");

//Function to sanitize inputs (add more checks as needed)
function sanitizeInput($conn, $data){
    return $conn->real_escape_string(trim($data));
}

function updateProductStock($product_id, $quantity, $conn) {
    // Decrease product stock
    $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $update_stock->bind_param("ii", $quantity, $product_id);
    return $update_stock->execute();
}
function reduceProductStock($conn, $order_id) {
    // Ambil semua item dari pesanan
    $items_query = mysqli_query($conn, "SELECT product_id, quantity FROM order_items WHERE order_id = '$order_id'");
    
    while ($item = mysqli_fetch_assoc($items_query)) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        
        // Kurangi stok produk
        mysqli_query($conn, "UPDATE products SET stock = stock - $quantity WHERE id = '$product_id'");
    }
}