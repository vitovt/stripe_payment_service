<?php
$requestBody = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($requestBody['orderId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

$orderId = (int) $requestBody['orderId'];

require_once '../../_payment.php';
$payment = new Payment($stripe);
$result = $payment->checkPayment($orderId);
$statusCode = $result['statusCode'];

if($statusCode == 'order-not-found') {
	http_response_code(404);
}

if($statusCode == 'invalid-order-number') {
    http_response_code(400);
}

echo json_encode($result);
?>
