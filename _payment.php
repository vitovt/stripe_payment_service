<?php

require_once '_shared.php'; // Assumes shared.php contains necessary Stripe initialization
require_once '_database.php'; // Database connection and operations

class Payment {
    private $stripe;
    private $db;

    public function __construct($stripe) {
        $this->stripe = $stripe;
        $this->db = new Database();
    }

    public function createPayment($Amount1, $Amount2, $Description) {
        $domain_url = $_ENV['DOMAIN'];

        if ($this->isValidAmount($Amount1, $Amount2)) {
            $Amount = (int)$Amount2 * 100 + (int)$Amount1;
        } else {
            return [
                'error' => 'WRONG NUMBER of Euro!',
            ];
        }

        $products = $this->stripe->products->all(['limit' => 3]);
        $price = $this->stripe->prices->create([
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

        $orderId = $this->db->addRecord();
        $price_id = $price->id;
        $checkout_session = $this->stripe->checkout->sessions->create([
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
                'description' => $orderId,
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

        $this->db->addToken($checkout_session->id, $orderId);
        $this->db->updateRecordStatus($checkout_session->payment_status, $orderId);
        $this->db->updateRecordDescription($Description, $orderId);
        
        $redirurl =  $checkout_session->url;
        $transaction_id = $checkout_session->id;
        $payment_status = $checkout_session->payment_status;
        $expireddatetime = $checkout_session->expires_at;
        $expired = date("Y-m-d H:i:s", substr($expireddatetime, 0, 10));

        return [
            'orderId' => $orderId,
            'redirurl' => $redirurl,
            'linksExpires' => $expired,
        ];
    }

    public function checkPayment($orderId) {
        $message = '';
        $statusCode = 'null';
        $description = '';

        if ($orderId > 0 && $orderId < 12799999) {
            $message = "<p>Номер заказа: <strong>$orderId</strong></p>";
            $sessionId = $this->db->getToken($orderId);

            if ($sessionId) {
                $checkout_session = $this->stripe->checkout->sessions->retrieve($sessionId);
                $payment_status = $checkout_session->payment_status;
                $statusstyle = $this->getStatusStyle($payment_status);
                $message .= "<h2 class=\"success\">Статус заказа: <span $statusstyle>$payment_status</span></h2>";
                $statusCode = $payment_status;
                $description = $this->db->getRecordDescription($orderId);
            } else {
                $message .= "<h2 class=\"warning\">Номер заказа не существует</h2>";
                $statusCode = 'order-not-found';
            }
        } else {
            $message = "<p>Номер заказа: <strong>Не определен</strong></p>";
            $message .= "<h2 class=\"error\">Введите корректный номер заказа</h2>";
            $statusCode = 'invalid-order-number';
        }

        return [
            'message' => $message,
            'statusCode' => $statusCode,
            'description' => $description,
        ];
    }

    private function isValidAmount($Amount1, $Amount2) {
        return is_numeric($Amount2) && (int)$Amount2 >= 0 &&
               is_numeric($Amount1) && (int)$Amount1 < 100 && (int)$Amount1 >= 0;
    }

    private function getStatusStyle($payment_status) {
        switch ($payment_status) {
            case 'paid':
                return 'class="success"';
            case 'unpaid':
                return 'class="error"';
            default:
                return 'class="unknown"';
        }
    }
}

?>

