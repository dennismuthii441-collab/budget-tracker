<?php
require_once 'includes/functions.php';
$page_title = 'Home - EBook Store';

// Fetch all books
$stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC");
$books = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Hero Section with Inspiring Design -->
    <div class="hero-section bg-gradient text-white p-5 rounded mb-5">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 fw-bold">📚 Welcome to Your Literary Journey</h1>
                <p class="lead mb-4">Discover extraordinary stories, expand your mind, and embark on adventures that will transform your perspective. Every book is a doorway to infinite possibilities.</p>
                <div class="quote-section bg-light text-dark rounded p-3 mb-4">
                    <em>"A reader lives a thousand lives before he dies. The man who never reads lives only one."</em>
                    <small class="d-block mt-2">- George R.R. Martin</small>
                </div>
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-warning btn-lg me-3">
                        <i class="fas fa-rocket me-2"></i>Start Your Journey
                    </a>
                    <a href="login.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Welcome Back
                    </a>
                <?php else: ?>
                    <a href="my_books.php" class="btn btn-warning btn-lg me-3">
                        <i class="fas fa-book-open me-2"></i>Continue Reading
                    </a>
                    <a href="search.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-search me-2"></i>Discover More
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-center">
                <div class="hero-book-animation">
                    <i class="fas fa-book-reader fa-5x opacity-75 mb-3"></i>
                    <div class="sparkle"></div>
                    <div class="sparkle"></div>
                    <div class="sparkle"></div>
                    <div class="sparkle"></div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($books)): ?>
        <div class="text-center py-5">
            <div class="empty-state">
                <i class="fas fa-book fa-4x text-muted mb-4"></i>
                <h3 class="text-muted">Our Library is Growing</h3>
                <p class="text-muted lead">Amazing books are coming soon! Check back for new releases that will inspire and captivate you.</p>
                <div class="mt-4">
                    <a href="contact.php" class="btn btn-primary">
                        <i class="fas fa-envelope me-2"></i>Get Notified
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Featured Books Section -->
        <div class="section-header text-center mb-5">
            <h2 class="display-5 mb-3">✨ Featured Literary Treasures</h2>
            <p class="lead text-muted">Handpicked stories that will ignite your imagination and touch your soul</p>
            <div class="book-shelf"></div>
        </div>
        
        <div class="row">
            <?php foreach ($books as $index => $book): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 book-card book-spine-effect" style="animation-delay: <?php echo $index * 0.1; ?>s">
                        <div class="book-cover-container">
                            <?php if ($book['cover_image']): ?>
                                <img src="assets/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     class="card-img-top book-cover" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="card-img-top book-cover-placeholder d-flex align-items-center justify-content-center">
                                    <div class="text-center">
                                        <i class="fas fa-book fa-3x text-muted mb-2"></i>
                                        <div class="small text-muted">Cover Coming Soon</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="book-overlay">
                                <a href="book_preview.php?id=<?php echo $book['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-eye me-2"></i>Preview Magic
                                </a>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <p class="card-text text-muted mb-2">
                                <i class="fas fa-feather-alt me-1"></i>by <?php echo htmlspecialchars($book['author']); ?>
                            </p>
                            <p class="card-text flex-grow-1">
                                <?php echo htmlspecialchars(substr($book['description'], 0, 120)) . '...'; ?>
                            </p>
                            
                            <!-- Book Stats -->
                            <div class="book-stats mb-3">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="stat-item">
                                            <div class="stat-value"><?php echo $book['total_chapters']; ?></div>
                                            <div class="stat-label">Chapters</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-item">
                                            <div class="stat-value"><?php echo $book['preview_chapters']; ?></div>
                                            <div class="stat-label">Free Preview</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="h5 text-success mb-0">
                                        <i class="fas fa-dollar-sign"></i><?php echo number_format($book['price'], 2); ?>
                                    </span>
                                    <div class="book-rating">
                                        <?php
                                        // Get average rating for this book
                                        $stmt_rating = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM book_reviews WHERE book_id = ?");
                                        $stmt_rating->execute([$book['id']]);
                                        $avg_rating = $stmt_rating->fetch()['avg_rating'] ?? 0;
                                        ?>
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-muted">(<?php echo round($avg_rating, 1); ?>)</small>
                                    </div>
                                </div>
                                <a href="book_preview.php?id=<?php echo $book['id']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-book-open me-2"></i>Start Reading Journey
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Features Section -->
    <div class="section-header text-center mt-5 mb-5">
        <h2 class="display-6 mb-3">🌟 Why Readers Choose Us</h2>
        <p class="lead text-muted">Experience the future of digital reading with features designed for book lovers</p>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <i class="fas fa-eye fa-3x text-primary mb-3"></i>
                <h4>Free Previews</h4>
                <p>Dive into sample chapters before purchasing. Experience the author's voice and story style to ensure every book resonates with you.</p>
                <div class="feature-highlight">
                    <small class="text-primary">✨ Try before you buy</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <i class="fas fa-mobile-alt fa-3x text-success mb-3"></i>
                <h4>Instant Access</h4>
                <p>Pay securely with M-Pesa or PayPal and start reading immediately. Your books are available across all your devices, anytime, anywhere.</p>
                <div class="feature-highlight">
                    <small class="text-success">⚡ Read in seconds</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="feature-card">
                <i class="fas fa-heart fa-3x text-danger mb-3"></i>
                <h4>Curated Collection</h4>
                <p>Every book in our library is carefully selected for quality, impact, and reader satisfaction. Discover your next favorite author.</p>
                <div class="feature-highlight">
                    <small class="text-danger">💎 Quality guaranteed</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reading Stats Section -->
    <?php if (isLoggedIn()): ?>
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-gradient text-white">
                <div class="card-body text-center p-4">
                    <h4 class="mb-3">📊 Your Reading Journey</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value" id="booksOwned">
                                    <?php
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND status = 'completed'");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    echo $stmt->fetchColumn();
                                    ?>
                                </div>
                                <div class="stat-label">Books Owned</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value" id="readingTime">0h 0m</div>
                                <div class="stat-label">Reading Time</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value" id="pagesRead">0</div>
                                <div class="stat-label">Pages Read</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-value">📚</div>
                                <div class="stat-label">Keep Reading!</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Call to Action -->
    <?php if (!isLoggedIn()): ?>
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-gradient text-white text-center">
                <div class="card-body p-5">
                    <h3 class="mb-3">🚀 Ready to Begin Your Adventure?</h3>
                    <p class="lead mb-4">Join thousands of readers who have discovered their next favorite book with us. Your literary journey awaits!</p>
                    <a href="register.php" class="btn btn-warning btn-lg me-3">
                        <i class="fas fa-user-plus me-2"></i>Join Our Community
                    </a>
                    <a href="search.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-search me-2"></i>Browse Books
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Update reading stats on page load
document.addEventListener('DOMContentLoaded', function() {
    const totalTime = parseInt(localStorage.getItem('totalReadingTime') || '0');
    const pagesRead = parseInt(localStorage.getItem('pagesRead') || '0');
    
    const readingTimeElement = document.getElementById('readingTime');
    const pagesReadElement = document.getElementById('pagesRead');
    
    if (readingTimeElement) {
        readingTimeElement.textContent = EBookStore.formatReadingTime(totalTime);
    }
    
    if (pagesReadElement) {
        pagesReadElement.textContent = pagesRead;
    }
});
</script>

<?php include 'includes/footer.php'; ?>