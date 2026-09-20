<?php
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$book_id = (int)$_GET['id'];

// Get book details
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: index.php');
    exit();
}

// Get preview chapters
$stmt = $pdo->prepare("SELECT * FROM book_chapters WHERE book_id = ? AND is_preview = 1 ORDER BY chapter_number");
$stmt->execute([$book_id]);
$preview_chapters = $stmt->fetchAll();

// Get book reviews and rating
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM book_reviews WHERE book_id = ?");
$stmt->execute([$book_id]);
$rating_data = $stmt->fetch();
$avg_rating = $rating_data['avg_rating'] ?? 0;
$total_reviews = $rating_data['total_reviews'] ?? 0;

// Check if user has access to full book
$has_access = false;
if (isLoggedIn()) {
    $has_access = hasBookAccess($_SESSION['user_id'], $book_id);
}

$page_title = $book['title'] . ' - Preview';
include 'includes/header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($book['title']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-4">
            <div class="book-detail-cover">
                <?php if ($book['cover_image']): ?>
                    <img src="assets/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                         class="img-fluid rounded shadow" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>">
                <?php else: ?>
                    <div class="book-cover-placeholder-large d-flex align-items-center justify-content-center rounded shadow">
                        <i class="fas fa-book fa-5x text-muted"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-8">
            <h1 class="display-5"><?php echo htmlspecialchars($book['title']); ?></h1>
            <p class="lead text-muted">
                <i class="fas fa-user me-2"></i>by <?php echo htmlspecialchars($book['author']); ?>
            </p>
            <p class="fs-5"><?php echo htmlspecialchars($book['description']); ?></p>
            
            <!-- Rating Display -->
            <?php if ($total_reviews > 0): ?>
                <div class="rating-display mb-3">
                    <div class="d-flex align-items-center">
                        <div class="stars me-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="me-2"><?php echo number_format($avg_rating, 1); ?></span>
                        <a href="reviews.php?book_id=<?php echo $book_id; ?>" class="text-decoration-none">
                            (<?php echo $total_reviews; ?> review<?php echo $total_reviews !== 1 ? 's' : ''; ?>)
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="book-stats mb-4">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="stat-item">
                            <i class="fas fa-file-alt text-primary"></i>
                            <span><?php echo $book['total_chapters']; ?> Chapters</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="stat-item">
                            <i class="fas fa-eye text-info"></i>
                            <span><?php echo $book['preview_chapters']; ?> Preview Chapters</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="price-section mb-4">
                <h3 class="text-success">
                    <i class="fas fa-dollar-sign"></i><?php echo number_format($book['price'], 2); ?>
                </h3>
            </div>
            
            <div class="action-buttons">
                <?php if (!$has_access): ?>
                    <?php if (isLoggedIn()): ?>
                        <a href="payment.php?book_id=<?php echo $book['id']; ?>" class="btn btn-success btn-lg me-3">
                            <i class="fas fa-shopping-cart me-2"></i>Purchase Full Access
                        </a>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Please <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="alert-link">login</a> 
                            to purchase this book.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="reader.php?book_id=<?php echo $book['id']; ?>" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-book-open me-2"></i>Read Full Book
                    </a>
                    <span class="badge bg-success fs-6">
                        <i class="fas fa-check me-1"></i>Purchased
                    </span>
                <?php endif; ?>
                
                <a href="reviews.php?book_id=<?php echo $book_id; ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-star me-2"></i>Reviews (<?php echo $total_reviews; ?>)
                </a>
            </div>
        </div>
    </div>
    
    <hr class="my-5">
    
    <div class="preview-section">
        <h2><i class="fas fa-eye me-2"></i>Preview Chapters</h2>
        <p class="text-muted mb-4">Get a taste of this amazing book with these free preview chapters!</p>
        
        <?php if (empty($preview_chapters)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No preview chapters are available for this book yet.
            </div>
        <?php else: ?>
            <?php foreach ($preview_chapters as $index => $chapter): ?>
                <div class="chapter-card mb-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <button class="btn btn-link text-decoration-none" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#chapter<?php echo $chapter['id']; ?>">
                                    <i class="fas fa-book-open me-2"></i>
                                    Chapter <?php echo $chapter['chapter_number']; ?>: 
                                    <?php echo htmlspecialchars($chapter['chapter_title']); ?>
                                </button>
                            </h5>
                        </div>
                        <div id="chapter<?php echo $chapter['id']; ?>" class="collapse <?php echo $index === 0 ? 'show' : ''; ?>">
                            <div class="card-body">
                                <div class="chapter-content">
                                    <?php echo nl2br(htmlspecialchars($chapter['content'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (!$has_access): ?>
                <div class="purchase-cta text-center py-5">
                    <div class="card bg-gradient text-white">
                        <div class="card-body">
                            <h4><i class="fas fa-star me-2"></i>Enjoying the preview?</h4>
                            <p class="lead">Purchase full access to read all <?php echo $book['total_chapters']; ?> chapters and support the author!</p>
                            <?php if (isLoggedIn()): ?>
                                <a href="payment.php?book_id=<?php echo $book['id']; ?>" class="btn btn-light btn-lg">
                                    <i class="fas fa-shopping-cart me-2"></i>Purchase Now - $<?php echo number_format($book['price'], 2); ?>
                                </a>
                            <?php else: ?>
                                <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-light btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login to Purchase
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>