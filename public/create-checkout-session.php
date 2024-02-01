<?php
require_once 'shared.php';
require_once '../_database.php';

// This is the root of the URL and includes the scheme. It usually looks like
// `http://localhost:4242`. This is used when constructing the fully qualified
// URL where the user will be redirected to after going through the payment
// flow.
$domain_url = $_ENV['DOMAIN'];
$db = new Database();
$orderId = $db->addRecord();

if (!empty($_POST)) {
    // Sanitize and validate Amount1 and Amount2
    $Amount1 = filter_input(INPUT_POST, 'Amount1', FILTER_SANITIZE_NUMBER_INT);
    $Amount2 = filter_input(INPUT_POST, 'Amount2', FILTER_SANITIZE_NUMBER_INT);

    // Validate if Amount1 and Amount2 are integers
    if (filter_var($Amount1, FILTER_VALIDATE_INT) !== false && filter_var($Amount2, FILTER_VALIDATE_INT) !== false) {
        // Calculate the total amount in cents
        $Amount = (int)$Amount1 + (int)$Amount2 * 100;
    } else {
        // Handle the error if Amount1 or Amount2 are not valid integers
        die('Invalid input for Amount1 or Amount2');
    }

    // Sanitize Description
    $Description = filter_input(INPUT_POST, 'Description', FILTER_SANITIZE_STRING);

    // Now, $Description is sanitized and can be safely used for further processing.
    // Make sure to use prepared statements when inserting it into the database later.

} else {
    // Handle the case where $_POST is not set
    die("Direct access is forbidden!");
}


$products = $stripe->products->all(['limit' => 3]);

//create custom price
$price = $stripe->prices->create([
  'currency' => 'eur',
  'unit_amount' => $Amount,
  //Which product to use: Select ONE option:

  //1) Create product every time
  //'product_data' => ['name' => 'Customer payment' ],

  //2) Use predefined product
  'product' =>  $_ENV['PRODUCT'],

  //3) Get last created product
  //'product' => $products->data[0]->id,
]);

$price_id = $price->id;

// Create new Checkout Session for the order
// ?session_id={CHECKOUT_SESSION_ID} means the redirect will have the session ID set as a query param
$checkout_session = $stripe->checkout->sessions->create([
  'success_url' => $domain_url . '/success.php?session_id={CHECKOUT_SESSION_ID}',
  'cancel_url' => $domain_url . '/canceled.html',
  'mode' => 'payment',
  /*'client_reference_id' => 'VR007-test',
  'metadata' => [
     'naznachenie' => 'VQ008-test'
  ],*/
  // 'automatic_tax' => ['enabled' => true],
  'line_items' => [[
    'price' => $price_id,
    'quantity' => 1,
  ]],
 /*   'custom_text' => [
        'submit' => [
            'message' => 'Пополнение баланса диллера: ' . $Description,
            // Custom message alongside the payment confirmation button
        ],
        'after_submit' => [
            'message' => 'Спасибо за оплату!'
            // Add your custom message here, up to 1200 characters
        ],
 ],*/
    'payment_intent_data' => [
        'description' => $Description,
    ],
    /*'custom_fields' => [
        [
            'key' => 'customer_num',
            'label' => [
                'custom' => 'Customer/Клиент/Клиенттік/Müştəri/ Nr',
                'type' => 'custom'
            ],
            'type' => 'text',
            'text' => [
                'maximum_length' => 21, // Set your desired maximum length
                'minimum_length' => 1   // Set your desired minimum length
            ],
            'optional' => false // Set to true if the field is not mandatory
        ],
        [
            'key' => 'client_id',
            'label' => [
                'custom' => 'Client ID',
                'type' => 'custom'
            ],
            'type' => 'text',
            'text' => [
                'maximum_length' => 20, // Set your desired maximum length
                'minimum_length' => 1   // Set your desired minimum length
            ],
            'optional' => false // Set to true if the field is not mandatory
	]
    ],*/
]);

$redirurl =  $checkout_session->url;
$transaction_id = $checkout_session->id;
$payment_status = $checkout_session->payment_status;
$expireddatetime = $checkout_session->expires_at;
$expired = date("Y-m-d H:i:s", substr($expireddatetime, 0, 10));


$db->addToken($transaction_id, $orderId);
$db->updateRecord($payment_status, $orderId);

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Confirm payment</title>

    <link rel="stylesheet" href="css/view.css" />
    <link rel="stylesheet" href="css/normalize.css" />
    <link rel="stylesheet" href="css/global.css" />
  </head>
  <body>
    <div class="sr-root">
      <div class="sr-main">
        <section class="container">
          <div>
                        <h1>Подтверждение оплаты</h1>
                        <p>Проверьте данные для оплаты и нажмите "Оплатить сейчас"</p>
          </div>

                                        <div class="form_description">
                                                <img src="css/logo.png">
                </div>
<form>
<?php
if ($redirurl) {
        echo '<ul>';
        echo '<li>Номер заказа: <strong>' . $orderId . '</strong></li>';
        echo '<li><a href="' . $redirurl  . '">Ссылка для оплаты</a> (можно передавать клиенту): </li>';
        echo '<li><input id="urlField" value="' . $redirurl . '" readonly>';
        echo '<a class="smallbutton" onclick="copyToClipboard()">Copy</a></li>';
        echo "<li>Время действия ссылки: <strong> 24 часа </strong></li>";
        echo '<input id="submit" class="button" type="submit" name="submit" value="Оплатить сейчас" />';
        echo "<br>\n<br>\n";
        echo "Код для отслеживания оплаты (не передавать): <strong>" .  $orderId . "</strong><br>";
        echo "<li>Отследить оплату можно по ссылке: <a href='$domain_url/assert.php'>$domain_url/assert.php</a></li>";
        echo '</ul>';
} else {
        echo "<p>Непредвиденная ошибка! Напишите в поддержку</p>";
}

?>
</form>
                <div id="footer">&copy; by <a href="http://pay2.2ego.de">2EGO</a> 2018 - <?php echo date('Y');?></div>
        </section>
      </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var redirectUrl = '<?= $redirurl; ?>';
        var submitButton = document.getElementById('submit');

        submitButton.addEventListener('click', function(event) {
            event.preventDefault();
            window.location.href = redirectUrl;
        });
    });

function copyToClipboard() {
    var copyText = document.getElementById("urlField");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand("copy");

    var backdrop = document.createElement("div");
    backdrop.className = "backdrop";
    document.body.appendChild(backdrop);
    setTimeout(() => backdrop.style.opacity = '1', 10);

    var notification = document.createElement("div");
    notification.className = "notification";

    var preloader = document.createElement("div");
    preloader.className = "preloader";
    notification.appendChild(preloader);

    var text = document.createElement("div");
    text.textContent = "Ссылка скопирована";
    notification.appendChild(text);

    document.body.appendChild(notification);

    // Start preloader animation
    setTimeout(() => preloader.style.width = '0', 10);

    // Fade out and remove notification and backdrop
    setTimeout(function() {
        notification.style.opacity = '0';
        backdrop.style.opacity = '0';
        setTimeout(function() {
            document.body.removeChild(notification);
            document.body.removeChild(backdrop);
        }, 500); // 500ms for fade out transition
    }, 3000); // 3 seconds till close
}
</script>

</body>
</html>
