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

//supply information for enquiry
$webcash->reference = '599bfb5e60c29';
$webcash->amount = 50;

$enquiry = $webcash->enquiry();

$msg = '';
switch ($enquiry) {
	case 'success':
		//update database record - your logic
		$msg = 'Transaction Successful';
		break;
	case 'fail':
		$msg = 'Transaction Failed';
		break;
	case 'abort':
		$msg = 'Transaction Aborted';
		break;
	case 'invalid':
	case 'not_found':
		$msg = 'Invalid parameters - Please check your amount and reference';
		break;
}

echo $msg;
