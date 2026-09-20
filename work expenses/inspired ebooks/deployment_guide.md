# 🚀 Online Deployment Guide for PHP EBook Website

## 📋 Prerequisites for Online Deployment

### 1. **Web Hosting Requirements**
- **PHP 7.4+** (recommended 8.0+)
- **MySQL 5.7+** or MariaDB 10.2+
- **Apache/Nginx** with mod_rewrite
- **SSL Certificate** (required for M-Pesa)
- **cURL extension** enabled
- **1GB+ RAM** and **5GB+ storage**

### 2. **Recommended Hosting Providers**
- **Shared Hosting**: Hostinger, Namecheap, Bluehost
- **VPS**: DigitalOcean, Linode, Vultr
- **Cloud**: AWS, Google Cloud, Azure
- **Local (Kenya)**: Sasahost, Truehost, Webhost Kenya

## 🔧 Deployment Steps

### Step 1: Prepare Your Files
1. **Download all files** from your local development
2. **Update configuration** files for production
3. **Set up M-Pesa credentials** (see M-Pesa setup below)

### Step 2: Upload to Server
```bash
# Via FTP/SFTP
- Upload all files to public_html/ or www/ directory
- Ensure proper file permissions (755 for directories, 644 for files)
- Make uploads directory writable (755)
```

### Step 3: Database Setup
1. **Create MySQL database** via hosting control panel
2. **Import database.sql** file
3. **Update config/database.php** with production credentials:

```php
$host = 'localhost'; // or your DB host
$dbname = 'your_production_db_name';
$username = 'your_db_username';
$password = 'your_db_password';
```

### Step 4: SSL Certificate
- **Enable SSL** through your hosting provider
- **Update all URLs** to use HTTPS
- **Test M-Pesa callback** URLs

## 💳 M-Pesa Integration Setup

### 1. **Get M-Pesa API Credentials**
1. Visit [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
2. **Create an account** and verify your identity
3. **Create a new app** and select "M-Pesa Express (STK Push)"
4. **Get your credentials**:
   - Consumer Key
   - Consumer Secret
   - Business Short Code
   - Passkey

### 2. **Update M-Pesa Configuration**
Edit `config/mpesa.php`:

```php
// Production credentials
const CONSUMER_KEY = 'your_production_consumer_key';
const CONSUMER_SECRET = 'your_production_consumer_secret';
const BUSINESS_SHORT_CODE = 'your_business_shortcode';
const PASSKEY = 'your_production_passkey';
const ENVIRONMENT = 'production'; // Change from 'sandbox'
```

### 3. **Configure Callback URL**
- **Callback URL**: `https://yourdomain.com/mpesa_callback.php`
- **Register this URL** in your M-Pesa app settings
- **Ensure SSL** is working properly

### 4. **Test M-Pesa Integration**
1. **Use sandbox first** to test functionality
2. **Switch to production** after successful testing
3. **Test with small amounts** initially

## 🔒 Security Considerations

### 1. **File Permissions**
```bash
# Set proper permissions
chmod 755 assets/uploads/
chmod 644 config/database.php
chmod 644 config/mpesa.php
```

### 2. **Environment Variables**
Consider using environment variables for sensitive data:

```php
// In config/database.php
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'ebook_site';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';
```

### 3. **Security Headers**
Update `.htaccess` for production:

```apache
# Additional security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

## 🌐 Domain and DNS Setup

### 1. **Domain Configuration**
- **Point domain** to your hosting server
- **Set up subdomain** if needed (e.g., books.yourdomain.com)
- **Configure DNS** A records

### 2. **SSL Certificate**
- **Free SSL**: Let's Encrypt (most hosts provide this)
- **Paid SSL**: Comodo, DigiCert for enhanced trust
- **Verify SSL** is working: https://www.ssllabs.com/ssltest/

## 📊 Performance Optimization

### 1. **Enable Caching**
```apache
# In .htaccess
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
</IfModule>
```

### 2. **Database Optimization**
- **Add indexes** to frequently queried columns
- **Optimize images** before uploading
- **Use CDN** for static assets if needed

### 3. **PHP Configuration**
```ini
# Recommended php.ini settings
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

## 🔍 Testing Checklist

### Before Going Live:
- [ ] **Database connection** works
- [ ] **File uploads** working
- [ ] **User registration/login** functional
- [ ] **Book preview** system working
- [ ] **M-Pesa STK Push** tested
- [ ] **PayPal integration** tested
- [ ] **Admin panel** accessible
- [ ] **SSL certificate** installed
- [ ] **Email functionality** working
- [ ] **Mobile responsiveness** verified

## 📈 Post-Deployment

### 1. **Monitoring**
- **Set up error logging**
- **Monitor M-Pesa transactions**
- **Track user registrations**
- **Monitor server resources**

### 2. **Backup Strategy**
- **Daily database backups**
- **Weekly file backups**
- **Test restore procedures**

### 3. **Updates and Maintenance**
- **Regular security updates**
- **Monitor for PHP/MySQL updates**
- **Update M-Pesa credentials** if needed

## 💰 Cost Estimates

### Hosting Costs (Monthly):
- **Shared Hosting**: $3-10/month
- **VPS**: $10-50/month
- **Cloud Hosting**: $20-100/month

### Additional Costs:
- **Domain**: $10-15/year
- **SSL Certificate**: Free-$100/year
- **M-Pesa Integration**: Transaction fees apply

## 🆘 Troubleshooting

### Common Issues:
1. **M-Pesa callback not working**: Check SSL and URL accessibility
2. **File upload errors**: Check permissions and PHP limits
3. **Database connection errors**: Verify credentials and host
4. **SSL issues**: Ensure proper certificate installation

### Support Resources:
- **Safaricom Developer Support**: For M-Pesa issues
- **Hosting Provider Support**: For server-related issues
- **PHP Documentation**: For code-related problems

---

**🎉 Your PHP EBook Website is now ready for the world!**

Remember to test thoroughly before launching and always keep backups of your data.