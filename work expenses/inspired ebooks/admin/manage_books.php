<?php
require_once '../includes/functions.php';
requireAdmin();

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $book_id = (int)$_GET['id'];
    
    // Get book details for file cleanup
    $stmt = $pdo->prepare("SELECT cover_image FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    
    // Delete book and related data (cascading deletes will handle chapters and purchases)
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    if ($stmt->execute([$book_id])) {
        // Delete cover image file if exists
        if ($book && $book['cover_image'] && file_exists('../assets/uploads/covers/' . $book['cover_image'])) {
            unlink('../assets/uploads/covers/' . $book['cover_image']);
        }
        $message = 'Book deleted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Failed to delete book.';
        $message_type = 'danger';
    }
}

// Get all books
$stmt = $pdo->query("
    SELECT b.*, 
           COUNT(DISTINCT bc.id) as chapter_count,
           COUNT(DISTINCT p.id) as purchase_count,
           COALESCE(SUM(p.amount), 0) as total_revenue
    FROM books b 
    LEFT JOIN book_chapters bc ON b.id = bc.book_id 
    LEFT JOIN purchases p ON b.id = p.book_id AND p.status = 'completed'
    GROUP BY b.id 
    ORDER BY b.created_at DESC
");
$books = $stmt->fetchAll();

$page_title = 'Manage Books - Admin';
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
            <li class="breadcrumb-item active">Manage Books</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-books me-2"></i>Manage Books</h1>
        <a href="add_book.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Book
        </a>
    </div>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (empty($books)): ?>
        <div class="text-center py-5">
            <i class="fas fa-book fa-3x text-muted mb-3"></i>
            <h3>No books found</h3>
            <p class="text-muted">Start by adding your first book to the store.</p>
            <a href="add_book.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Your First Book
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($books as $book): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100">
                        <div class="book-cover-container-admin">
                            <?php if ($book['cover_image']): ?>
                                <img src="../assets/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     class="card-img-top book-cover-admin" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <?php else: ?>
                                <div class="card-img-top book-cover-placeholder-admin d-flex align-items-center justify-content-center">
                                    <i class="fas fa-book fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <p class="card-text text-muted mb-2">
                                <i class="fas fa-user me-1"></i>by <?php echo htmlspecialchars($book['author']); ?>
                            </p>
                            <p class="card-text small">
                                <?php echo htmlspecialchars(substr($book['description'], 0, 100)) . '...'; ?>
                            </p>
                            
                            <!-- Book Stats -->
                            <div class="book-stats mb-3">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <div class="stat-value"><?php echo $book['chapter_count']; ?></div>
                                            <div class="stat-label">Chapters</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <div class="stat-value"><?php echo $book['purchase_count']; ?></div>
                                            <div class="stat-label">Sales</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <div class="stat-value">$<?php echo number_format($book['total_revenue'], 0); ?></div>
                                            <div class="stat-label">Revenue</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="h6 text-success mb-0">
                                        $<?php echo number_format($book['price'], 2); ?>
                                    </span>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y', strtotime($book['created_at'])); ?>
                                    </small>
                                </div>
                                
                                <div class="btn-group w-100">
                                    <a href="../book_preview.php?id=<?php echo $book['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <a href="edit_book.php?id=<?php echo $book['id']; ?>" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                            onclick="confirmDelete(<?php echo $book['id']; ?>, '<?php echo htmlspecialchars($book['title'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="bookTitle"></span>"?</p>
                <p class="text-danger small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This action cannot be undone. All chapters and purchase records will also be deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteConfirm" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i>Delete Book
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(bookId, bookTitle) {
    document.getElementById('bookTitle').textContent = bookTitle;
    document.getElementById('deleteConfirm').href = '?delete=1&id=' + bookId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>