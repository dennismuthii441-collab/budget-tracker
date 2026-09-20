<?php
require_once 'includes/functions.php';
requireLogin();

$page_title = 'My Profile - EBook Store';

$message = '';
$message_type = '';

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get user statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_purchases FROM purchases WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$total_purchases = $stmt->fetch()['total_purchases'];

$stmt = $pdo->prepare("SELECT SUM(amount) as total_spent FROM purchases WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$total_spent = $stmt->fetch()['total_spent'] ?? 0;

if ($_POST) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($full_name) || empty($email)) {
        $message = 'Please fill in all required fields';
        $message_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address';
        $message_type = 'danger';
    } else {
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        
        if ($stmt->fetchColumn() > 0) {
            $message = 'Email address is already taken by another user';
            $message_type = 'danger';
        } else {
            $update_password = false;
            $hashed_password = $user['password'];
            
            // Check if user wants to change password
            if (!empty($current_password) || !empty($new_password)) {
                if (empty($current_password)) {
                    $message = 'Please enter your current password';
                    $message_type = 'danger';
                } elseif (!password_verify($current_password, $user['password'])) {
                    $message = 'Current password is incorrect';
                    $message_type = 'danger';
                } elseif (strlen($new_password) < 6) {
                    $message = 'New password must be at least 6 characters long';
                    $message_type = 'danger';
                } elseif ($new_password !== $confirm_password) {
                    $message = 'New passwords do not match';
                    $message_type = 'danger';
                } else {
                    $update_password = true;
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }
            
            if (empty($message)) {
                // Update user profile
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?");
                
                if ($stmt->execute([$full_name, $email, $hashed_password, $_SESSION['user_id']])) {
                    $message = 'Profile updated successfully!';
                    $message_type = 'success';
                    
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();
                } else {
                    $message = 'Failed to update profile. Please try again.';
                    $message_type = 'danger';
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user me-2"></i>My Profile</h3>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                    <div class="form-text">Username cannot be changed</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <hr>
                        <h5>Change Password</h5>
                        <p class="text-muted">Leave blank if you don't want to change your password</p>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                    <div class="form-text">At least 6 characters</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                            <a href="my_books.php" class="btn btn-outline-secondary">
                                <i class="fas fa-book me-2"></i>My Books
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar me-2"></i>Account Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Member Since:</span>
                            <strong><?php echo date('M j, Y', strtotime($user['created_at'])); ?></strong>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Books Purchased:</span>
                            <strong><?php echo $total_purchases; ?></strong>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Spent:</span>
                            <strong class="text-success">$<?php echo number_format($total_spent, 2); ?></strong>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Account Type:</span>
                            <strong class="text-<?php echo $user['is_admin'] ? 'warning' : 'primary'; ?>">
                                <?php echo $user['is_admin'] ? 'Administrator' : 'Customer'; ?>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-cog me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="my_books.php" class="btn btn-outline-primary">
                            <i class="fas fa-book me-2"></i>View My Books
                        </a>
                        <a href="index.php" class="btn btn-outline-success">
                            <i class="fas fa-search me-2"></i>Browse Books
                        </a>
                        <?php if ($user['is_admin']): ?>
                            <a href="admin/" class="btn btn-outline-warning">
                                <i class="fas fa-cog me-2"></i>Admin Panel
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>