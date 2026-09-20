<?php
require_once 'includes/functions.php';

if (!isset($_GET['book_id'])) {
    header('Location: index.php');
    exit();
}

$book_id = (int)$_GET['book_id'];

// Get book details
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: index.php');
    exit();
}

// Handle review submission
$message = '';
$message_type = '';

if ($_POST && isLoggedIn()) {
    $rating = (int)$_POST['rating'];
    $review_text = sanitize($_POST['review_text']);
    
    // Check if user has purchased the book
    if (!hasBookAccess($_SESSION['user_id'], $book_id)) {
        $message = 'You can only review books you have purchased.';
        $message_type = 'danger';
    } elseif ($rating < 1 || $rating > 5) {
        $message = 'Please select a valid rating (1-5 stars).';
        $message_type = 'danger';
    } else {
        // Check if user has already reviewed this book
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM book_reviews WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$_SESSION['user_id'], $book_id]);
        
        if ($stmt->fetchColumn() > 0) {
            // Update existing review
            $stmt = $pdo->prepare("UPDATE book_reviews SET rating = ?, review_text = ? WHERE user_id = ? AND book_id = ?");
            if ($stmt->execute([$rating, $review_text, $_SESSION['user_id'], $book_id])) {
                $message = 'Your review has been updated successfully!';
                $message_type = 'success';
            }
        } else {
            // Insert new review
            $stmt = $pdo->prepare("INSERT INTO book_reviews (user_id, book_id, rating, review_text) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$_SESSION['user_id'], $book_id, $rating, $review_text])) {
                $message = 'Thank you for your review!';
                $message_type = 'success';
            }
        }
    }
}

// Get all reviews for this book
$stmt = $pdo->prepare("
    SELECT br.*, u.username, u.full_name 
    FROM book_reviews br 
    JOIN users u ON br.user_id = u.id 
    WHERE br.book_id = ? 
    ORDER BY br.created_at DESC
");
$stmt->execute([$book_id]);
$reviews = $stmt->fetchAll();

// Get average rating
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM book_reviews WHERE book_id = ?");
$stmt->execute([$book_id]);
$rating_data = $stmt->fetch();
$avg_rating = $rating_data['avg_rating'] ?? 0;
$total_reviews = $rating_data['total_reviews'] ?? 0;

// Get user's existing review if any
$user_review = null;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM book_reviews WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$_SESSION['user_id'], $book_id]);
    $user_review = $stmt->fetch();
}

$page_title = 'Reviews - ' . $book['title'];
include 'includes/header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="book_preview.php?id=<?php echo $book_id; ?>"><?php echo htmlspecialchars($book['title']); ?></a></li>
            <li class="breadcrumb-item active">Reviews</li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-star me-2"></i>Reviews for "<?php echo htmlspecialchars($book['title']); ?>"</h3>
                </div>
                <div class="card-body">
                    <!-- Rating Summary -->
                    <div class="rating-summary mb-4 p-3 bg-light rounded">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="text-center">
                                    <h2 class="display-4 mb-0"><?php echo number_format($avg_rating, 1); ?></h2>
                                    <div class="stars mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-muted"><?php echo $total_reviews; ?> review(s)</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?php
                                // Get rating distribution
                                $stmt = $pdo->prepare("
                                    SELECT rating, COUNT(*) as count 
                                    FROM book_reviews 
                                    WHERE book_id = ? 
                                    GROUP BY rating 
                                    ORDER BY rating DESC
                                ");
                                $stmt->execute([$book_id]);
                                $rating_distribution = $stmt->fetchAll();
                                $distribution = array_column($rating_distribution, 'count', 'rating');
                                ?>
                                
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="me-2"><?php echo $i; ?> star</span>
                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                            <div class="progress-bar bg-warning" style="width: <?php 
                                                echo $total_reviews > 0 ? (($distribution[$i] ?? 0) / $total_reviews) * 100 : 0; 
                                            ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?php echo $distribution[$i] ?? 0; ?></small>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Review Form -->
                    <?php if (isLoggedIn() && hasBookAccess($_SESSION['user_id'], $book_id)): ?>
                        <div class="review-form mb-4">
                            <h5><?php echo $user_review ? 'Update Your Review' : 'Write a Review'; ?></h5>
                            
                            <?php if ($message): ?>
                                <div class="alert alert-<?php echo $message_type; ?>">
                                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Rating *</label>
                                    <div class="rating-input">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" 
                                                   <?php echo ($user_review && $user_review['rating'] == $i) ? 'checked' : ''; ?> required>
                                            <label for="star<?php echo $i; ?>" class="star-label">
                                                <i class="fas fa-star"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="review_text" class="form-label">Your Review</label>
                                    <textarea class="form-control" id="review_text" name="review_text" rows="4" 
                                              placeholder="Share your thoughts about this book..."><?php echo $user_review ? htmlspecialchars($user_review['review_text']) : ''; ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-star me-2"></i><?php echo $user_review ? 'Update Review' : 'Submit Review'; ?>
                                </button>
                            </form>
                        </div>
                        <hr>
                    <?php elseif (isLoggedIn()): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            You need to purchase this book to write a review.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Please <a href="login.php" class="alert-link">login</a> to write a review.
                        </div>
                    <?php endif; ?>
                    
                    <!-- Reviews List -->
                    <div class="reviews-list">
                        <h5>All Reviews</h5>
                        
                        <?php if (empty($reviews)): ?>
                            <p class="text-muted">No reviews yet. Be the first to review this book!</p>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item mb-4 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong><?php echo htmlspecialchars($review['full_name']); ?></strong>
                                            <div class="stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                                        </small>
                                    </div>
                                    <?php if ($review['review_text']): ?>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <?php if ($book['cover_image']): ?>
                        <img src="assets/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                             class="img-fluid rounded mb-3" style="max-height: 200px;" alt="Book cover">
                    <?php else: ?>
                        <div class="book-cover-placeholder-large d-flex align-items-center justify-content-center rounded mb-3" style="height: 200px;">
                            <i class="fas fa-book fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <h6><?php echo htmlspecialchars($book['title']); ?></h6>
                    <p class="text-muted">by <?php echo htmlspecialchars($book['author']); ?></p>
                    <p class="text-success h5">$<?php echo number_format($book['price'], 2); ?></p>
                    <a href="book_preview.php?id=<?php echo $book['id']; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Book
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating-input input[type="radio"] {
    display: none;
}

.star-label {
    color: #ddd;
    font-size: 1.5rem;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-input input[type="radio"]:checked ~ .star-label,
.rating-input .star-label:hover,
.rating-input .star-label:hover ~ .star-label {
    color: #ffc107;
}
</style>

<?php include 'includes/footer.php'; ?>