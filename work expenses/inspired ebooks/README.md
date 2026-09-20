# PHP EBook Website - Complete Edition

A comprehensive ebook website built with PHP, MySQL, HTML, CSS, and JavaScript for XAMPP hosting.

## 🚀 Features

### User Features
- **User Registration & Authentication** - Secure login system with password hashing
- **Book Preview System** - Read sample chapters before purchasing
- **Multiple Payment Options** - M-Pesa and PayPal integration structure
- **Personal Library** - Access purchased books anytime
- **Reading Progress** - Track your reading journey
- **Book Reviews & Ratings** - Rate and review books
- **Search Functionality** - Find books by title, author, or description
- **Responsive Design** - Works perfectly on all devices

### Admin Features
- **Complete Admin Panel** - Manage books, users, and sales
- **Book Management** - Add, edit, delete books with chapters
- **User Management** - View and manage user accounts
- **Sales Analytics** - Comprehensive sales reports and statistics
- **Review Monitoring** - Track book reviews and ratings

### Technical Features
- **Security** - SQL injection prevention, XSS protection, secure sessions
- **File Upload** - Secure image upload for book covers
- **Database Design** - Normalized database with proper relationships
- **Clean Code** - Well-organized, commented, and maintainable code
- **SEO Friendly** - Proper meta tags and URL structure

## 📁 File Structure

```
ebook_site/
├── admin/                  # Admin panel
│   ├── index.php          # Admin dashboard
│   ├── add_book.php       # Add new books
│   ├── edit_book.php      # Edit existing books
│   ├── manage_books.php   # Book management
│   ├── users.php          # User management
│   └── sales.php          # Sales reports
├── assets/                # Static assets
│   ├── css/style.css      # Main stylesheet
│   ├── js/main.js         # JavaScript functionality
│   └── uploads/           # File uploads
│       ├── covers/        # Book cover images
│       └── books/         # Book files
├── config/                # Configuration
│   └── database.php      # Database connection
├── includes/              # Shared components
│   ├── header.php         # Site header
│   ├── footer.php         # Site footer
│   └── functions.php      # Helper functions
├── supabase/migrations/   # Database schema
├── index.php              # Homepage
├── book_preview.php       # Book preview page
├── reader.php             # Book reader
├── payment.php            # Payment processing
├── my_books.php           # User's library
├── profile.php            # User profile
├── reviews.php            # Book reviews
├── search.php             # Search functionality
├── about.php              # About page
├── contact.php            # Contact form
├── login.php              # User login
├── register.php           # User registration
├── logout.php             # Logout handler
├── install.php            # Installation script
├── database.sql           # Database structure
├── .htaccess              # Apache configuration
└── README.md              # This file
```

## 🛠️ Installation Instructions

### Method 1: Automatic Installation

1. **Download and Extract** all files to `htdocs/ebook_site/`
2. **Start XAMPP** (Apache and MySQL)
3. **Run Installation** - Visit `http://localhost/ebook_site/install.php`
4. **Follow the wizard** to set up your database
5. **Delete install.php** after completion

### Method 2: Manual Installation

1. **Install XAMPP** and start Apache and MySQL services

2. **Create Database**:
   - Open phpMyAdmin (`http://localhost/phpmyadmin`)
   - Create a new database called `ebook_site`
   - Import the `database.sql` file

3. **Copy Files**:
   - Copy all files to your `htdocs/ebook_site/` directory

4. **Set Permissions**:
   - Make sure the `assets/uploads/` directories are writable
   - Windows: Right-click folders → Properties → Security → Edit → Full Control
   - Linux/Mac: `chmod 755 assets/uploads/covers assets/uploads/books`

5. **Configure Database**:
   - Update `config/database.php` with your database credentials

6. **Access the Website**:
   - Visit: `http://localhost/ebook_site/`

## 👤 Default Accounts

### Admin Account
- **Username:** admin
- **Password:** admin123
- **Email:** admin@ebooks.com

### Demo User Accounts
- **Username:** john_reader / **Password:** password123
- **Username:** jane_bookworm / **Password:** password123

## 💳 Payment Integration

The payment system is structured for easy integration with real payment gateways:

### M-Pesa Integration
- Ready for Safaricom's Daraja API
- Phone number validation included
- Transaction tracking system

### PayPal Integration
- Structured for PayPal SDK
- Secure payment processing
- Automatic status updates

## 🔒 Security Features

- **Password Security** - bcrypt hashing with salt
- **SQL Injection Prevention** - Prepared statements throughout
- **XSS Protection** - Input sanitization and output escaping
- **CSRF Protection** - Session-based security
- **File Upload Security** - Type and size validation
- **Access Control** - Role-based permissions

## 📊 Database Schema

### Core Tables
- **users** - User accounts and authentication
- **books** - Book information and metadata
- **book_chapters** - Individual book chapters
- **purchases** - Purchase transactions
- **book_reviews** - User reviews and ratings
- **reading_progress** - Reading tracking

## 🎨 Customization

### Styling
- Modify `assets/css/style.css` for custom styling
- Bootstrap 5 framework for responsive design
- Font Awesome icons included

### Functionality
- Add new payment methods in `payment.php`
- Extend admin features in the `admin/` directory
- Customize email templates and notifications

## 📱 Responsive Design

- **Mobile-First** approach
- **Bootstrap 5** responsive grid
- **Touch-Friendly** interface
- **Cross-Browser** compatibility

## 🚀 Performance Features

- **Optimized Database** queries with proper indexing
- **Image Optimization** for book covers
- **Caching Headers** via .htaccess
- **Compressed Assets** for faster loading

## 🔧 System Requirements

- **PHP 7.4+** (recommended 8.0+)
- **MySQL 5.7+** or MariaDB 10.2+
- **Apache 2.4+** with mod_rewrite
- **1GB RAM** minimum
- **100MB Disk Space** for base installation

## 📈 Analytics & Reporting

- **Sales Reports** with charts and graphs
- **User Analytics** and engagement metrics
- **Book Performance** tracking
- **Revenue Analysis** by time period

## 🛡️ Backup & Maintenance

- Regular database backups recommended
- Monitor `assets/uploads/` directory size
- Update PHP and MySQL regularly
- Review security logs periodically

## 🤝 Support & Documentation

- **Code Comments** - Extensively documented codebase
- **Error Handling** - Comprehensive error management
- **Logging System** - Track important events
- **Debug Mode** - Easy troubleshooting

## 📄 License

This project is open source and available under the MIT License.

## 🎯 Future Enhancements

- **Real-time Chat** support
- **Advanced Search** with filters
- **Wishlist** functionality
- **Social Sharing** features
- **Multi-language** support
- **API Integration** for mobile apps

---

**Built with ❤️ for the reading community**

For support or questions, please refer to the documentation or contact the development team.