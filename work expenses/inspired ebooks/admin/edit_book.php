<?php
require_once '../includes/functions.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: manage_books.php');
    exit();
}

$book_id = (int)$_GET['id'];

// Get book details
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: manage_books.php');
    exit();
}

// Get book chapters
$stmt = $pdo->prepare("SELECT * FROM book_chapters WHERE book_id = ? ORDER BY chapter_number");
$stmt->execute([$book_id]);
$chapters = $stmt->fetchAll();

$message = '';
$message_type = '';

if ($_POST) {
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $preview_chapters = (int)$_POST['preview_chapters'];
    $total_chapters = (int)$_POST['total_chapters'];
    
    // Handle cover image upload
    $cover_image = $book['cover_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $upload_dir = '../assets/uploads/covers/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $new_cover = uploadFile($_FILES['cover_image'], $upload_dir);
        if ($new_cover) {
            // Delete old cover if exists
            if ($cover_image && file_exists($upload_dir . $cover_image)) {
                unlink($upload_dir . $cover_image);
            }
            $cover_image = $new_cover;
        }
    }
    
    // Validation
    if (empty($title) || empty($author) || empty($description) || $price <= 0 || $total_chapters <= 0) {
        $message = 'Please fill in all required fields with valid values.';
        $message_type = 'danger';
    } elseif ($preview_chapters > $total_chapters) {
        $message = 'Preview chapters cannot be more than total chapters.';
        $message_type = 'danger';
    } else {
        // Update book
        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, description = ?, cover_image = ?, price = ?, preview_chapters = ?, total_chapters = ? WHERE id = ?");
        
        if ($stmt->execute([$title, $author, $description, $cover_image, $price, $preview_chapters, $total_chapters, $book_id])) {
            // Update existing chapters
            if (!empty($_POST['chapters'])) {
                // Delete existing chapters
                $stmt = $pdo->prepare("DELETE FROM book_chapters WHERE book_id = ?");
                $stmt->execute([$book_id]);
                
                // Add updated chapters
                $chapters = $_POST['chapters'];
                foreach ($chapters as $index => $chapter_data) {
                    if (!empty($chapter_data['title']) && !empty($chapter_data['content'])) {
                        $chapter_number = $index + 1;
                        $chapter_title = sanitize($chapter_data['title']);
                        $chapter_content = $chapter_data['content'];
                        $is_preview = $chapter_number <= $preview_chapters ? 1 : 0;
                        
                        $stmt = $pdo->prepare("INSERT INTO book_chapters (book_id, chapter_number, chapter_title, content, is_preview) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$book_id, $chapter_number, $chapter_title, $chapter_content, $is_preview]);
                    }
                }
            }
            
            $message = 'Book updated successfully!';
            $message_type = 'success';
            
            // Refresh book data
            $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
            $stmt->execute([$book_id]);
            $book = $stmt->fetch();
            
            $stmt = $pdo->prepare("SELECT * FROM book_chapters WHERE book_id = ? ORDER BY chapter_number");
            $stmt->execute([$book_id]);
            $chapters = $stmt->fetchAll();
        } else {
            $message = 'Failed to update book. Please try again.';
            $message_type = 'danger';
        }
    }
}

$page_title = 'Edit Book - Admin';
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
            <li class="breadcrumb-item"><a href="manage_books.php">Manage Books</a></li>
            <li class="breadcrumb-item active">Edit Book</li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-edit me-2"></i>Edit Book: <?php echo htmlspecialchars($book['title']); ?></h3>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" id="editBookForm">
                        <!-- Basic Book Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Book Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($book['title']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="author" class="form-label">Author *</label>
                                    <input type="text" class="form-control" id="author" name="author" 
                                           value="<?php echo htmlspecialchars($book['author']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($book['description']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price ($) *</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" 
                                           value="<?php echo $book['price']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="total_chapters" class="form-label">Total Chapters *</label>
                                    <input type="number" class="form-control" id="total_chapters" name="total_chapters" min="1" 
                                           value="<?php echo $book['total_chapters']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="preview_chapters" class="form-label">Preview Chapters</label>
                                    <input type="number" class="form-control" id="preview_chapters" name="preview_chapters" min="0" 
                                           value="<?php echo $book['preview_chapters']; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cover_image" class="form-label">Cover Image</label>
                            <?php if ($book['cover_image']): ?>
                                <div class="mb-2">
                                    <img src="../assets/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                         class="img-thumbnail" style="max-height: 150px;" alt="Current cover">
                                    <div class="form-text">Current cover image</div>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*">
                            <div class="form-text">Upload a new cover image (optional)</div>
                        </div>
                        
                        <!-- Chapters Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Book Chapters</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addChapter">
                                    <i class="fas fa-plus me-1"></i>Add Chapter
                                </button>
                            </div>
                            
                            <div id="chaptersContainer">
                                <?php foreach ($chapters as $index => $chapter): ?>
                                    <div class="chapter-item mb-3 p-3 border rounded">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6>Chapter <?php echo $index + 1; ?></h6>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeChapter(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="mb-2">
                                            <input type="text" class="form-control" name="chapters[<?php echo $index; ?>][title]" 
                                                   placeholder="Chapter Title" value="<?php echo htmlspecialchars($chapter['chapter_title']); ?>">
                                        </div>
                                        <div>
                                            <textarea class="form-control" name="chapters[<?php echo $index; ?>][content]" rows="4" 
                                                      placeholder="Chapter Content"><?php echo htmlspecialchars($chapter['content']); ?></textarea>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Update Book
                            </button>
                            <a href="manage_books.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle me-2"></i>Book Preview</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($book['cover_image']): ?>
                        <img src="../assets/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                             class="img-fluid rounded mb-3" style="max-height: 200px;" alt="Book cover">
                    <?php else: ?>
                        <div class="book-cover-placeholder-admin d-flex align-items-center justify-content-center rounded mb-3" style="height: 200px;">
                            <i class="fas fa-book fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <h6><?php echo htmlspecialchars($book['title']); ?></h6>
                    <p class="text-muted">by <?php echo htmlspecialchars($book['author']); ?></p>
                    <p class="text-success h5">$<?php echo number_format($book['price'], 2); ?></p>
                    <a href="../book_preview.php?id=<?php echo $book['id']; ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                        <i class="fas fa-eye me-1"></i>View Live Preview
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let chapterCount = <?php echo count($chapters); ?>;

document.getElementById('addChapter').addEventListener('click', function() {
    chapterCount++;
    const container = document.getElementById('chaptersContainer');
    
    const chapterDiv = document.createElement('div');
    chapterDiv.className = 'chapter-item mb-3 p-3 border rounded';
    chapterDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6>Chapter ${chapterCount}</h6>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeChapter(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="mb-2">
            <input type="text" class="form-control" name="chapters[${chapterCount-1}][title]" placeholder="Chapter Title">
        </div>
        <div>
            <textarea class="form-control" name="chapters[${chapterCount-1}][content]" rows="4" placeholder="Chapter Content"></textarea>
        </div>
    `;
    
    container.appendChild(chapterDiv);
});

function removeChapter(button) {
    button.closest('.chapter-item').remove();
}

// Validate preview chapters
document.getElementById('total_chapters').addEventListener('input', function() {
    const totalChapters = parseInt(this.value) || 0;
    const previewInput = document.getElementById('preview_chapters');
    previewInput.max = totalChapters;
    
    if (parseInt(previewInput.value) > totalChapters) {
        previewInput.value = totalChapters;
    }
});
</script>

<?php include '../includes/footer.php'; ?>