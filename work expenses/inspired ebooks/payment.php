<?php
require_once 'includes/functions.php';
requireLogin();

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

// Check if user already has access
if (hasBookAccess($_SESSION['user_id'], $book_id)) {
    header('Location: reader.php?book_id=' . $book_id);
    exit();
}

$message = '';
$message_type = '';

if ($_POST) {
    $payment_method = $_POST['payment_method'];
    $phone_number = isset($_POST['phone_number']) ? sanitize($_POST['phone_number']) : '';
    
    // Validate payment method
    if (!in_array($payment_method, ['mpesa', 'paypal'])) {
        $message = 'Invalid payment method selected.';
        $message_type = 'danger';
    } elseif ($payment_method === 'mpesa' && empty($phone_number)) {
        $message = 'Please enter your M-Pesa phone number.';
        $message_type = 'danger';
    } else {
        // Generate a mock transaction ID
        $transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
        
        // Insert purchase record
        $stmt = $pdo->prepare("INSERT INTO purchases (user_id, book_id, payment_method, transaction_id, amount, status) VALUES (?, ?, ?, ?, ?, 'completed')");
        
        if ($stmt->execute([$_SESSION['user_id'], $book_id, $payment_method, $transaction_id, $book['price']])) {
            $message = 'Payment successful! You now have access to the full book.';
            $message_type = 'success';
            // In a real application, you would integrate with actual payment gateways here
        } else {
            $message = 'Payment processing failed. Please try again.';
            $message_type = 'danger';
        }
    }
}

$page_title = 'Purchase - ' . $book['title'];
include 'includes/header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="book_preview.php?id=<?php echo $book_id; ?>"><?php echo htmlspecialchars($book['title']); ?></a></li>
            <li class="breadcrumb-item active">Purchase</li>
        </ol>
    </nav>
    
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h3><i class="fas fa-shopping-cart me-2"></i>Purchase Book</h3>
                </div>
                <div class="card-body p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                            <?php echo $message; ?>
                        </div>
                        <?php if ($message_type === 'success'): ?>
                            <div class="text-center">
                                <a href="reader.php?book_id=<?php echo $book_id; ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-book-open me-2"></i>Start Reading Now
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Book Summary -->
                        <div class="book-summary mb-4">
                            <div class="row">
                                <div class="col-4">
                                    <?php if ($book['cover_image']): ?>
                                        <img src="assets/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                             class="img-fluid rounded" 
                                             alt="<?php echo htmlspecialchars($book['title']); ?>">
                                    <?php else: ?>
                                        <div class="book-cover-placeholder d-flex align-items-center justify-content-center rounded">
                                            <i class="fas fa-book fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-8">
                                    <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                                    <p class="text-muted">by <?php echo htmlspecialchars($book['author']); ?></p>
                                    <p><i class="fas fa-file-alt me-1"></i><?php echo $book['total_chapters']; ?> chapters</p>
                                    <h5 class="text-success">
                                        <i class="fas fa-dollar-sign"></i><?php echo number_format($book['price'], 2); ?>
                                    </h5>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Payment Form -->
                        <form method="POST" id="paymentForm">
                            <h5 class="mb-3"><i class="fas fa-credit-card me-2"></i>Select Payment Method</h5>
                            
                            <div class="payment-methods mb-4">
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="mpesa" value="mpesa" required>
                                    <label class="form-check-label payment-label" for="mpesa">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-mobile-alt fa-2x text-success me-3"></i>
                                            <div>
                                                <strong>M-Pesa</strong>
                                                <div class="text-muted small">Pay with your mobile money</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="form-check payment-option mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" required>
                                    <label class="form-check-label payment-label" for="paypal">
                                        <div class="d-flex align-items-center">
                                            <i class="fab fa-paypal fa-2x text-primary me-3"></i>
                                            <div>
                                                <strong>PayPal</strong>
                                                <div class="text-muted small">Pay with PayPal account or card</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- M-Pesa Phone Number Field -->
                            <div id="mpesaDetails" class="payment-details mb-3" style="display: none;">
                                <label for="phone_number" class="form-label">
                                    <i class="fas fa-phone me-1"></i>M-Pesa Phone Number
                                </label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                       placeholder="e.g., 254712345678">
                                <div class="form-text">Enter your M-Pesa registered phone number</div>
                            </div>
                            
                            <!-- PayPal Details -->
                            <div id="paypalDetails" class="payment-details mb-3" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    You will be redirected to PayPal to complete your payment securely.
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <small>
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Demo Notice:</strong> This is a demonstration. In production, 
                                    you would integrate with actual M-Pesa and PayPal APIs for real payments.
                                </small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-lock me-2"></i>Complete Purchase - $<?php echo number_format($book['price'], 2); ?>
                                </button>
                                <a href="book_preview.php?id=<?php echo $book_id; ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Preview
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const mpesaDetails = document.getElementById('mpesaDetails');
    const paypalDetails = document.getElementById('paypalDetails');
    const phoneInput = document.getElementById('phone_number');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Hide all payment details
            mpesaDetails.style.display = 'none';
            paypalDetails.style.display = 'none';
            
            // Show relevant payment details
            if (this.value === 'mpesa') {
                mpesaDetails.style.display = 'block';
                phoneInput.required = true;
            } else if (this.value === 'paypal') {
                paypalDetails.style.display = 'block';
                phoneInput.required = false;
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>