<?php
namespace PaysonAB\PaysonCheckout2\Model\Api;

use PaysonAB\PaysonCheckout2\Model\Api\PaysonApiException;

class PaysonApi
{
    private $merchantId;
    private $apiKey;
    private $protocol = "https://%s";
    const ACTION_CHECKOUTS = "Checkouts/";
    const ACTION_ACCOUNTS = "Accounts/";
    private $allOrderData = array();
    private $useTestEnvironment;
    protected $checkout;
    protected $paysonConfig;
    protected $_account;
    public $paysonResponseErrors = array();
    protected $_curl;
    protected $sslVersion;
    protected $_body;
    protected $_headerSize;
    protected $_responseCode;
    
    public function __construct(
        \PaysonAB\PaysonCheckout2\Model\Api\Checkout $checkout,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig,
        \PaysonAB\PaysonCheckout2\Model\Api\Account $account
    ) {
        $this->checkout = $checkout;
        $this->paysonConfig = $paysonConfig;
        $this->_account = $account;
        $this->merchantId = $this->paysonConfig->validateMerchantId();
        $this->apiKey = $this->paysonConfig->validateApiKey();
    }

    public function init($merchantId, $apiKey)
    {
        $this->useTestEnvironment = $this->getTestMode();
        $this->merchantId =$merchantId;
        $this->apiKey = $apiKey;
        
        if (!function_exists('curl_exec')) {
            throw new PaysonApiException('Curl not installed. Is required for PaysonApi.');
        }
        return $this;
    }

    public function getCheckoutObj()
    {
        return $this->checkout;
    }
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function CreateCheckout()
    {
        $checkout = $this->getCheckoutObj();
        $result = $this->doCurlRequest('POST', $this->getUrl(self::ACTION_CHECKOUTS), $checkout->toArray());

        $checkoutId = $this->extractCheckoutId($result);

        if (!$checkoutId) {
            throw new PaysonApiException("Checkout Id not received of unclear reason");
        }
        return $checkoutId;
    }
    
    public function UpdateCheckout($checkout)
    {
        if (!$checkout->id) {
            throw new PaysonApiException("Checkout object which should be updated must have id property set");
        }
        $result = $this->doCurlRequest('PUT', $this->getUrl(self::ACTION_CHECKOUTS).$checkout->id, $checkout->toArray());
        return $checkout;
    }
    
    public function GetCheckout($checkoutId)
    {
        $result = $this->doCurlRequest('GET', $this->getUrl(self::ACTION_CHECKOUTS).$checkoutId, null);
        return Checkout::create(json_decode($result));
    }
    
    public function ShipCheckout(Checkout $checkout)
    {
        $checkout->status = 'shipped';
        return $this->UpdateCheckout($checkout);
    }
    
    public function CancelCheckout(Checkout $checkout)
    {
        $checkout->status = 'canceled';
        return $this->UpdateCheckout($checkout);
    }
    
    public function Validate()
    {
        $result = $this->doCurlRequest('GET', $this->getUrl(self::ACTION_ACCOUNTS), null);
        $result = json_decode($result);
        return $this->_account->create($result);
    }

    private function doCurlRequest($method, $url, $postfields)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->authorizationHeader());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields?json_encode($postfields):null);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $result = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($result, $header_size);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response_code == 200) {
            return $body;
        } elseif ($response_code == 201) {
            return $result;
        } elseif ($result == false) {
            //print_r($postfields); die;
            throw new PaysonApiException('Curl error: '.curl_error($ch));
        } else {
            $errors = array();

            $data = json_decode($body, true);
            $errors[] = new PaysonApiError('HTTP status code: ' . $response_code.', '.$data['message'], null);

            if (isset($data['errors']) && count($data['errors'])) {
                $errors = array_merge($errors, $this->parseErrors($data['errors'], $response_code));
            }

            throw new PaysonApiException("Api errors", $errors);
        }
        curl_close($ch);
    }

    private function authorizationHeader()
    {
        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Authorization: Basic ' . base64_encode($this->merchantId . ':' . $this->apiKey);
        return $header;
    }


    private function extractCheckoutId($result)
    {
        $checkoutId = null;
        if (preg_match('#Location: (.*)#', $result, $res)) {
            $checkoutId = trim($res[1]);
        }
        $checkoutId = explode('/', $checkoutId);
        $checkoutId = $checkoutId[count($checkoutId) - 1];
        return $checkoutId;
    }

    private function parseErrors($responseErrors, $response_code)
    {
        $errors = array();
        foreach ($responseErrors as $error) {
            $errors[] = new PaysonApiError($error['message'], (isset($error['property'])?$error['property']:null));
        }
        return $errors;
    }

    public function setStatus($status)
    {
        $this->allOrderData['status'] = $status;
    }
    
    private function getUrl($action)
    {
        return (sprintf($this->protocol, ($this->getTestMode() ? 'test-' : '')) . 'api.payson.se/2.0/'.$action);
    }
    
    public function getTestMode()
    {
        return $this->useTestEnvironment = $this->paysonConfig->isTestmode() ? true : false;
    }
    
}
