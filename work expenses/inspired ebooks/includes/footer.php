</main>
    
    <!-- Footer -->
    <footer class="text-white text-center py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-md-start">
                    <h5 class="mb-2">
                        <i class="fas fa-book-open me-2"></i>EBook Store
                    </h5>
                    <p class="mb-0 small">Your gateway to infinite stories</p>
                </div>
                <div class="col-md-4 text-center my-3 my-md-0">
                    <div class="social-links">
                        <a href="#" class="text-white me-3" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-white me-3" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-white me-3" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-white" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="mb-1">&copy; 2025 EBook Store. All rights reserved.</p>
                    <small class="text-light">
                        <i class="fas fa-heart text-danger"></i> Made for book lovers
                    </small>
                </div>
            </div>
            <hr class="my-3 border-light">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="about.php" class="text-light text-decoration-none small">About Us</a>
                        <a href="contact.php" class="text-light text-decoration-none small">Contact</a>
                        <a href="#" class="text-light text-decoration-none small">Privacy Policy</a>
                        <a href="#" class="text-light text-decoration-none small">Terms of Service</a>
                        <a href="#" class="text-light text-decoration-none small">Help Center</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo isset($js_path) ? $js_path : 'assets/js/main.js'; ?>"></script>
    
    <!-- Performance and Analytics -->
    <script>
        // Preload critical resources
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('SW registered: ', registration);
                }).catch(function(registrationError) {
                    console.log('SW registration failed: ', registrationError);
                });
            });
        }
        
        // Track page views (replace with your analytics)
        if (typeof gtag !== 'undefined') {
            gtag('config', 'GA_MEASUREMENT_ID', {
                page_title: document.title,
                page_location: window.location.href
            });
        }
    </script>
</body>
</html>