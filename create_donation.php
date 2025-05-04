<?php
header('Content-Type: application/json');

require 'paypal_config.php'; // Store credentials securely

$data = json_decode(file_get_contents('php://input'));
$amount = floatval($data->amount);

// Validate donation
if ($amount < 1 || $amount > 10000) {
  http_response_code(400);
  die(json_encode(['error' => 'Invalid donation amount']));
}

// Create PayPal order
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api-m.paypal.com/v2/checkout/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  'intent' => 'CAPTURE',
  'purchase_units' => [[
    'amount' => [
      'currency_code' => 'USD',
      'value' => $amount
    ],
    'description' => 'Charity Donation: ' . $data->designation
  ]]
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Content-Type: application/json',
  'Authorization: Bearer ' . getPayPalAccessToken()
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;

function getPayPalAccessToken() {
  // Implement token caching for better performance
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://api-m.paypal.com/v1/oauth2/token');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
  curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ':' . PAYPAL_SECRET);
  
  $response = json_decode(curl_exec($ch));
  curl_close($ch);
  
  return $response->access_token;
}