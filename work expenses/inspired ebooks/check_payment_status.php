<?php
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$checkout_request_id = $input['checkout_request_id'] ?? '';

if (empty($checkout_request_id)) {
    echo json_encode(['error' => 'Missing checkout request ID']);
    exit();
}

// Check purchase status in database
$stmt = $pdo->prepare("SELECT status FROM purchases WHERE transaction_id = ?");
$stmt->execute([$checkout_request_id]);
$purchase = $stmt->fetch();

if ($purchase) {
    echo json_encode(['status' => $purchase['status']]);
} else {
    echo json_encode(['status' => 'not_found']);
}
?>