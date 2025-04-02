<?php
require '../proses/functions.php';
session_start();

// Atur error reporting untuk development
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);

if (!isset($_SESSION["login"])) {
    header("Location: ../login/login.php");
    exit;
}

// Inisialisasi variabel error
$error = null;

// Inisialisasi data form
$formData = [
    'name' => '',
    'number' => '',
    'jalan' => '',
    'alamat' => '',
    'kota' => '',
    'provinsi' => '',
    'negara' => 'Indonesia',
    'pos_kode' => '',
    'is_default' => 0
];

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['id'];
    
    // Validasi dan sanitasi input
    $formData = [
        'user_id' => $user_id,
        'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
        'number' => filter_input(INPUT_POST, 'number', FILTER_SANITIZE_STRING),
        'email' => mysqli_fetch_assoc(mysqli_query($conn, "SELECT email FROM users WHERE id = '$user_id'"))['email'],
        'jalan' => filter_input(INPUT_POST, 'jalan', FILTER_SANITIZE_STRING),
        'alamat' => filter_input(INPUT_POST, 'alamat', FILTER_SANITIZE_STRING),
        'kota' => filter_input(INPUT_POST, 'kota', FILTER_SANITIZE_STRING),
        'provinsi' => filter_input(INPUT_POST, 'provinsi', FILTER_SANITIZE_STRING),
        'negara' => filter_input(INPUT_POST, 'negara', FILTER_SANITIZE_STRING) ?: 'Indonesia',
        'pos_kode' => filter_input(INPUT_POST, 'pos_kode', FILTER_SANITIZE_STRING),
        'is_default' => isset($_POST['is_default']) ? 1 : 0
    ];

    // Validasi data yang diperlukan
    if (empty($formData['name']) || empty($formData['number']) || empty($formData['jalan']) || 
        empty($formData['alamat']) || empty($formData['kota']) || empty($formData['provinsi']) || 
        empty($formData['pos_kode'])) {
        $error = "Semua field wajib diisi!";
    } else {
        // Jika dijadikan default, update yang lain menjadi tidak default
        if ($formData['is_default'] == 1) {
            mysqli_query($conn, "UPDATE addresses SET is_default = 0 WHERE user_id = '$user_id'");
        }
        
        // Escape string untuk keamanan
        $escapedData = array_map(function($item) use ($conn) {
            return mysqli_real_escape_string($conn, $item);
        }, $formData);
        
        $columns = implode(", ", array_keys($escapedData));
        $values = "'" . implode("', '", array_values($escapedData)) . "'";
        
        $query = "INSERT INTO `addresses` ($columns) VALUES ($values)";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Alamat berhasil ditambahkan";
            header("Location: address.php");
            exit;
        } else {
            $error = "Gagal menambahkan alamat: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Alamat | Coffee Shop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .address-form-section {
            padding: 2rem 7%;
            margin-top: 9.5rem;
            color: var(--white);
        }
        
        .address-form-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--black);
            border: var(--border);
            border-radius: .5rem;
            padding: 3rem;
        }
        
        .address-form-container h1 {
            font-size: 3rem;
            color: var(--main-color);
            margin-bottom: 2rem;
            text-transform: uppercase;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 2rem;
        }
        
        .form-group label {
            display: block;
            font-size: 1.6rem;
            color: var(--main-color);
            margin-bottom: 1rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1.5rem;
            font-size: 1.6rem;
            color: var(--white);
            background: var(--bg);
            border: var(--border);
            border-radius: .5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
        }
        
        .btn-submit {
            background: var(--main-color);
            color: var(--black);
            padding: 1.5rem 3rem;
            font-size: 1.6rem;
            font-weight: bold;
            border-radius: .5rem;
            cursor: pointer;
        }
        
        .btn-cancel {
            background: var(--red);
            color: var(--white);
            padding: 1.5rem 3rem;
            font-size: 1.6rem;
            font-weight: bold;
            border-radius: .5rem;
            cursor: pointer;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .checkbox-group input {
            width: auto;
        }
        
        .error-message {
            color: var(--red);
            font-size: 1.4rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- header section -->
    <?php include '../partials/header.php'; ?>

    <!-- Address Form Section -->
    <section class="address-form-section">
        <div class="address-form-container">
            <h1>Tambah Alamat Baru</h1>
            
            <?php if (isset($error)): ?>
                <div class="error-message" style="margin-bottom: 2rem; text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nama Penerima</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($formData['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="number">Nomor Telepon</label>
                        <input type="text" id="number" name="number" value="<?= htmlspecialchars($formData['number']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="jalan">Nama Jalan</label>
                    <input type="text" id="jalan" name="jalan" value="<?= htmlspecialchars($formData['jalan']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="alamat">Detail Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($formData['alamat']) ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kota">Kota</label>
                        <input type="text" id="kota" name="kota" value="<?= htmlspecialchars($formData['kota']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="provinsi">Provinsi</label>
                        <input type="text" id="provinsi" name="provinsi" value="<?= htmlspecialchars($formData['provinsi']) ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="negara">Negara</label>
                        <input type="text" id="negara" name="negara" value="<?= htmlspecialchars($formData['negara']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pos_kode">Kode Pos</label>
                        <input type="text" id="pos_kode" name="pos_kode" value="<?= htmlspecialchars($formData['pos_kode']) ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_default" name="is_default" <?= $formData['is_default'] ? 'checked' : '' ?>>
                        <label for="is_default">Jadikan alamat utama</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="address.php" class="btn-cancel">Batal</a>
                    <button type="submit" name="submit" class="btn-submit">Simpan Alamat</button>
                </div>
            </form>
        </div>
    </section>

    <!-- footer section -->
    <?php include '../partials/footer.php'; ?>

    <script src="../js/script.js"></script>
</body>
</html>