<?php
require_once '../_payment.php';

if (!empty($_POST)) {
    // Sanitize and validate Amount2 and Amount1
    $Amount2 = filter_input(INPUT_POST, 'Amount2', FILTER_SANITIZE_STRING);
    $Amount1 = filter_input(INPUT_POST, 'Amount1', FILTER_SANITIZE_STRING);

    // Sanitize Description
    $Description = filter_input(INPUT_POST, 'Description', FILTER_SANITIZE_STRING);

    // Now, $Description is sanitized and can be safely used for further processing.
    // Make sure to use prepared statements when inserting it into the database later.

} else {
    // Handle the case where $_POST is not set
    die("Direct access is forbidden!");
}

    $payment = new Payment($stripe);
    $result = $payment->createPayment($Amount1, $Amount2, $Description);

    if (array_key_exists('error', $result)) {
        die($result['error'] . ' <a href="javascript:history.back()">Go back</a>');
    }

    $orderId = $result['orderId'];
    $redirurl = $result['redirurl'];
    $domain_url = $_ENV['DOMAIN']; 




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
