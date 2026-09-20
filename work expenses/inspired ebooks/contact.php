<?php
require_once 'includes/functions.php';
$page_title = 'Contact Us - EBook Store';

$message = '';
$message_type = '';

if ($_POST) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message_text = sanitize($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $message = 'Please fill in all fields.';
        $message_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'danger';
    } else {
        // In a real application, you would send an email here
        // For demo purposes, we'll just show a success message
        $message = 'Thank you for your message! We will get back to you soon.';
        $message_type = 'success';
    }
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-envelope me-2"></i>Contact Us</h3>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Get in Touch</h5>
                            <p>Have questions about our books or need help with your account? We're here to help!</p>
                            
                            <div class="contact-info">
                                <div class="mb-3">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <strong>Email:</strong> support@ebookstore.com
                                </div>
                                <div class="mb-3">
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    <strong>Phone:</strong> +1 (555) 123-4567
                                </div>
                                <div class="mb-3">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    <strong>Hours:</strong> Mon-Fri 9AM-6PM EST
                                </div>
                                <div class="mb-3">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    <strong>Address:</strong> 123 Book Street, Reading City, RC 12345
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Send us a Message</h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <select class="form-control" id="subject" name="subject" required>
                                        <option value="">Select a subject</option>
                                        <option value="General Inquiry">General Inquiry</option>
                                        <option value="Technical Support">Technical Support</option>
                                        <option value="Payment Issue">Payment Issue</option>
                                        <option value="Book Request">Book Request</option>
                                        <option value="Account Help">Account Help</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" 
                                              placeholder="Please describe your inquiry..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-question-circle fa-3x text-primary mb-3"></i>
                    <h5>FAQ</h5>
                    <p>Find answers to commonly asked questions about our service.</p>
                    <a href="#" class="btn btn-outline-primary">View FAQ</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-life-ring fa-3x text-success mb-3"></i>
                    <h5>Support Center</h5>
                    <p>Access our comprehensive help documentation and guides.</p>
                    <a href="#" class="btn btn-outline-success">Get Help</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-comments fa-3x text-info mb-3"></i>
                    <h5>Live Chat</h5>
                    <p>Chat with our support team for immediate assistance.</p>
                    <a href="#" class="btn btn-outline-info">Start Chat</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>