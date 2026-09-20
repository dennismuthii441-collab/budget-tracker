<?php
require_once '../includes/functions.php';
requireAdmin();

// Get sales data
$stmt = $pdo->query("
    SELECT p.*, u.username, u.full_name, b.title, b.author 
    FROM purchases p 
    JOIN users u ON p.user_id = u.id 
    JOIN books b ON p.book_id = b.id 
    ORDER BY p.created_at DESC
");
$sales = $stmt->fetchAll();

// Get sales statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_sales FROM purchases WHERE status = 'completed'");
$total_sales = $stmt->fetch()['total_sales'];

$stmt = $pdo->query("SELECT SUM(amount) as total_revenue FROM purchases WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total_revenue'] ?? 0;

$stmt = $pdo->query("SELECT AVG(amount) as avg_sale FROM purchases WHERE status = 'completed'");
$avg_sale = $stmt->fetch()['avg_sale'] ?? 0;

// Monthly sales data
$stmt = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
           COUNT(*) as sales_count, 
           SUM(amount) as revenue 
    FROM purchases 
    WHERE status = 'completed' 
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
    ORDER BY month DESC 
    LIMIT 12
");
$monthly_sales = $stmt->fetchAll();

$page_title = 'Sales Report - Admin';
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';
$home_path = '../index.php';
$admin_path = './';
$logout_path = '../logout.php';
include '../includes/header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Sales Report</li>
        </ol>
    </nav>
    
    <h1><i class="fas fa-chart-line me-2"></i>Sales Report</h1>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>Total Sales</h5>
                            <h2><?php echo $total_sales; ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>Total Revenue</h5>
                            <h2>$<?php echo number_format($total_revenue, 2); ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>Average Sale</h5>
                            <h2>$<?php echo number_format($avg_sale, 2); ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-bar fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5>This Month</h5>
                            <h2>
                                <?php 
                                $this_month = !empty($monthly_sales) ? $monthly_sales[0]['sales_count'] : 0;
                                echo $this_month;
                                ?>
                            </h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Monthly Sales Chart -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line me-2"></i>Monthly Sales</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($monthly_sales)): ?>
                        <canvas id="salesChart" width="400" height="200"></canvas>
                    <?php else: ?>
                        <p class="text-muted">No sales data available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Payment Methods -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-credit-card me-2"></i>Payment Methods</h5>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $pdo->query("
                        SELECT payment_method, COUNT(*) as count, SUM(amount) as total 
                        FROM purchases 
                        WHERE status = 'completed' 
                        GROUP BY payment_method
                    ");
                    $payment_methods = $stmt->fetchAll();
                    ?>
                    
                    <?php if (!empty($payment_methods)): ?>
                        <?php foreach ($payment_methods as $method): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0"><?php echo ucfirst($method['payment_method']); ?></h6>
                                    <small class="text-muted"><?php echo $method['count']; ?> transactions</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">$<?php echo number_format($method['total'], 2); ?></div>
                                    <small class="text-muted">
                                        <?php echo round(($method['total'] / $total_revenue) * 100, 1); ?>%
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No payment data available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sales Table -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list me-2"></i>All Sales</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Book</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo $sale['id']; ?></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($sale['full_name']); ?></strong>
                                        <br><small class="text-muted">@<?php echo htmlspecialchars($sale['username']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($sale['title']); ?></strong>
                                        <br><small class="text-muted">by <?php echo htmlspecialchars($sale['author']); ?></small>
                                    </div>
                                </td>
                                <td class="text-success fw-bold">$<?php echo number_format($sale['amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $sale['payment_method'] === 'mpesa' ? 'success' : 'primary'; ?>">
                                        <?php echo ucfirst($sale['payment_method']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $sale['status'] === 'completed' ? 'success' : 
                                            ($sale['status'] === 'pending' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($sale['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y H:i', strtotime($sale['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($monthly_sales)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            <?php foreach (array_reverse($monthly_sales) as $month): ?>
                '<?php echo date('M Y', strtotime($month['month'] . '-01')); ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Sales',
            data: [
                <?php foreach (array_reverse($monthly_sales) as $month): ?>
                    <?php echo $month['sales_count']; ?>,
                <?php endforeach; ?>
            ],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Revenue ($)',
            data: [
                <?php foreach (array_reverse($monthly_sales) as $month): ?>
                    <?php echo $month['revenue']; ?>,
                <?php endforeach; ?>
            ],
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                position: 'left'
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>