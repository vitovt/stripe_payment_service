<?php
require_once '../_shared.php';
require_once '../_database.php';

$db = new Database();

$message = '';
$statusCode = 'null';
if (!empty($_POST)) {
    $orderId = filter_input(INPUT_POST, 'orderid', FILTER_SANITIZE_NUMBER_INT);

        if($orderId > 0 && $orderId < 12799999) {
            $message = "<p>Номер заказа: <strong>$orderId</strong></p>";
            $sessionId = $db->getToken($orderId);
            if($sessionId) {
                $checkout_session = $stripe->checkout->sessions->retrieve($sessionId);
                $payment_status = $checkout_session->payment_status;
                if($payment_status == 'paid') {
                    $statusstyle='class="success"';
                } elseif($payment_status == 'unpaid') {
                    $statusstyle='class="error"';
                } else {
                    $statusstyle='class="unknown"';
                }
                $message .= "<h2 class=\"success\">Статус заказа: <span $statusstyle>$payment_status</span></h2>"; 
                $statusCode = $payment_status;
            } else {
                $message .= "<h2 class=\"warning\">Номер заказа не существует</h2>";
                $statusCode = 'order-not-found';
            }
        } else {
            $message = "<p>Номер заказа: <strong>Не определен</strong></p>";
	    $message .= "<h2 class=\"error\">Введите корректный номер заказа</h2>";
            $statusCode = 'invalid-order-number';
	}
    $message .= "<p><span class=\"status\">Status: <span class=\"statuscode\">$statusCode</span></p><span>";
}
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
	  <h1>Проверка статуса оплаты</h1>
          <?php
          if ($message) {
	    echo $message;
	  }
          ?>
          </div>
                <form id="form_check" class="appnitro"  method="POST" action="assert.php">
                 <div class="form_description">
                     <img src="css/logo.png">
		</div>
	       <p>Введите номер заказа и нажмите "Проверить"</p>
                <ul>
               <li> 
                  <input id="orderid" name="orderid" class="element text currency" size="10" value="" type="number" min="12700000" max="12799999" placeholder="12700001" required />
               </li> 
                <li class="buttons">
                   <input id="submit" class="button" type="submit"  name="submit" value="Проверить">
                </li>
                        </ul>
                </form>

		<div id="footer">&copy; by <a href="">2EGO</a> 2018 - <?php echo date('Y'); ?></div>
        </section>
      </div>

      </div>
    </div>
  </body>
</html>
