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
    mysqli_query($conn, "INSERT INTO users VALUES('', '$username', '$email', '$password')");
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
