<?php
require_once 'includes/functions.php';
requireLogin();

$page_title = 'My Books - EBook Store';

// Get user's purchased books
$stmt = $pdo->prepare("
    SELECT b.*, p.created_at as purchase_date 
    FROM books b 
    JOIN purchases p ON b.id = p.book_id 
    WHERE p.user_id = ? AND p.status = 'completed' 
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$purchased_books = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-book me-2"></i>My Books</h1>
        <a href="index.php" class="btn btn-outline-primary">
            <i class="fas fa-plus me-2"></i>Browse More Books
        </a>
    </div>
    
    <?php if (empty($purchased_books)): ?>
        <div class="text-center py-5">
            <i class="fas fa-book fa-3x text-muted mb-3"></i>
            <h3>No books purchased yet</h3>
            <p class="text-muted">Start building your digital library by purchasing some amazing books!</p>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-search me-2"></i>Browse Books
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($purchased_books as $book): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 book-card">
                        <div class="book-cover-container">
                            <?php if ($book['cover_image']): ?>
                                <img src="assets/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     class="card-img-top book-cover" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <?php else: ?>
                                <div class="card-img-top book-cover-placeholder d-flex align-items-center justify-content-center">
                                    <i class="fas fa-book fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="book-overlay">
                                <a href="reader.php?book_id=<?php echo $book['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-book-open me-1"></i>Read Now
                                </a>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <p class="card-text text-muted mb-2">
                                <i class="fas fa-user me-1"></i>by <?php echo htmlspecialchars($book['author']); ?>
                            </p>
                            <p class="card-text flex-grow-1">
                                <?php echo htmlspecialchars(substr($book['description'], 0, 120)) . '...'; ?>
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Purchased
                                    </span>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('M j, Y', strtotime($book['purchase_date'])); ?>
                                    </small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-file-alt me-1"></i><?php echo $book['total_chapters']; ?> chapters
                                    </small>
                                    <span class="text-success fw-bold">
                                        $<?php echo number_format($book['price'], 2); ?>
                                    </span>
                                </div>
                                <a href="reader.php?book_id=<?php echo $book['id']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-book-open me-1"></i>Continue Reading
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-5">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Reading Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo count($purchased_books); ?></div>
                                <div class="stat-label">Books Owned</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value">
                                    <?php 
                                    $total_chapters = array_sum(array_column($purchased_books, 'total_chapters'));
                                    echo $total_chapters;
                                    ?>
                                </div>
                                <div class="stat-label">Total Chapters</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value">
                                    $<?php 
                                    $total_spent = array_sum(array_column($purchased_books, 'price'));
                                    echo number_format($total_spent, 2);
                                    ?>
                                </div>
                                <div class="stat-label">Total Spent</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value">
                                    <?php echo date('M Y', strtotime($purchased_books[0]['purchase_date'])); ?>
                                </div>
                                <div class="stat-label">Member Since</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>