<?php
namespace PaysonAB\PaysonCheckout2\Model\Api;

class Account
{
    /**
 * @var string $accountEmail 
*/
    public $accountEmail;
    /**
 * @var string $status 
*/
    public $status;
    /**
 * @var int $merchantId 
*/
    public $merchantId;
    /**
 * @var string $enabledForInvoice 
*/
    public $enabledForInvoice;
    /**
 * @var string $enabledForPaymentPlan 
*/
    public $enabledForPaymentPlan;
    

    public function accountInit($accountEmail, $status, $merchantId, $enabledForInvoice, $enabledForpaymentPlan)
    {
        $this->accountEmail = $accountEmail;
        $this->status = $status;
        $this->merchantId = $merchantId;
        $this->enabledForInvoice = $enabledForInvoice;
        $this->enabledForpaymentPlan = $enabledForpaymentPlan;
        return $this;
    }
    
    public static function create($data)
    {
        $accountObject = new Account();

        return $accountObject->accountInit($accountObject->setAccountEmail($data->accountEmail), $accountObject->setStatus($data->status), $accountObject->setMerchantId($data->merchantId), $accountObject->setEnabledForInvoice($data->enabledForInvoice), $accountObject->setEnabledForpaymentPlans($data->enabledForpaymentPlan));
    }
    
    public function setAccountEmail($accountEmail = '')
    {
        if (is_null($this->accountEmail) && (!isset($this->accountEmail))) {
            $this->accountEmail = $accountEmail;
        }
        return $this->accountEmail;
    }
    

    public function setStatus($status = '')
    {
        if (is_null($this->status) && (!isset($this->status))) {
            $this->status = $status;
        }
        return $this->status;
    }

    public function setMerchantId($merchantId = '')
    {
        if (is_null($this->merchantId) && (!isset($this->merchantId))) {
            $this->merchantId = $merchantId;
        }
        return $this->merchantId;
    }


    public function setEnabledForInvoice($enabledForInvoice = '')
    {
        if (is_null($this->enabledForInvoice) && (!isset($this->enabledForInvoice))) {
            $this->enabledForInvoice = $enabledForInvoice;
        }
        return $this->enabledForInvoice;
    }

    public function setEnabledForpaymentPlans($enabledForpaymentPlan = '')
    {
        if (!isset($this->enabledForpaymentPlan)) {
            $this->enabledForpaymentPlan = $enabledForpaymentPlan;
        }
        return $this->enabledForpaymentPlan;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}
