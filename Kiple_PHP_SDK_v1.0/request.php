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

//set the parameters
$webcash->datetime = date('Y-m-d H:i:s');
$webcash->amount = 50; //order amount
$webcash->gst = 0; //gst - only works if you registered as GST merchant with Webcash
$webcash->serviceCharges = 0; //service charges - reference
$webcash->deliveryCharges = 5; //delivery charges - reference
$webcash->buyerName = 'Suresh Muniandy'; //user full name
$webcash->buyerTelephone = '0162702298'; //user telephone number
$webcash->buyerEmail = 'suresh@webonline.com.my'; //user email
$webcash->reference =  uniqid(); //your reference

$webcash->returnUrl = $webcash->createUrl('return.php'); //point to return url

//connecto payment gateway
echo $webcash->connect();
