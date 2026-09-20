<?php
require_once 'includes/functions.php';
$page_title = 'Search Books - EBook Store';

$search_query = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$books = [];

if (!empty($search_query)) {
    // Search books by title, author, or description
    $stmt = $pdo->prepare("
        SELECT * FROM books 
        WHERE title LIKE ? OR author LIKE ? OR description LIKE ? 
        ORDER BY created_at DESC
    ");
    $search_term = '%' . $search_query . '%';
    $stmt->execute([$search_term, $search_term, $search_term]);
    $books = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-search me-2"></i>Search Books</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" 
                                   value="<?php echo htmlspecialchars($search_query); ?>" 
                                   placeholder="Search by title, author, or description...">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                        </div>
                    </form>
                    
                    <?php if (!empty($search_query)): ?>
                        <div class="search-results">
                            <h5>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h5>
                            <p class="text-muted"><?php echo count($books); ?> book(s) found</p>
                            
                            <?php if (empty($books)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No books found matching your search criteria. Try different keywords.
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($books as $book): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card h-100">
                                                <div class="row g-0">
                                                    <div class="col-4">
                                                        <?php if ($book['cover_image']): ?>
                                                            <img src="assets/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                                                 class="img-fluid rounded-start h-100" style="object-fit: cover;"
                                                                 alt="<?php echo htmlspecialchars($book['title']); ?>">
                                                        <?php else: ?>
                                                            <div class="bg-light h-100 d-flex align-items-center justify-content-center rounded-start">
                                                                <i class="fas fa-book fa-2x text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-8">
                                                        <div class="card-body">
                                                            <h6 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h6>
                                                            <p class="card-text small text-muted">
                                                                by <?php echo htmlspecialchars($book['author']); ?>
                                                            </p>
                                                            <p class="card-text small">
                                                                <?php echo htmlspecialchars(substr($book['description'], 0, 80)) . '...'; ?>
                                                            </p>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="text-success fw-bold">
                                                                    $<?php echo number_format($book['price'], 2); ?>
                                                                </span>
                                                                <a href="book_preview.php?id=<?php echo $book['id']; ?>" 
                                                                   class="btn btn-primary btn-sm">
                                                                    <i class="fas fa-eye me-1"></i>View
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5>Search our book collection</h5>
                            <p class="text-muted">Enter keywords to find books by title, author, or description</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>