<?php
require_once 'shared.php';

// This is the root of the URL and includes the scheme. It usually looks like
// `http://localhost:4242`. This is used when constructing the fully qualified
// URL where the user will be redirected to after going through the payment
// flow.
$domain_url = $_ENV['DOMAIN'];

if( isset($_POST) )
{
     $OrderId = (int)$_POST['OrderId'];
     $Amount = (int)$_POST['Amount1'] + (int)$_POST['Amount2'] * 100;
     $Description = $_POST['Description'];
}


//$clint

$price = $stripe->prices->create([
  'currency' => 'eur',
  'unit_amount' => $Amount,
  'product_data' => ['name' => 'Payment for client number ' . $Description ],
]);

$price_id = $price->id;

// Create new Checkout Session for the order
// ?session_id={CHECKOUT_SESSION_ID} means the redirect will have the session ID set as a query param
$checkout_session = $stripe->checkout->sessions->create([
  'success_url' => $domain_url . '/success.php?session_id={CHECKOUT_SESSION_ID}',
  'cancel_url' => $domain_url . '/canceled.html',
  'mode' => 'payment',
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
    //'description' => 'Описание заказа',
    'custom_fields' => [
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
        /*[
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
	]*/
    ],
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);
