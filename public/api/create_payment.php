<?php
$requestBody = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($requestBody['amount1']) || !isset($requestBody['amount2']) || !isset($requestBody['description'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

if (isset($requestBody['source'])) {
    $Source = $requestBody['source'];
} else {
    $Source = 'api';
}
$Amount1 = (int) $requestBody['amount1'];
$Amount2 = (int) $requestBody['amount2'];
$Description = $requestBody['description'];
$Description = filter_var($Description, FILTER_SANITIZE_STRING);
//    echo json_encode(['error' => 'Invalid requese', 'Amount1' => $Amount1, 'Amount2' => $Amount2]);


require_once '../../_payment.php';
$payment = new Payment($stripe);
$result = $payment->createPayment($Amount1, $Amount2, $Description, $Source);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Payment creation failed']);
    exit;
}

if (array_key_exists('error', $result)) {
    http_response_code(400);
    echo json_encode($result);
    exit;
}

//$orderId = $result['orderId'];
//$redirurl = $result['redirurl'];
echo json_encode($result);

?>
