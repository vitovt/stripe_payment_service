<?php
require_once 'shared.php';
// Retrieve the Checkout Session for the successful payment flow that just
// completed. This will be displayed in a `pre` tag as json in this file.
$checkout_session = $stripe->checkout->sessions->retrieve(
$_GET['session_id']
);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Stripe Checkout Sample</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="css/normalize.css" />
    <link rel="stylesheet" href="css/global.css" />
  </head>

  <body>
    <div class="sr-root">
      <div class="sr-main">
        <div class="sr-payment-summary completed-view">
          <h1>Your payment succeeded</h1>
          <h4>
            View CheckoutSession response:</a>
          </h4>
        </div>
        <div class="sr-section completed-view">
          <div class="sr-callout">
            <!--pre><?= json_encode($checkout_session, JSON_PRETTY_PRINT); ?></pre-->
          </div>
          <button type="submit" class="button" onclick="window.location.href = 'index.html';">Restart</button>
        </div>
      </div>
      <div class="sr-content">
        <div class="pasha-image-stack">
         <img src="css/logo.png">
        </div>
      </div>
    </div>
  </body>
</html>
