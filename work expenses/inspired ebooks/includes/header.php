<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'EBook Store - Your Digital Library'; ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Discover amazing books and start your reading journey. Preview chapters for free, then unlock the full experience with secure payments.">
    <meta name="keywords" content="ebooks, digital books, reading, literature, online library, book store">
    <meta name="author" content="EBook Store">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title : 'EBook Store - Your Digital Library'; ?>">
    <meta property="og:description" content="Discover amazing books and start your reading journey with our curated collection of digital books.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo getBaseUrl(); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;800&family=Georgia:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo isset($css_path) ? $css_path : 'assets/css/style.css'; ?>">
    
    <!-- Performance Optimization -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo isset($home_path) ? $home_path : 'index.php'; ?>">
                <i class="fas fa-book-open me-2"></i>EBook Store
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav me-auto">
                    <a class="nav-link" href="<?php echo isset($home_path) ? $home_path : 'index.php'; ?>">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                    <a class="nav-link" href="<?php echo isset($search_path) ? $search_path : 'search.php'; ?>">
                        <i class="fas fa-search me-1"></i>Discover
                    </a>
                    <a class="nav-link" href="<?php echo isset($about_path) ? $about_path : 'about.php'; ?>">
                        <i class="fas fa-info-circle me-1"></i>About
                    </a>
                    <a class="nav-link" href="<?php echo isset($contact_path) ? $contact_path : 'contact.php'; ?>">
                        <i class="fas fa-envelope me-1"></i>Contact
                    </a>
                </div>
                <div class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <a class="nav-link" href="<?php echo isset($my_books_path) ? $my_books_path : 'my_books.php'; ?>">
                            <i class="fas fa-book me-1"></i>My Library
                        </a>
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo isset($profile_path) ? $profile_path : 'profile.php'; ?>">
                                    <i class="fas fa-user me-2"></i>My Profile
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo isset($my_books_path) ? $my_books_path : 'my_books.php'; ?>">
                                    <i class="fas fa-book me-2"></i>My Library
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?php echo isset($admin_path) ? $admin_path : 'admin/'; ?>">
                                        <i class="fas fa-cog me-2"></i>Admin Dashboard
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo isset($logout_path) ? $logout_path : 'logout.php'; ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a class="nav-link" href="<?php echo isset($login_path) ? $login_path : 'login.php'; ?>">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                        <a class="nav-link" href="<?php echo isset($register_path) ? $register_path : 'register.php'; ?>">
                            <i class="fas fa-user-plus me-1"></i>Join Us
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="flex-grow-1">