<?php
require_once("config/razorpay.php");
require_once __DIR__ . "/vendor/autoload.php";

use Razorpay\Api\Api;

try {
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    $order = $api->order->create([
        'receipt' => 'test_' . time(),
        'amount' => 10000,
        'currency' => 'INR',
        'payment_capture' => 1
    ]);

    echo "SUCCESS ✅";
    echo "<pre>";
    print_r($order->toArray());
    echo "</pre>";

} catch (Exception $e) {
    echo "ERROR ❌: " . $e->getMessage();
}