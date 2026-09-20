<?php
require_once '../includes/functions.php';
requireAdmin();

// Handle user actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'toggle_admin' && $user_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET is_admin = NOT is_admin WHERE id = ?");
        $stmt->execute([$user_id]);
        $message = 'User admin status updated successfully!';
        $message_type = 'success';
    } elseif ($action === 'delete' && $user_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $message = 'User deleted successfully!';
        $message_type = 'success';
    }
}

// Get all users with statistics
$stmt = $pdo->query("
    SELECT u.*, 
           COUNT(DISTINCT p.id) as total_purchases,
           COALESCE(SUM(p.amount), 0) as total_spent
    FROM users u 
    LEFT JOIN purchases p ON u.id = p.user_id AND p.status = 'completed'
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

$page_title = 'Manage Users - Admin';
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
            <li class="breadcrumb-item active">Manage Users</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users me-2"></i>Manage Users</h1>
    </div>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Purchases</th>
                            <th>Total Spent</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-primary">You</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="badge bg-warning">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $user['total_purchases']; ?></td>
                                <td class="text-success">$<?php echo number_format($user['total_spent'], 2); ?></td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-warning" 
                                                    onclick="toggleAdmin(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')">
                                                <i class="fas fa-user-cog"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="toggleAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Toggle Admin Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to toggle admin status for "<span id="adminUsername"></span>"?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="adminConfirm" class="btn btn-warning">
                    <i class="fas fa-user-cog me-1"></i>Toggle Admin
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user "<span id="deleteUsername"></span>"?</p>
                <p class="text-danger small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This action cannot be undone. All user data and purchases will be deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteConfirm" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i>Delete User
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAdmin(userId, username) {
    document.getElementById('adminUsername').textContent = username;
    document.getElementById('adminConfirm').href = '?action=toggle_admin&id=' + userId;
    new bootstrap.Modal(document.getElementById('toggleAdminModal')).show();
}

function deleteUser(userId, username) {
    document.getElementById('deleteUsername').textContent = username;
    document.getElementById('deleteConfirm').href = '?action=delete&id=' + userId;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>