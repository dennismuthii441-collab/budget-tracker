<?php
require_once 'includes/functions.php';
requireLogin();

if (!isset($_GET['book_id'])) {
    header('Location: index.php');
    exit();
}

$book_id = (int)$_GET['book_id'];

// Check if user has access to this book
if (!hasBookAccess($_SESSION['user_id'], $book_id)) {
    header('Location: book_preview.php?id=' . $book_id);
    exit();
}

// Get book details
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: index.php');
    exit();
}

// Get all chapters
$stmt = $pdo->prepare("SELECT * FROM book_chapters WHERE book_id = ? ORDER BY chapter_number");
$stmt->execute([$book_id]);
$chapters = $stmt->fetchAll();

$current_chapter = isset($_GET['chapter']) ? (int)$_GET['chapter'] : 1;
$current_chapter_data = null;

foreach ($chapters as $chapter) {
    if ($chapter['chapter_number'] == $current_chapter) {
        $current_chapter_data = $chapter;
        break;
    }
}

if (!$current_chapter_data && !empty($chapters)) {
    $current_chapter_data = $chapters[0];
    $current_chapter = $current_chapter_data['chapter_number'];
}

$page_title = $book['title'] . ' - Chapter ' . $current_chapter;
include 'includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Sidebar with chapters -->
        <div class="col-md-3 col-lg-2">
            <div class="reader-sidebar">
                <div class="book-info mb-4">
                    <h6 class="text-muted">Now Reading</h6>
                    <h5><?php echo htmlspecialchars($book['title']); ?></h5>
                    <p class="small text-muted">by <?php echo htmlspecialchars($book['author']); ?></p>
                </div>
                
                <div class="chapters-list">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-list me-2"></i>Chapters
                    </h6>
                    <?php if (empty($chapters)): ?>
                        <p class="text-muted small">No chapters available</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($chapters as $chapter): ?>
                                <a href="reader.php?book_id=<?php echo $book_id; ?>&chapter=<?php echo $chapter['chapter_number']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $chapter['chapter_number'] == $current_chapter ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold">Chapter <?php echo $chapter['chapter_number']; ?></div>
                                            <small><?php echo htmlspecialchars($chapter['chapter_title']); ?></small>
                                        </div>
                                        <?php if ($chapter['is_preview']): ?>
                                            <span class="badge bg-info">Preview</span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-4">
                    <a href="book_preview.php?id=<?php echo $book_id; ?>" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-arrow-left me-2"></i>Back to Book
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main reading area -->
        <div class="col-md-9 col-lg-10">
            <div class="reader-content">
                <?php if ($current_chapter_data): ?>
                    <div class="chapter-header mb-4">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="book_preview.php?id=<?php echo $book_id; ?>"><?php echo htmlspecialchars($book['title']); ?></a></li>
                                <li class="breadcrumb-item active">Chapter <?php echo $current_chapter; ?></li>
                            </ol>
                        </nav>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1>Chapter <?php echo $current_chapter_data['chapter_number']; ?></h1>
                                <h2 class="h4 text-muted"><?php echo htmlspecialchars($current_chapter_data['chapter_title']); ?></h2>
                            </div>
                            <div class="chapter-controls">
                                <?php
                                $prev_chapter = null;
                                $next_chapter = null;
                                
                                foreach ($chapters as $index => $chapter) {
                                    if ($chapter['chapter_number'] == $current_chapter) {
                                        if ($index > 0) {
                                            $prev_chapter = $chapters[$index - 1];
                                        }
                                        if ($index < count($chapters) - 1) {
                                            $next_chapter = $chapters[$index + 1];
                                        }
                                        break;
                                    }
                                }
                                ?>
                                
                                <?php if ($prev_chapter): ?>
                                    <a href="reader.php?book_id=<?php echo $book_id; ?>&chapter=<?php echo $prev_chapter['chapter_number']; ?>" 
                                       class="btn btn-outline-primary me-2">
                                        <i class="fas fa-chevron-left me-1"></i>Previous
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($next_chapter): ?>
                                    <a href="reader.php?book_id=<?php echo $book_id; ?>&chapter=<?php echo $next_chapter['chapter_number']; ?>" 
                                       class="btn btn-outline-primary">
                                        Next<i class="fas fa-chevron-right ms-1"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chapter-content">
                        <div class="reading-text">
                            <?php echo nl2br(htmlspecialchars($current_chapter_data['content'])); ?>
                        </div>
                    </div>
                    
                    <!-- Chapter navigation at bottom -->
                    <div class="chapter-navigation mt-5 pt-4 border-top">
                        <div class="row">
                            <div class="col-6">
                                <?php if ($prev_chapter): ?>
                                    <a href="reader.php?book_id=<?php echo $book_id; ?>&chapter=<?php echo $prev_chapter['chapter_number']; ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-chevron-left me-2"></i>
                                        <div>
                                            <div class="small">Previous Chapter</div>
                                            <div><?php echo htmlspecialchars($prev_chapter['chapter_title']); ?></div>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="col-6 text-end">
                                <?php if ($next_chapter): ?>
                                    <a href="reader.php?book_id=<?php echo $book_id; ?>&chapter=<?php echo $next_chapter['chapter_number']; ?>" 
                                       class="btn btn-outline-primary">
                                        <div>
                                            <div class="small">Next Chapter</div>
                                            <div><?php echo htmlspecialchars($next_chapter['chapter_title']); ?></div>
                                        </div>
                                        <i class="fas fa-chevron-right ms-2"></i>
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Congratulations! You've finished reading this book.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Chapter not found or no chapters available for this book.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>