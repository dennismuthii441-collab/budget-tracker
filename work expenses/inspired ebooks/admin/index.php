<?php
require_once '../includes/functions.php';
requireAdmin();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_books FROM books");
$total_books = $stmt->fetch()['total_books'];

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE is_admin = 0");
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as total_purchases FROM purchases WHERE status = 'completed'");
$total_purchases = $stmt->fetch()['total_purchases'];

$stmt = $pdo->query("SELECT SUM(amount) as total_revenue FROM purchases WHERE status = 'completed'");
$total_revenue = $stmt->fetch()['total_revenue'] ?? 0;

// Recent purchases
$stmt = $pdo->query("
    SELECT p.*, u.username, b.title 
    FROM purchases p 
    JOIN users u ON p.user_id = u.id 
    JOIN books b ON p.book_id = b.id 
    WHERE p.status = 'completed'
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$recent_purchases = $stmt->fetchAll();

// Recent reviews
$stmt = $pdo->query("
    SELECT br.*, u.username, b.title 
    FROM book_reviews br 
    JOIN users u ON br.user_id = u.id 
    JOIN books b ON br.book_id = b.id 
    ORDER BY br.created_at DESC 
    LIMIT 5
");
$recent_reviews = $stmt->fetchAll();

$page_title = 'Admin Dashboard';
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';
$home_path = '../index.php';
$logout_path = '../logout.php';
include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h1>
        <div>
            <a href="add_book.php" class="btn btn-primary me-2">
                <i class="fas fa-plus me-1"></i>Add Book
            </a>
            <a href="manage_books.php" class="btn btn-outline-primary">
                <i class="fas fa-cog me-1"></i>Manage Books
            </a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Books</h5>
                            <h2 class="mb-0"><?php echo $total_books; ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-book fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Users</h5>
                            <h2 class="mb-0"><?php echo $total_users; ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Sales</h5>
                            <h2 class="mb-0"><?php echo $total_purchases; ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Total Revenue</h5>
                            <h2 class="mb-0">$<?php echo number_format($total_revenue, 2); ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="add_book.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Book
                        </a>
                        <a href="manage_books.php" class="btn btn-secondary">
                            <i class="fas fa-edit me-2"></i>Manage Books
                        </a>
                        <a href="users.php" class="btn btn-info">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a>
                        <a href="sales.php" class="btn btn-success">
                            <i class="fas fa-chart-line me-2"></i>Sales Report
                        </a>
                        <a href="../index.php" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>View Website
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Purchases -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="fas fa-shopping-cart me-2"></i>Recent Purchases</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_purchases)): ?>
                        <p class="text-muted">No purchases yet.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_purchases as $purchase): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($purchase['title']); ?></h6>
                                            <p class="mb-1 small text-muted">
                                                by <?php echo htmlspecialchars($purchase['username']); ?>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y', strtotime($purchase['created_at'])); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-success">
                                            $<?php echo number_format($purchase['amount'], 2); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Reviews -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="fas fa-star me-2"></i>Recent Reviews</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_reviews)): ?>
                        <p class="text-muted">No reviews yet.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_reviews as $review): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($review['title']); ?></h6>
                                            <p class="mb-1 small text-muted">
                                                by <?php echo htmlspecialchars($review['username']); ?>
                                            </p>
                                            <div class="stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>" style="font-size: 0.8rem;"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('M j', strtotime($review['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>