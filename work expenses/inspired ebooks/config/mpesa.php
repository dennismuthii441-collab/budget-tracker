<?php
// M-Pesa Configuration
class MpesaConfig {
    // Sandbox credentials (replace with production for live)
    const CONSUMER_KEY = 'your_consumer_key_here';
    const CONSUMER_SECRET = 'your_consumer_secret_here';
    const BUSINESS_SHORT_CODE = '174379'; // Sandbox shortcode
    const PASSKEY = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'; // Sandbox passkey
    
    // URLs
    const SANDBOX_BASE_URL = 'https://sandbox.safaricom.co.ke';
    const PRODUCTION_BASE_URL = 'https://api.safaricom.co.ke';
    
    // Endpoints
    const AUTH_URL = '/oauth/v1/generate?grant_type=client_credentials';
    const STK_PUSH_URL = '/mpesa/stkpush/v1/processrequest';
    const STK_QUERY_URL = '/mpesa/stkpushquery/v1/query';
    
    // Environment (sandbox or production)
    const ENVIRONMENT = 'sandbox'; // Change to 'production' for live
    
    public static function getBaseUrl() {
        return self::ENVIRONMENT === 'production' ? self::PRODUCTION_BASE_URL : self::SANDBOX_BASE_URL;
    }
    
    public static function getCallbackUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . $host . '/ebook_site/mpesa_callback.php';
    }
}
?>