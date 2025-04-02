<?php
session_start();
require '../config/database.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Ambil data laporan
try {
    $pdo = Database::getInstance()->getConnection();
    
    // Total penjualan bulan ini
    $stmt = $pdo->query("SELECT SUM(total_price) as total FROM `kopiuy order` 
                         WHERE MONTH(created_at) = MONTH(CURRENT_DATE())");
    $monthly_sales = $stmt->fetchColumn() ?? 0;
    
    // Produk terlaris
    $stmt = $pdo->query("SELECT p.name, SUM(oi.quantity) as total_sold 
                        FROM `kopiuy order_items` oi
                        JOIN `kopiuy products` p ON oi.product_id = p.id
                        GROUP BY oi.product_id 
                        ORDER BY total_sold DESC 
                        LIMIT 5");
    $top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Data penjualan per bulan
    $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                         SUM(total_price) as total
                         FROM `kopiuy order`
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                         GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                         ORDER BY month");
    $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KopiUy - Laporan Penjualan</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .reports-container {
            margin-top: 9.5rem;
            padding: 2rem 5%;
        }
        
        .report-card {
            background: var(--black);
            border: var(--border);
            padding: 2rem;
            border-radius: .5rem;
            margin-bottom: 2rem;
        }
        
        .chart-container {
            height: 300px;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="dashboard.php" class="logo">
            <img src="../images/logo.png" alt="KopiUy Logo" height="60">
        </a>
        
        <nav class="navbar">
            <a href="dashboard.php">Dashboard</a>
            <a href="products.php">Produk</a>
            <a href="orders.php">Pesanan</a>
            <a href="customers.php">Pelanggan</a>
            <a href="reports.php" class="active">Laporan</a>
        </nav>
        
        <div class="icons">
            <a href="logout.php" class="fas fa-sign-out-alt"></a>
        </div>
    </header>

    <section class="reports-container">
        <h1 class="heading"><span>Laporan</span> Penjualan</h1>
        
        <div class="report-card">
            <h3 style="color: var(--main-color); margin-bottom: 1rem;">Penjualan Bulan Ini</h3>
            <p style="font-size: 2.5rem; font-weight: bold;">
                Rp <?= number_format($monthly_sales, 0, ',', '.') ?>
            </p>
        </div>
        
        <div class="report-card">
            <h3 style="color: var(--main-color); margin-bottom: 1rem;">Grafik Penjualan 6 Bulan Terakhir</h3>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        
        <div class="report-card">
            <h3 style="color: var(--main-color); margin-bottom: 1rem;">5 Produk Terlaris</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                <thead>
                    <tr style="background: var(--main-color); color: var(--black);">
                        <th style="padding: 1rem;">Produk</th>
                        <th style="padding: 1rem;">Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($top_products as $product): ?>
                    <tr style="border-bottom: var(--border);">
                        <td style="padding: 1rem;"><?= htmlspecialchars($product['name']) ?></td>
                        <td style="padding: 1rem;"><?= number_format($product['total_sold']) ?> pcs</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <script>
        // Grafik Penjualan
        const salesData = {
            labels: [<?php foreach($sales_data as $data): ?>"<?= date('M Y', strtotime($data['month'].'-01')) ?>", <?php endforeach; ?>],
            datasets: [{
                label: 'Total Penjualan',
                data: [<?php foreach($sales_data as $data): ?><?= $data['total'] ?>, <?php endforeach; ?>],
                backgroundColor: 'rgba(211, 173, 127, 0.2)',
                borderColor: 'rgba(211, 173, 127, 1)',
                borderWidth: 2,
                tension: 0.4
            }]
        };
        
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: salesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>

    <section class="footer">
        <div class="credit">
            <p>KopiUy Admin Panel &copy; <?= date('Y') ?></p>
        </div>
    </section>

    <script src="../js/admin.js"></script>
</body>
</html>