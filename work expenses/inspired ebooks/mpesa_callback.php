<?php
require_once 'includes/functions.php';

// Log the callback for debugging
$callback_data = file_get_contents('php://input');
$log_file = 'logs/mpesa_callbacks.log';

// Create logs directory if it doesn't exist
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}

// Log the callback
file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $callback_data . "\n", FILE_APPEND);

// Decode the callback data
$callback = json_decode($callback_data, true);

if ($callback && isset($callback['Body']['stkCallback'])) {
    $stk_callback = $callback['Body']['stkCallback'];
    $checkout_request_id = $stk_callback['CheckoutRequestID'];
    $result_code = $stk_callback['ResultCode'];
    
    // Find the purchase record
    $stmt = $pdo->prepare("SELECT * FROM purchases WHERE transaction_id = ?");
    $stmt->execute([$checkout_request_id]);
    $purchase = $stmt->fetch();
    
    if ($purchase) {
        if ($result_code == 0) {
            // Payment successful
            $stmt = $pdo->prepare("UPDATE purchases SET status = 'completed' WHERE transaction_id = ?");
            $stmt->execute([$checkout_request_id]);
            
            // Extract payment details if available
            if (isset($stk_callback['CallbackMetadata']['Item'])) {
                $metadata = $stk_callback['CallbackMetadata']['Item'];
                $mpesa_receipt = '';
                $phone_number = '';
                
                foreach ($metadata as $item) {
                    if ($item['Name'] === 'MpesaReceiptNumber') {
                        $mpesa_receipt = $item['Value'];
                    }
                    if ($item['Name'] === 'PhoneNumber') {
                        $phone_number = $item['Value'];
                    }
                }
                
                // Update with M-Pesa receipt number
                if ($mpesa_receipt) {
                    $stmt = $pdo->prepare("UPDATE purchases SET mpesa_receipt = ? WHERE transaction_id = ?");
                    $stmt->execute([$mpesa_receipt, $checkout_request_id]);
                }
            }
        } else {
            // Payment failed
            $stmt = $pdo->prepare("UPDATE purchases SET status = 'failed' WHERE transaction_id = ?");
            $stmt->execute([$checkout_request_id]);
        }
    }
}

// Respond to Safaricom
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
?>