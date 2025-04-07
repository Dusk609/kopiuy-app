<?php
// 1. Mulai session dan atur error reporting
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Load konfigurasi database
require __DIR__ . '/../config/database.php';

// 3. Verifikasi login admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}


// 4. Inisialisasi variabel
$total_revenue = 0;
$monthly_revenue = [];
$revenue_by_product = [];
$revenue_by_customer = [];
$error_message = '';    

// 5. Koneksi database dan query
try {
    $pdo = Database::getInstance()->getConnection();
    
    // Total pendapatan keseluruhan
    $total_revenue = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM `order` WHERE status = 'completed'")->fetchColumn();
    
    // Pendapatan per bulan (6 bulan terakhir)
    $monthly_revenue = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, 
               SUM(total_price) AS revenue
        FROM `order`
        WHERE status = 'completed'
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Pendapatan per produk
    $revenue_by_product = $pdo->query("
        SELECT p.id, p.name, SUM(oi.quantity * oi.price) AS revenue
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN `order` o ON oi.order_id = o.id
        WHERE o.status = 'completed'
        GROUP BY p.id
        ORDER BY revenue DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Pendapatan per pelanggan
    $revenue_by_customer = $pdo->query("
        SELECT u.id, u.username, SUM(o.total_price) AS revenue
        FROM `order` o
        JOIN users u ON o.user_id = u.id
        WHERE o.status = 'completed'
        GROUP BY u.id
        ORDER BY revenue DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Revenue Report Error: " . $e->getMessage());
    $error_message = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan | KopiUy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        .revenue-container {
            padding: 2rem 5%;
            margin-top: 9.5rem;
            min-height: calc(100vh - 9.5rem);
        }
        
        .revenue-header {
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: var(--border);
        }
        
        .revenue-title {
            font-size: 2.8rem;
            color: var(--white);
        }
        
        .revenue-title span {
            color: var(--main-color);
        }
        
        .total-revenue-card {
            background: var(--card-bg);
            border: var(--border);
            border-radius: 0.8rem;
            padding: 2rem;
            margin-bottom: 3rem;
            text-align: center;
        }
        
        .total-revenue-value {
            font-size: 3.5rem;
            color: var(--main-color);
            font-weight: bold;
            margin: 1rem 0;
        }
        
        .revenue-section {
            background: var(--card-bg);
            border: var(--border);
            border-radius: 0.8rem;
            padding: 2rem;
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
        
        .revenue-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }
        
        .revenue-table th {
            background: var(--main-color);
            color: var(--black);
            padding: 1.2rem 1.5rem;
            font-size: 1.6rem;
            text-align: left;
        }
        
        .revenue-table td {
            padding: 1.2rem 1.5rem;
            font-size: 1.4rem;
            color: var(--text-light);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--white);
        }
        
        .revenue-table tr:last-child td {
            border-bottom: none;
        }
        
        .revenue-table tr:hover td {
            background: rgba(255, 255, 255, 0.03);
        }
        
        .revenue-amount {
            font-weight: bold;
            color: var(--main-color);
        }
        
        .time-filter {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .time-filter-btn {
            padding: 0.8rem 1.5rem;
            background: var(--card-bg);
            border: var(--border);
            border-radius: 0.5rem;
            color: var(--white);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .time-filter-btn.active {
            background: var(--main-color);
            color: var(--black);
        }
        
        .time-filter-btn:hover {
            background: var(--main-color);
            color: var(--black);
        }
        
        @media (max-width: 768px) {
            .revenue-container {
                padding: 2rem;
            }
            
            .time-filter {
                flex-wrap: wrap;
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
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="products.php"><i class="fas fa-coffee"></i> Produk</a>
            <a href="orders.php"><i class="fas fa-shopping-cart"></i> Pesanan</a>
            <a href="customers.php"><i class="fas fa-users"></i> Pelanggan</a>
            <a href="revenue_report.php" class="active"><i class="fas fa-money-bill-wave"></i> Pendapatan</a>
        </nav>
        
        <div class="icons">
            <div class="fas fa-user"></div>
            <a href="logout.php" class="fas fa-sign-out-alt"></a>
        </div>
    </header>

    <!-- Revenue Content -->
    <main class="revenue-container">
        <div class="revenue-header">
            <h1 class="revenue-title">Laporan <span>Pendapatan</span></h1>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Total Revenue Card -->
        <div class="total-revenue-card">
            <h3><i class="fas fa-money-bill-wave"></i> Total Pendapatan</h3>
            <div class="total-revenue-value">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
            <p>Dari semua pesanan yang telah selesai</p>
        </div>
        
        <!-- Monthly Revenue Chart -->
        <div class="revenue-section">
            <h2 class="section-title"><i class="fas fa-chart-line"></i> Pendapatan Bulanan</h2>
            <div class="time-filter">
                <button class="time-filter-btn active" data-range="6">6 Bulan Terakhir</button>
                <button class="time-filter-btn" data-range="12">1 Tahun Terakhir</button>
                <button class="time-filter-btn" data-range="all">Semua Waktu</button>
            </div>
            <div class="chart-container">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>
        
        <!-- Revenue by Product -->
        <div class="revenue-section">
            <h2 class="section-title"><i class="fas fa-coffee"></i> Pendapatan per Produk</h2>
            <table class="revenue-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($revenue_by_product)): ?>
                        <?php foreach($revenue_by_product as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td class="revenue-amount">Rp <?= number_format($product['revenue'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center;">Tidak ada data pendapatan produk</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Revenue by Customer -->
        <div class="revenue-section">
            <h2 class="section-title"><i class="fas fa-users"></i> Pendapatan per Pelanggan</h2>
            <table class="revenue-table">
                <thead>
                    <tr>
                        <th>Pelanggan</th>
                        <th>Total Belanja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($revenue_by_customer)): ?>
                        <?php foreach($revenue_by_customer as $customer): ?>
                        <tr>
                            <td><?= htmlspecialchars($customer['username']) ?></td>
                            <td class="revenue-amount">Rp <?= number_format($customer['revenue'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center;">Tidak ada data pelanggan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Footer -->
    <section class="footer">
        <div class="credit">
            <p>KopiUy Admin Panel &copy; <?= date('Y') ?></p>
        </div>
    </section>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Monthly Revenue Chart
        const monthlyRevenueData = <?= json_encode($monthly_revenue) ?>;
        
        const months = monthlyRevenueData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        });
        
        const revenues = monthlyRevenueData.map(item => item.revenue);
        
        const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
        const monthlyRevenueChart = new Chart(monthlyRevenueCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Pendapatan',
                    data: revenues,
                    backgroundColor: 'rgba(211, 173, 127, 0.7)',
                    borderColor: 'rgba(211, 173, 127, 1)',
                    borderWidth: 1
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

        // Time filter buttons
        document.querySelectorAll('.time-filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.time-filter-btn').forEach(b => {
                    b.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Get the time range
                const range = this.dataset.range;
                
                // Here you would typically make an AJAX request to get filtered data
                // For now, we'll just show an alert
                alert('Filter akan menampilkan data untuk ' + 
                    (range === '6' ? '6 bulan terakhir' : 
                     range === '12' ? '1 tahun terakhir' : 'semua waktu'));
                
                // In a real implementation, you would:
                // 1. Make an AJAX request to get filtered data
                // 2. Update the chart with new data
                // 3. Possibly update the tables as well
            });
        });
    </script>
</body>
</html>