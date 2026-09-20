<?php
require_once 'includes/functions.php';
$page_title = 'About Us - EBook Store';

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="hero-section bg-gradient text-white p-5 rounded mb-5">
        <div class="text-center">
            <h1 class="display-4 fw-bold">About EBook Store</h1>
            <p class="lead">Your premier destination for digital books and reading experiences</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2>Our Story</h2>
                    <p class="lead">
                        EBook Store was founded with a simple mission: to make quality books accessible to everyone, 
                        anywhere, at any time.
                    </p>
                    
                    <p>
                        In today's digital age, we believe that reading should be convenient, affordable, and enjoyable. 
                        Our platform brings together authors and readers in a seamless digital environment where stories 
                        come to life and knowledge is shared freely.
                    </p>
                    
                    <h3>What We Offer</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Free Previews:</strong> Try before you buy with our generous preview system
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Instant Access:</strong> Start reading immediately after purchase
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Multiple Payment Options:</strong> Pay with M-Pesa, PayPal, and more
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Cross-Device Reading:</strong> Access your books on any device
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Author Support:</strong> We help authors reach their audience
                        </li>
                    </ul>
                    
                    <h3>Our Values</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-heart text-danger me-2"></i>Passion for Reading</h5>
                            <p>We believe in the transformative power of books and stories.</p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-users text-primary me-2"></i>Community First</h5>
                            <p>Our readers and authors are at the center of everything we do.</p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-shield-alt text-success me-2"></i>Trust & Security</h5>
                            <p>Your data and payments are protected with industry-leading security.</p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-rocket text-warning me-2"></i>Innovation</h5>
                            <p>We continuously improve our platform for the best reading experience.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>By the Numbers</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get statistics
                    $stmt = $pdo->query("SELECT COUNT(*) as total_books FROM books");
                    $total_books = $stmt->fetch()['total_books'];
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE is_admin = 0");
                    $total_users = $stmt->fetch()['total_users'];
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as total_purchases FROM purchases WHERE status = 'completed'");
                    $total_purchases = $stmt->fetch()['total_purchases'];
                    
                    $stmt = $pdo->query("SELECT COUNT(*) as total_chapters FROM book_chapters");
                    $total_chapters = $stmt->fetch()['total_chapters'];
                    ?>
                    
                    <div class="stat-item text-center mb-3">
                        <h3 class="text-primary"><?php echo $total_books; ?></h3>
                        <p class="mb-0">Books Available</p>
                    </div>
                    <div class="stat-item text-center mb-3">
                        <h3 class="text-success"><?php echo $total_users; ?></h3>
                        <p class="mb-0">Happy Readers</p>
                    </div>
                    <div class="stat-item text-center mb-3">
                        <h3 class="text-info"><?php echo $total_purchases; ?></h3>
                        <p class="mb-0">Books Sold</p>
                    </div>
                    <div class="stat-item text-center mb-3">
                        <h3 class="text-warning"><?php echo $total_chapters; ?></h3>
                        <p class="mb-0">Chapters Written</p>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-envelope me-2"></i>Get in Touch</h5>
                </div>
                <div class="card-body">
                    <p>Have questions or suggestions? We'd love to hear from you!</p>
                    <a href="contact.php" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users me-2"></i>Meet Our Team</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <div class="team-member">
                                <div class="avatar mb-3">
                                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                                </div>
                                <h5>John Smith</h5>
                                <p class="text-muted">Founder & CEO</p>
                                <p>Passionate about books and technology, John founded EBook Store to revolutionize digital reading.</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-4">
                            <div class="team-member">
                                <div class="avatar mb-3">
                                    <i class="fas fa-user-circle fa-5x text-success"></i>
                                </div>
                                <h5>Sarah Johnson</h5>
                                <p class="text-muted">Head of Content</p>
                                <p>Sarah works closely with authors to bring the best books to our platform and ensure quality content.</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-4">
                            <div class="team-member">
                                <div class="avatar mb-3">
                                    <i class="fas fa-user-circle fa-5x text-info"></i>
                                </div>
                                <h5>Mike Chen</h5>
                                <p class="text-muted">Lead Developer</p>
                                <p>Mike ensures our platform runs smoothly and continuously improves the user experience.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>