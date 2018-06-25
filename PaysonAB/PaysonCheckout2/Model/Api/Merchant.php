<?php
namespace PaysonAB\PaysonCheckout2\Model\Api;

class Merchant
{
    /**
 * @var url $checkoutUri URI to the merchants checkout page.
*/
    public $checkoutUri = null;
    /**
 * @var url $confirmationUri URI to the merchants confirmation page. 
*/
    public $confirmationUri;
    /**
 * @var url $notificationUri Notification URI which receives CPR-status updates. 
*/
    public $notificationUri;
    /**
 * @var url $verificationUri Validation URI which is called to verify an order before it can be paid. 
*/
    public $validationUri = null;
    /**
 * @var url $termsUri URI leading to the sellers terms. 
*/
    public $termsUri;
    /**
 * @var string $reference Merchants own reference of the checkout.
*/
    public $reference = null;
    /**
 * @var string $partnerId Partners unique identifier 
*/
    public $partnerId = null;
    /**
 * @var string $integrationInfo Information about the integration. 
*/
    public $integrationInfo = null;

    /*public function __construct()
    {
    }*/

    public function metchantInit($checkoutUri, $confirmationUri, $notificationUri, $termsUri, $partnerId = null)
    {
        //Updated to use object manager
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $moduleListdata = $objectManager->get('Magento\Framework\Module\ModuleListInterface');
        $version = $productMetadata->getVersion();
        $moduleName = $moduleListdata->getOne('PaysonAB_PaysonCheckout2');

        $integrationInfo = 'PaysonCheckout2Magento2|'.$moduleName['setup_version'].'|'.$version;
        $this->checkoutUri = $checkoutUri;
        $this->confirmationUri = $confirmationUri;
        $this->notificationUri = $notificationUri;
        $this->termsUri = $termsUri;
        $this->partnerId = $partnerId;
        $this->integrationInfo = $integrationInfo;
        return $this;
    }
        
    public static function create($data)
    {
        $merchantObject = new Merchant();
        $merchant =  $merchantObject->metchantInit($data->checkoutUri, $data->confirmationUri, $data->notificationUri, $data->termsUri, $data->partnerId, $data->integrationInfo);
        $merchant->setReference($data->reference);
        $merchant->setValidationUri($data->validationUri);
        return $merchant;
    }
     
    public function toArray()
    {
        return get_object_vars($this);
    }

    public function setReference($reference)
    {
        if (is_null($this->reference)) {
            $this->reference = $reference;
        }
        return $this->reference;
    }

    public function setValidationUri($validationUri)
    {
        if (is_null($this->validationUri)) {
            $this->validationUri = $validationUri;
        }
        return $this->validationUri;
    }
}
