<?php
// Installation script for EBook Store
// This file helps set up the database and initial configuration

$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

if ($_POST) {
    if ($step === 1) {
        // Database connection test
        $host = $_POST['host'];
        $dbname = $_POST['dbname'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Store connection details in session for next step
            session_start();
            $_SESSION['db_config'] = [
                'host' => $host,
                'dbname' => $dbname,
                'username' => $username,
                'password' => $password
            ];
            
            $success = 'Database connection successful!';
            header('Location: install.php?step=2');
            exit();
        } catch(PDOException $e) {
            $error = 'Database connection failed: ' . $e->getMessage();
        }
    } elseif ($step === 2) {
        // Create database tables
        session_start();
        if (!isset($_SESSION['db_config'])) {
            header('Location: install.php?step=1');
            exit();
        }
        
        $config = $_SESSION['db_config'];
        
        try {
            $pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8", 
                          $config['username'], $config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Read and execute SQL file
            $sql = file_get_contents('database.sql');
            $statements = explode(';', $sql);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            // Create config file
            $config_content = "<?php
\$host = '{$config['host']}';
\$dbname = '{$config['dbname']}';
\$username = '{$config['username']}';
\$password = '{$config['password']}';

try {
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException \$e) {
    die(\"Connection failed: \" . \$e->getMessage());
}
?>";
            
            file_put_contents('config/database.php', $config_content);
            
            $success = 'Database tables created successfully!';
            header('Location: install.php?step=3');
            exit();
        } catch(Exception $e) {
            $error = 'Installation failed: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EBook Store - Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3><i class="fas fa-cog me-2"></i>EBook Store Installation</h3>
                    </div>
                    <div class="card-body">
                        <!-- Progress Steps -->
                        <div class="row mb-4">
                            <div class="col-4 text-center">
                                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?>">
                                    <div class="step-number <?php echo $step >= 1 ? 'bg-primary text-white' : 'bg-light'; ?>">1</div>
                                    <div class="step-label">Database</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">
                                    <div class="step-number <?php echo $step >= 2 ? 'bg-primary text-white' : 'bg-light'; ?>">2</div>
                                    <div class="step-label">Setup</div>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                                    <div class="step-number <?php echo $step >= 3 ? 'bg-success text-white' : 'bg-light'; ?>">3</div>
                                    <div class="step-label">Complete</div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($step === 1): ?>
                            <h4>Step 1: Database Configuration</h4>
                            <p>Please enter your database connection details:</p>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="host" class="form-label">Database Host</label>
                                    <input type="text" class="form-control" id="host" name="host" value="localhost" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dbname" class="form-label">Database Name</label>
                                    <input type="text" class="form-control" id="dbname" name="dbname" value="ebook_site" required>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Database Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="root" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Database Password</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i>Test Connection
                                </button>
                            </form>
                            
                        <?php elseif ($step === 2): ?>
                            <h4>Step 2: Database Setup</h4>
                            <p>Click the button below to create the database tables and initial data:</p>
                            
                            <form method="POST">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-database me-2"></i>Create Database Tables
                                </button>
                            </form>
                            
                        <?php elseif ($step === 3): ?>
                            <h4>Installation Complete!</h4>
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle me-2"></i>Congratulations!</h5>
                                <p>EBook Store has been successfully installed. You can now:</p>
                                <ul>
                                    <li>Access the website at <a href="index.php">index.php</a></li>
                                    <li>Login as admin with username: <strong>admin</strong> and password: <strong>admin123</strong></li>
                                    <li>Start adding books and managing your store</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Security Note:</h6>
                                <p class="mb-0">Please delete this <code>install.php</code> file for security reasons.</p>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="index.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-home me-2"></i>Go to Website
                                </a>
                                <a href="admin/" class="btn btn-outline-secondary">
                                    <i class="fas fa-cog me-2"></i>Admin Panel
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: bold;
    }
    
    .step-label {
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .step.active .step-label {
        color: #495057;
        font-weight: 500;
    }
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>