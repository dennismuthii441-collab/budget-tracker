<?php
require_once 'includes/functions.php';
require_once 'includes/mpesa.php';
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
$checkout_request_id = '';

if ($_POST) {
    $payment_method = $_POST['payment_method'];
    $phone_number = isset($_POST['phone_number']) ? sanitize($_POST['phone_number']) : '';
    
    if (!in_array($payment_method, ['mpesa', 'paypal'])) {
        $message = 'Invalid payment method selected.';
        $message_type = 'danger';
    } elseif ($payment_method === 'mpesa' && empty($phone_number)) {
        $message = 'Please enter your M-Pesa phone number.';
        $message_type = 'danger';
    } else {
        try {
            if ($payment_method === 'mpesa') {
                // Initialize M-Pesa STK Push
                $mpesa = new MpesaAPI();
                $account_reference = 'BOOK_' . $book_id . '_USER_' . $_SESSION['user_id'];
                $transaction_desc = 'Purchase of ' . $book['title'];
                
                $response = $mpesa->initiateSTKPush(
                    $phone_number,
                    $book['price'],
                    $account_reference,
                    $transaction_desc
                );
                
                if (isset($response['CheckoutRequestID'])) {
                    $checkout_request_id = $response['CheckoutRequestID'];
                    
                    // Insert purchase record with pending status
                    $stmt = $pdo->prepare("INSERT INTO purchases (user_id, book_id, payment_method, transaction_id, amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                    $stmt->execute([$_SESSION['user_id'], $book_id, $payment_method, $checkout_request_id, $book['price']]);
                    
                    $message = 'Payment request sent! Please check your phone and enter your M-Pesa PIN to complete the payment.';
                    $message_type = 'info';
                } else {
                    $message = 'Failed to initiate M-Pesa payment: ' . ($response['errorMessage'] ?? 'Unknown error');
                    $message_type = 'danger';
                }
            } else {
                // PayPal payment (mock for demo)
                $transaction_id = 'PAYPAL_' . time() . '_' . rand(1000, 9999);
                
                $stmt = $pdo->prepare("INSERT INTO purchases (user_id, book_id, payment_method, transaction_id, amount, status) VALUES (?, ?, ?, ?, ?, 'completed')");
                
                if ($stmt->execute([$_SESSION['user_id'], $book_id, $payment_method, $transaction_id, $book['price']])) {
                    $message = 'PayPal payment successful! You now have access to the full book.';
                    $message_type = 'success';
                }
            }
        } catch (Exception $e) {
            $message = 'Payment processing failed: ' . $e->getMessage();
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
                            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'info' ? 'info-circle' : 'exclamation-triangle'); ?> me-2"></i>
                            <?php echo $message; ?>
                        </div>
                        
                        <?php if ($message_type === 'success'): ?>
                            <div class="text-center">
                                <a href="reader.php?book_id=<?php echo $book_id; ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-book-open me-2"></i>Start Reading Now
                                </a>
                            </div>
                        <?php elseif ($message_type === 'info' && !empty($checkout_request_id)): ?>
                            <div class="payment-status-checker" data-checkout-id="<?php echo $checkout_request_id; ?>">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Checking payment status...</span>
                                    </div>
                                    <p class="mt-2">Waiting for payment confirmation...</p>
                                    <button type="button" class="btn btn-outline-primary" onclick="checkPaymentStatus()">
                                        <i class="fas fa-sync me-2"></i>Check Status
                                    </button>
                                </div>
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
                                                <strong>M-Pesa STK Push</strong>
                                                <div class="text-muted small">Pay with your mobile money (PIN required)</div>
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
                                       placeholder="e.g., 0712345678 or 254712345678">
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
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <strong>Secure Payment:</strong> M-Pesa STK Push will send a payment prompt to your phone. 
                                    Enter your M-Pesa PIN to complete the transaction.
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
            mpesaDetails.style.display = 'none';
            paypalDetails.style.display = 'none';
            
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

function checkPaymentStatus() {
    const checkoutId = document.querySelector('.payment-status-checker').dataset.checkoutId;
    
    fetch('check_payment_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({checkout_request_id: checkoutId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'completed') {
            location.reload();
        } else if (data.status === 'failed') {
            alert('Payment failed. Please try again.');
            location.reload();
        } else {
            alert('Payment is still pending. Please complete the payment on your phone.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error checking payment status. Please try again.');
    });
}

// Auto-check payment status every 5 seconds if waiting
if (document.querySelector('.payment-status-checker')) {
    setInterval(checkPaymentStatus, 5000);
}
</script>

<?php include 'includes/footer.php'; ?>