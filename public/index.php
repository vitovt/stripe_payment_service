<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Checkout Page</title>

    <link rel="stylesheet" href="css/view.css" />
    <link rel="stylesheet" href="css/normalize.css" />
    <link rel="stylesheet" href="css/global.css" />
  </head>

  <body>
    <div class="sr-root">
      <div class="sr-main">
        <section class="container">
          <div>
                        <h1>Форма оплаты</h1>
                        <p>Введите данные для оплаты и нажмите "Оплатить"</p>
          </div>

                <form id="form_10984" class="appnitro"  method="POST" action="create-checkout-session.php">
                                        <div class="form_description">
                                                <img src="css/logo.png">
                </div>
                        <ul >

                <li id="li_1" >
                <label class="description" for="element_1">Сумма </label>
                <span class="symbol">&#8364;</span>
                <span>
                        <input id="element_1_1" name="Amount2" class="element text currency" size="10" value="" type="number" min="0" step="1" required />
                        <label for="element_1_1">Euros</label>
                </span>
                <span>
                        <input id="element_1_2" name="Amount1" class="element text currency" size="3" value="" type="number" min="0" max="99" step="1" required />
                        <label for="element_1_2">Cents</label>
                </span>
                </li>
                <li id="li_2" ><p>Customer/Клиент/Клиенттік/Müştəri/ Nr</p>
			<label class="description" for="element_3">Номер Диллера = Номер Клиента<br/> (например V014 = 000017)</label>
                <div>
                        <input id="element_3" name="Description" class="element text medium" type="text" maxlength="255" placeholder="V014 = 000017" value="" required />
                </div>
                </li>

                                        <li class="buttons">
                            <input type="hidden" name="form_id" value="10984" />
                                <input id="submit" class="button" type="submit" name="submit" value="Оплатить" />
                </li>
                        </ul>
                </form>
		<div id="footer">&copy; by <a href="">2EGO</a> 2018 - <?php echo date('Y'); ?></div>
        </section>
      </div>
    </div>
  </body>
</html>
