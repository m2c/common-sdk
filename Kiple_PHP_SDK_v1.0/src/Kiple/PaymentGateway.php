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

namespace Kiple;

class PaymentGateway
{
    const VERSION = '1.0.0';
    const PROD_ENTRY_URL = 'https://kiplepay.com/gateway';
    const PROD_ENQUIRY_URL = 'https://kiplepay.com/enquiries';
    const DEV_ENTRY_URL = 'https://uat.kiplepay.com/gateway';
    const DEV_ENQUIRY_URL = 'https://uat.kiplepay.com/enquiries';
    const DEFAULT_REQUEST_TIMEOUT = 60;


    private $parameters = [
        'ord_date' => 'datetime',
        'ord_totalamt' => 'amount',
        'ord_gstamt' => 'gst',
        'ord_delcharges' => 'deliveryCharges',
        'ord_svccharges' => 'serviceCharges',
        'ord_shipname' => 'buyerName',
        'ord_mercref' => 'reference',
        'ord_telephone' => 'buyerTelephone',
        'ord_email' => 'buyerEmail',
        'ord_mercID' => 'merchantId',
        'ord_returnURL' => 'returnUrl',
        'ord_key' => 'orderKey',
        'returncode' => 'status',
        'wcID' => 'kipleReference'
    ];

    private $merchantId;
    private $merchantSecret;
    private $gatewayUrl;

    public function __construct($merchantId, $merchantSecret, $testMode = false)
    {
        if (!$testMode) {
            $this->gatewayUrl = self::PROD_ENTRY_URL;
            $this->enquiryUrl = self::PROD_ENQUIRY_URL;
        } else {
            $this->gatewayUrl = self::DEV_ENTRY_URL;
            $this->enquiryUrl = self::DEV_ENQUIRY_URL;
        }

        if (empty($merchantId) || empty($merchantSecret)) {
            throw new Exception("Enter Merchant ID and Merchant Secret provided by Kiple");
        }

        $this->merchantId = $merchantId;
        $this->merchantSecret = $merchantSecret;
    }

    public function connect()
    {
        $this->checkFields($this);
        return $this->generateHtml();
    }

    public function response()
    {
        $request = $_REQUEST;

        if ($request['returncode'] == '-1') {
            return ['success' => false, 'code' => 'invalid_request', 'message' => 'Invalid request from merchant'];
        }

        $verifyKey = $this->validateIncomingHashKey($request['ord_mercref'], $request['ord_totalamt'], $request['returncode']);

        if ($request['ord_key'] != $verifyKey) {
            return ['success' => false, 'code' => 'invalid_hash', 'message' => 'Invalid hash key'];
        }

        switch ($request['returncode']) {
            case '100':
                $success = true;
                $code = 'success';
                $message = 'Successful Transaction';
                break;
            case 'E1':
                $success = false;
                $code = 'fail';
                $message = 'Failed Transaction';
                break;
            case 'E2':
                $success = false;
                $code = 'abort';
                $message = 'Aborted Transaction';
                break;
            default:
                $success = false;
                $code = 'error';
                $message = 'Error';
                break;
        }

        $formatted = [];
        foreach ($request as $key => $value) {
            $this->{$this->parameters[$key]} = $value;
        }

        return ['success' => $success, 'code' => $code, 'message' => $message];
    }

    public function enquiry()
    {
        $result = file_get_contents($this->enquiryUrl . '?ord_mercID=' . $this->merchantId . '&ord_mercref=' . $this->reference . '&ord_totalamt=' . $this->amount);

        switch ($result) {
            case 'S':
                $result = 'success';
                break;
            case 'F':
                $result = 'fail';
                break;
            case 'A':
                $result = 'abort';
                break;
            case 'Invalid Parameters':
                $result = 'invalid';
                break;
            case 'Records not Found':
                $result = 'not_found';
                break;
            default:
                $result = 'error';
                break;
        }

        return $result;
    }

    private function generateHashKey()
    {
        return sha1($this->merchantSecret . $this->merchantId . $this->reference . str_replace(['.', '|'], '', $this->amount));
    }

    private function validateIncomingHashKey($referenceNo, $amount, $status)
    {
        return sha1($this->merchantSecret . $this->merchantId . $referenceNo . str_replace(['.', '|'], '', $amount) . $status);
    }

    private function generateHtml()
    {
        $html = '<html>';
        $html .= '<head>';
        $html .= '<script type="text/javascript">';
        $html .= '	function fnSubmit() {';
        $html .= '	    window.document.gateway.submit();';
        $html .= '	    return;';
        $html .= '	}';
        $html .= '</script>';
        $html .= '</head>';
        $html .= '<body onload="return fnSubmit()">';
        $html .= '	<form name="gateway" id="gateway" action="' . $this->gatewayUrl . '" method="post">';

        foreach ($this->parameters as $field => $parameter) {
            if (!empty($this->{$parameter})) {
                $html .= '	  <input type="hidden" name="' . $field . '" value="' . $this->{$parameter} . '" />';
            }
        }

        $html .= '	  <input type="hidden" name="merchant_hashvalue" value="' . $this->generateHashKey() . '" />';
        $html .= '	</form>';
        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }

    private function checkFields($data)
    {
        $error = [];
        foreach ($data as $key => $value) {
            $validateMsg = $this->validate($key, $value);
            if (!empty($validateMsg)) {
                $error[] = $validateMsg;
            }
        }

        if (!empty($error)) {
            $count = count($error);
            $label = 'error' . (($count > 1) ? 's' : '');
            $msgs = implode(', ', $error);

            throw new Exception("{$count} {$label} occured: {$msgs}");
        }
    }

    private function validate($key, $value)
    {
        $error = '';
        switch ($key) {
            case 'date':
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                $valid = $date && $date->format('Y-m-d H:i:s') === $date;

                if (!$valid) {
                    $error = 'Invalid date format - YYYY-MM-DD HH:MM:SS format';
                }
                break;
            case 'amount':
            case 'gst':
            case 'deliveryCharges':
            case 'serviceCharges':
                $valid = is_numeric($value);
                if (!$valid) {
                    $error = 'Invalid amount format - should be in decimal or integer';
                }
                break;
            case 'buyerName':
                $valid = !empty($value);
                if (!$valid) {
                    $error = 'Invalid buyer name - buyer name is mandatory';
                }
                break;
            case 'reference':
                $valid = !empty($value);
                if (!$valid) {
                    $error = 'Invalid reference number';
                }
                break;
            case 'returnUrl':
                $valid = filter_var($value, FILTER_VALIDATE_URL);
                if (!$valid) {
                    $error = 'Invalid return url - example: http://example.com/receive';
                }
                break;
        }

        return $error;
    }

    //from http://hayageek.com/php-get-current-url/
    public function createUrl($path = '')
    {
        $currentURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
        $currentURL .= $_SERVER["SERVER_NAME"];

        if ($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443") {
            $currentURL .= ":".$_SERVER["SERVER_PORT"];
        }

        $currentURL .= $_SERVER["REQUEST_URI"];

        $url = explode('/', $currentURL);

        return str_replace($url[count($url)-1], '', $currentURL) . $path;
    }
}
