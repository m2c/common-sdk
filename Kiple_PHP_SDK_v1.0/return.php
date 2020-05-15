<?php
/**
 * Copyright 2016 Webonline Dot Com Sdn. Bhd.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Kiple.
 *
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Kiple' . DIRECTORY_SEPARATOR . 'autoload.php';

use Kiple\PaymentGateway;

//your merchant information
$merchantID = 80000321;
$merchantSecret = "AIEMX(@pZ)#CP@IW";
$testMode = true;

$webcash = new PaymentGateway($merchantID, $merchantSecret, $testMode);

$response = $webcash->response();

//order successful
if ($response['success'] === true) {
    //update database record - your logic
    echo 'Transaction successful';
} else {
    switch ($response['code']) {
        case 'fail':
            echo 'Transaction failed';
            break;
        case 'abort':
            echo 'Transaction aborted by user';
            break;
    }
}

echo '<pre>';
echo 'Transaction Datetime: ' . $webcash->datetime . "\n";
echo 'Transaction Amount: ' . $webcash->amount . "\n";

if (!empty($webcash->gst)) {
    echo 'Transaction GST: ' . $webcash->gst . "\n";
}

if (!empty($webcash->serviceCharges)) {
    echo 'Service Charges: ' . $webcash->serviceCharges . "\n";
}

if (!empty($webcash->deliveryCharges)) {
    echo 'Delivery Charges: ' . $webcash->deliveryCharges . "\n";
}

echo 'Your Name: ' . $webcash->buyerName . "\n";
echo 'Your Telephone: ' . $webcash->buyerTelephone . "\n";
echo 'Your Email: ' . $webcash->buyerEmail . "\n";
echo 'Transaction Reference: ' . $webcash->reference . "\n";
echo 'Transaction Status: ' . $webcash->status . "\n";
echo 'Kiple Reference: ' . $webcash->kipleReference . "\n";
echo '</pre>';
