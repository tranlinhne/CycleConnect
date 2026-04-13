<?php
require_once '../inc/auth.php';
require_once '../inc/header.php';

$year = date('Y');
// Lấy doanh thu 12 tháng gần nhất (có thể có tháng không có đơn hàng)
$monthly = $pdo->prepare("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
           SUM(total_price) as revenue,
           COUNT(*) as orders_count
    FROM orders 
    WHERE payment_status = 'paid' 
    GROUP BY month 
    ORDER BY month DESC 
    LIMIT 12
");
$monthly->execute();
$data = $monthly->fetchAll();
// Đảo ngược để hiển thị từ tháng cũ đến mới cho biểu đồ
$chartData = array_reverse($data);

// Tổng doanh thu
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();

// Tổng số đơn hàng đã thanh toán
$totalPaidOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'")->fetchColumn();

// Giá trị đơn hàng trung bình
$avgOrderValue = $totalPaidOrders > 0 ? $totalRevenue / $totalPaidOrders : 0;

// Doanh thu tháng hiện tại
$currentMonthRevenue = $pdo->prepare("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE payment_status = 'paid' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$currentMonthRevenue->execute();
$currentRevenue = $currentMonthRevenue->fetchColumn();
?>

<style>
    /* ---------- MÀU SẮC CHỦ ĐẠO ---------- */
    :root {
        --primary-orange: #F57C00;
        --bg-gray: #F5F5F5;
        --text-dark: #263238;
        --border-light: #e0e0e0;
        --card-shadow: 0 8px 16px rgba(0,0,0,0.05);
        --hover-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }

    body {
        background-color: var(--bg-gray);
        color: var(--text-dark);
    }

    /* Tiêu đề trang */
    .page-header {
        background: white;
        border-radius: 1rem;
        padding: 1.2rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
    }
    .page-header h2 {
        font-weight: 700;
        font-size: 1.6rem;
        margin: 0;
        color: var(--text-dark);
        border-left: 5px solid var(--primary-orange);
        padding-left: 1rem;
    }

    /* Thẻ thống kê */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.2rem;
        margin-bottom: 2rem;
    }
    .stat-card {
        background: white;
        border-radius: 1.2rem;
        padding: 1.2rem 1.5rem;
        box-shadow: var(--card-shadow);
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }
    .stat-card .stat-icon {
        position: absolute;
        right: 1rem;
        top: 1rem;
        font-size: 2.5rem;
        opacity: 0.15;
        color: var(--primary-orange);
    }
    .stat-card .stat-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }
    .stat-card .stat-number {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 0;
    }
    .stat-card .stat-unit {
        font-size: 0.9rem;
        font-weight: 500;
        color: #6c757d;
    }
    .stat-card .stat-footer {
        margin-top: 0.75rem;
        font-size: 0.75rem;
        border-top: 1px solid #eee;
        padding-top: 0.5rem;
        color: #6c757d;
    }
    .stat-card .stat-footer i {
        color: var(--primary-orange);
        margin-right: 4px;
    }

    /* Biểu đồ container */
    .chart-container {
        background: white;
        border-radius: 1.2rem;
        padding: 1.2rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        transition: box-shadow 0.2s;
    }
    .chart-container:hover {
        box-shadow: var(--hover-shadow);
    }
    .chart-title {
        font-weight: 700;
        font-size: 1.2rem;
        margin-bottom: 1rem;
        color: var(--text-dark);
        border-left: 4px solid var(--primary-orange);
        padding-left: 0.8rem;
    }

    /* Bảng doanh thu */
    .revenue-table {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        width: 100%;
    }
    .revenue-table thead th {
        background-color: #fafafa;
        color: var(--text-dark);
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--primary-orange);
        padding: 1rem;
    }
    .revenue-table tbody tr {
        transition: background 0.2s;
    }
    .revenue-table tbody tr:hover {
        background-color: rgba(245, 124, 0, 0.05);
    }
    .revenue-table td {
        padding: 0.8rem 1rem;
        vertical-align: middle;
        color: var(--text-dark);
    }
    .trend-up {
        color: #2e7d32;
        font-weight: 600;
    }
    .trend-down {
        color: #c62828;
        font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stat-number {
            font-size: 1.3rem;
        }
        .stat-card {
            padding: 1rem;
        }
        .chart-container {
            padding: 0.8rem;
        }
    }
</style>

<div class="container-fluid px-4">
    <div class="page-header">
        <h2><i class="fas fa-chart-line me-2" style="color: #F57C00;"></i> Báo cáo doanh thu</h2>
    </div>

    <!-- Thẻ thống kê tổng quan -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-label">Tổng doanh thu</div>
            <div class="stat-number"><?= number_format($totalRevenue, 0, ',', '.') ?>đ</div>
            <div class="stat-footer"><i class="fas fa-chart-simple"></i> Toàn thời gian</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-label">Đơn hàng đã thanh toán</div>
            <div class="stat-number"><?= number_format($totalPaidOrders) ?></div>
            <div class="stat-footer"><i class="fas fa-check-circle"></i> Thành công</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-label">Giá trị đơn hàng TB</div>
            <div class="stat-number"><?= number_format($avgOrderValue, 0, ',', '.') ?>đ</div>
            <div class="stat-footer"><i class="fas fa-calculator"></i> Trung bình</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-label">Doanh thu tháng này</div>
            <div class="stat-number"><?= number_format($currentRevenue, 0, ',', '.') ?>đ</div>
            <div class="stat-footer"><i class="fas fa-clock"></i> <?= date('F Y') ?></div>
        </div>
    </div>

    <!-- Biểu đồ doanh thu (Chart.js) -->
    <div class="chart-container">
        <div class="chart-title"><i class="fas fa-chart-column me-1" style="color: #F57C00;"></i> Xu hướng doanh thu 12 tháng gần nhất</div>
        <canvas id="revenueChart" style="width:100%; max-height: 350px;"></canvas>
    </div>

    <!-- Bảng doanh thu chi tiết -->
    <div class="chart-container">
        <div class="chart-title"><i class="fas fa-table me-1" style="color: #F57C00;"></i> Chi tiết theo tháng</div>
        <div class="table-responsive">
            <table class="revenue-table table">
                <thead>
                    <tr>
                        <th>Tháng</th>
                        <th>Doanh thu (đ)</th>
                        <th>Số đơn hàng</th>
                        <th>Trung bình đơn hàng</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($data) > 0): ?>
                    <?php 
                    $prevRevenue = null;
                    foreach ($data as $row): 
                        $monthRevenue = $row['revenue'];
                        $trend = null;
                        if ($prevRevenue !== null) {
                            $trend = $monthRevenue - $prevRevenue;
                        }
                        $prevRevenue = $monthRevenue;
                    ?>
                    <tr>
                        <td><strong><?= date('m/Y', strtotime($row['month'] . '-01')) ?></strong></td>
                        <td><?= number_format($monthRevenue, 0, ',', '.') ?>đ</td>
                        <td><?= $row['orders_count'] ?></td>
                        <td>
                            <?= number_format($row['orders_count'] > 0 ? $monthRevenue / $row['orders_count'] : 0, 0, ',', '.') ?>đ
                            <?php if ($trend !== null): ?>
                                <?php if ($trend > 0): ?>
                                    <span class="trend-up"><i class="fas fa-arrow-up"></i> +<?= number_format($trend, 0, ',', '.') ?>đ</span>
                                <?php elseif ($trend < 0): ?>
                                    <span class="trend-down"><i class="fas fa-arrow-down"></i> <?= number_format($trend, 0, ',', '.') ?>đ</span>
                                <?php else: ?>
                                    <span><i class="fas fa-minus"></i> 0đ</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Chưa có dữ liệu doanh thu</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Thêm Chart.js để vẽ biểu đồ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = <?= json_encode($chartData) ?>;
    const months = chartData.map(item => {
        const date = item.month + '-01';
        return new Date(date).toLocaleDateString('vi-VN', { year: 'numeric', month: 'short' });
    });
    const revenues = chartData.map(item => parseFloat(item.revenue));

    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: revenues,
                backgroundColor: 'rgba(245, 124, 0, 0.7)',
                borderColor: '#F57C00',
                borderWidth: 1,
                borderRadius: 6,
                barPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.raw;
                            return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                        }
                    },
                    title: {
                        display: true,
                        text: 'VNĐ'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tháng'
                    }
                }
            }
        }
    });
});
</script>

<?php require_once '../inc/footer.php'; ?>