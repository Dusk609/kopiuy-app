<?php
// 1. Mulai session dan atur error reporting
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1); // Hanya untuk development, matikan di production

// 2. Load konfigurasi database
require __DIR__ . '/../config/database.php';

// 3. Inisialisasi variabel
$stats = [
    'total_orders' => 0,
    'total_products' => 0,
    'total_revenue' => 0,
    'total_customers' => 0
];

// 4. Koneksi database dan query
try {
    // Dapatkan koneksi PDO
    $pdo = Database::getInstance()->getConnection();
    
    // Verifikasi koneksi berhasil
    if (!$pdo) {
        throw new Exception("Gagal mendapatkan koneksi database");
    }
    
    // 5. Query untuk statistik dashboard
    // Total Produk
    $stats['total_products'] = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    
    // Total Pesanan
    $stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM `order`")->fetchColumn();
    
    // Total Pendapatan (hitung dari pesanan completed)
    $stats['total_revenue'] = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM `order` WHERE status = 'completed'")->fetchColumn();
    
    // Total Pelanggan
    $stats['total_customers'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // Set last login time
    $_SESSION['last_login'] = $_SESSION['last_login'] ?? date('Y-m-d H:i:s');

} catch (PDOException $e) {
    // Log error ke file
    error_log("[" . date('Y-m-d H:i:s') . "] Database Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
    
    // Tampilkan pesan aman untuk user
    $error_message = "Terjadi gangguan sistem. Silakan coba lagi nanti.";
    
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] System Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/system_errors.log');
    $error_message = "Terjadi kesalahan sistem.";
}

// 6. Verifikasi login admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 7. Bersihkan output buffer
while (ob_get_level() > 0) {
    ob_end_clean();
}

$recent_orders = [];
try {
    $stmt = $pdo->query("
        SELECT o.id, o.total_price, o.status, o.created_at, 
               u.username AS customer_name 
        FROM `order` o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching recent orders: " . $e->getMessage());
    $recent_orders = []; // Ensure it's always an array
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | KopiUy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        :root {
            --dashboard-bg: #0a0a0a;
            --card-bg: #13131a;
            --card-hover: #1a1a24;
            --text-light: #f0f0f0;
            --text-muted: #aaaaaa;
            --success-light: rgba(76, 175, 80, 0.2);
            --warning-light: rgba(255, 165, 0, 0.2);
            --danger-light: rgba(244, 67, 54, 0.2);
            --info-light: rgba(33, 150, 243, 0.2);
        }

        .dashboard {
            padding: 2rem 5%;
            margin-top: 9.5rem;
            min-height: calc(100vh - 9.5rem);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: var(--border);
        }

        .dashboard-title {
            font-size: 2.8rem;
            color: var(--white);
        }

        .dashboard-title span {
            color: var(--main-color);
        }

        .last-login {
            color: var(--text-muted);
            font-size: 1.4rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: var(--border);
            border-radius: 0.8rem;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            background: var(--card-hover);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--main-color);
        }

        .stat-icon {
            font-size: 2.5rem;
            color: var(--main-color);
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 1.6rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1.5rem;
        }

        .stat-link {
            display: inline-flex;
            align-items: center;
            color: var(--main-color);
            font-size: 1.4rem;
            transition: all 0.3s;
        }

        .stat-link:hover {
            color: var(--white);
            transform: translateX(5px);
        }

        .stat-link i {
            margin-left: 0.5rem;
            font-size: 1.2rem;
        }

        .dashboard-section {
            background: var(--card-bg);
            border: var(--border);
            border-radius: 0.8rem;
            padding: 2.5rem;
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 2rem;
            color: var(--main-color);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 1rem;
        }

        .chart-container {
            height: 300px;
            margin-bottom: 2rem;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table th {
            background: var(--main-color);
            color: var(--black);
            padding: 1.2rem 1.5rem;
            font-size: 2rem;
            text-align: left;
        }

        .order-table td {
            padding: 1.2rem 1.5rem;
            font-size: 1.8rem;
            color: var(--text-light);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .order-table tr:last-child td {
            border-bottom: none;
        }

        .order-table tr:hover td {
            background: rgba(255, 255, 255, 0.03);
        }

        .order-status {
            display: inline-block;
            padding: 0.5rem 1.2rem;
            border-radius: 3rem;
            font-size: 1.3rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background: var(--warning-light);
            color: #FFA500;
        }

        .status-processing {
            background: var(--info-light);
            color: #2196F3;
        }

        .status-completed {
            background: var(--success-light);
            color: #4CAF50;
        }

        .status-cancelled {
            background: var(--danger-light);
            color: #F44336;
        }

        .view-all {
            text-align: center;
            margin-top: 2rem;
        }

        .btn-view {
            display: inline-flex;
            align-items: center;
            padding: 0.8rem 2rem;
            background: var(--main-color);
            color: var(--black);
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 2rem;
        }

        .btn-view:hover {
            background: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(210, 173, 127, 0.3);
        }

        .btn-view i {
            margin-left: 0.5rem;
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .dashboard {
                padding: 2rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .last-login {
                margin-top: 1rem;
            }
            
            .order-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-title {
                font-size: 2.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <a href="dashboard.php" class="logo">
            <img src="../images/logo.png" alt="KopiUy Logo">
        </a>
        
        <nav class="navbar">
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="products.php"><i class="fas fa-coffee"></i> Produk</a>
            <a href="orders.php"><i class="fas fa-shopping-cart"></i> Pesanan</a>
            <a href="customers.php"><i class="fas fa-users"></i> Pelanggan</a>
            <a href="reports.php"><i class="fas fa-chart-bar"></i> Laporan</a>
        </nav>
        
        <div class="icons">
            <div class="fas fa-bell" id="notification-btn"></div>
            <div class="fas fa-user"></div>
            <a href="logout.php" class="fas fa-sign-out-alt"></a>
        </div>
    </header>

    <!-- Dashboard Content -->
    <main class="dashboard">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Dashboard <span>Admin</span></h1>
            <div class="last-login">
                <i class="fas fa-clock"></i> Login terakhir: <?= date('d M Y H:i', strtotime($_SESSION['last_login'])) ?>
            </div>
        </div>
        
        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                <h3 class="stat-title">Total Pesanan</h3>
                <div class="stat-value"><?= number_format($stats['total_orders']) ?></div>
                <a href="orders.php" class="stat-link">
                    Lihat detail <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-coffee"></i></div> <!-- Ganti icon sesuai kebutuhan -->
                <h3 class="stat-title">Total Produk</h3>
                <div class="stat-value"><?= number_format($stats['total_products']) ?></div>
                <a href="products.php" class="stat-link">
                    Kelola produk <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <h3 class="stat-title">Total Pendapatan</h3>
                <div class="stat-value">Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></div>
                <a href="reports.php" class="stat-link">
                    Lihat laporan <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <h3 class="stat-title">Total Pelanggan</h3>
                <div class="stat-value"><?= number_format($stats['total_customers']) ?></div>
                <a href="customers.php" class="stat-link">
                    Lihat pelanggan <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        
        <!-- Revenue Chart -->
        <div class="dashboard-section">
            <h2 class="section-title"><i class="fas fa-chart-line"></i> Grafik Pendapatan 6 Bulan Terakhir</h2>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="dashboard-section">
            <h2 class="section-title"><i class="fas fa-clock"></i> Pesanan Terbaru</h2>
            <?php if (!empty($recent_orders)): ?>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_orders as $order): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                            <td>
                                <span class="order-status status-<?= htmlspecialchars($order['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: var(--text-muted); text-align: center; padding: 2rem;">
                    Tidak ada pesanan terbaru
                </p>
            <?php endif; ?>
            <div class="view-all">
                <a href="orders.php" class="btn-view">
                    Lihat Semua Pesanan <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <section class="footer">
        <div class="credit">
            <p>KopiUy Admin Panel &copy; <?= date('Y') ?> | Dibuat dengan <i class="fas fa-heart"></i> oleh Tim KopiUy</p>
        </div>
    </section>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src="../js/admin.js"></script>
    
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = <?= json_encode($monthly_revenue) ?>;
        
        const months = revenueData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        });
        
        const revenues = revenueData.map(item => item.revenue);
        
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Pendapatan',
                    data: revenues,
                    backgroundColor: 'rgba(211, 173, 127, 0.2)',
                    borderColor: 'rgba(211, 173, 127, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Notification system
        document.getElementById('notification-btn').addEventListener('click', function() {
            // Implement notification dropdown here
            alert('Sistem notifikasi akan diimplementasikan di sini');
        });
    </script>
</body>
</html>