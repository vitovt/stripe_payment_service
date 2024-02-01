<?php
if (isset($_GET['session_id'])) {
    $sessionId = $_GET['session_id'];

    // Check if the session_id matches the criteria: only Latin letters, numbers, and underscores
    // and check the length to be no longer than 96 characters
    if (preg_match('/^[a-zA-Z0-9_]+$/', $sessionId) && strlen($sessionId) <= 96) {
        // $sessionId is valid
        //echo "Session ID is valid.";
    } else {
        // Invalid $sessionId
        die("Invalid Session ID.");
    }
} else {
    // $_GET['session_id'] is not set
    die("Direct access is not allowed.");
}

require_once 'shared.php';
require_once '../_database.php';

$db = new Database();
$orderId = $db->getId($sessionId);

if(is_null($orderId)) {
    //no session in database
    die('Error: Unknown session!');
}

// Retrieve the Checkout Session for the successful payment flow that just
// completed. This will be displayed in a `pre` tag as json in this file.
$checkout_session = $stripe->checkout->sessions->retrieve($sessionId);
$payment_status = $checkout_session->payment_status;
//$expireddatetime = $checkout_session->expires_at;
//$expired = date("Y-m-d H:i:s", substr($expireddatetime, 0, 10));
$db->updateRecord($payment_status, $orderId);

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Stripe Checkout</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="css/view.css" />
    <link rel="stylesheet" href="css/normalize.css" />
    <link rel="stylesheet" href="css/global.css" />
  </head>

  <body>
    <div class="sr-root">
      <div class="sr-main">
        <section class="container">
          <div>
          <h1>Your payment succeeded</h1>
          <h2 class="success">Оплата успешна</h2>
          </div>

                                        <div class="form_description">
                                                <img src="css/logo.png">
          <button type="submit" class="button" onclick="window.location.href = 'index.php';">Restart</button>
                </div>
		<div id="footer">&copy; by <a href="">2EGO</a> 2018 - <?php echo date('Y'); ?></div>
        </section>
      </div>

      </div>
    </div>
  </body>
</html>
