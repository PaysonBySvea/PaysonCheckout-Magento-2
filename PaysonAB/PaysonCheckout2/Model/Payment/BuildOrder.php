<?php
namespace PaysonAB\PaysonCheckout2\Model\Payment;

use PaysonAB\PaysonCheckout2\Model\Api\PaysonApiException;
use Magento\Store\Model\ScopeInterface;

/**
 * Class BuildOrder
 *
 * @package PaysonAB\PaysonCheckout2\Model\Payment
 */
class BuildOrder
{
    /**
    * @var \Magento\Framework\UrlInterface
    */
    protected $_url;
    /**
    * @var \Magento\Framework\App\Response\Http
    */
    protected $_response;
    /**
    * @var \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi
    */
    protected $_paysonApi;
    /**
    * @var \PaysonAB\PaysonCheckout2\Helper\Order
    */
    protected $_orderHelper;
    /**
    * @var \PaysonAB\PaysonCheckout2\Helper\Data
    */
    protected $_paysonHelper;
    /**
    * @var \Magento\Framework\Stdlib\DateTime\DateTime
    */
    protected $_datetime;
    /**
    * @var \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue
    */
    protected $_paysoncheckoutQueue;
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;
    /**
    * @var \Magento\Shipping\Model\Config
    */
    protected $shipconfig;
    /**
    * @var CreateOrder
    */
    protected $_createOrder;
    /**
    * @var \Magento\Framework\Message\ManagerInterface
    */
    protected $_messageManager;
    /**
    * @var \Magento\Customer\Api\CustomerRepositoryInterface
    */
    protected $_customerRepository;
    /**
    * @var \Magento\Customer\Api\AddressRepositoryInterface
    */
    protected $_addressRepository;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * BuildOrder constructor.
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Response\Http $response
     * @param \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi $paysonApi
     * @param \PaysonAB\PaysonCheckout2\Helper\OrderFactory $orderHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\Data $paysonHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue $paysoncheckoutQueue
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Shipping\Model\Config $shipconfig
     * @param CreateOrder $createOrder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $response,
        \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi $paysonApi,
        \PaysonAB\PaysonCheckout2\Helper\OrderFactory $orderHelper,
        \PaysonAB\PaysonCheckout2\Helper\Data $paysonHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue $paysoncheckoutQueue,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shipconfig,
        CreateOrder $createOrder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_url = $url;
        $this->_response = $response;
        $this->_paysonApi = $paysonApi;
        $this->_orderHelper = $orderHelper;
        $this->_paysonHelper= $paysonHelper;
        $this->_datetime = $datetime;
        $this->_paysoncheckoutQueue = $paysoncheckoutQueue;
        $this->shipconfig = $shipconfig;
        $this->scopeConfig = $scopeConfig;
        $this->_createOrder = $createOrder;
        $this->_messageManager = $messageManager;
        $this->_customerRepository = $customerRepository;
        $this->_addressRepository = $addressRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param $quote
     * @param $paysonCheckoutId
     */
    public function paysonSaveInformation($quote, $paysonCheckoutId)
    {
        $model = $this->_paysoncheckoutQueue;
        try {
            $quote = $this->setShippingAddress($quote);
            if (!$quote) {
                $quote = $this->checkoutSession->getQuote();
            }
            if ($paysonCheckoutId) {
                // Already have an active checkout
                $paysonResponse = $this->_paysonApi->GetCheckout($paysonCheckoutId);
                switch ($paysonResponse->status) {
                case 'readyToShip':
                    if(is_null($quote->getPayment()->getMethod())) {
                        $this->_savePaymentMethod($quote);
                    }
                    $this->_createOrder->createOrder($paysonResponse, $paysonCheckoutId);
                    $checkoutPaysonUrl = $this->_url->getUrl('checkout/payson/confirmation');
                    $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                    break;
                case 'created':
                        $this->_orderHelper->create()->updateCart($paysonCheckoutId);
                    $this->_paysonsaveInfo($quote, $paysonCheckoutId, $model);
                    break;
                case 'expired':
                    $quote->setIsActive(false);
                    $quote->delete();
                    $checkoutPaysonUrl = $this->_url->getUrl('checkout/payson/emptycart');
                    $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                    break;
                case 'denied':
                default:
                    $message = sprintf(__('Something went wrong with the payment.'));

                    $this->_messageManager->addErrorMessage(
                        __($message)
                    );
                    throw new PaysonApiException($message);
                        break;
                }
            } else {

                $paysonCheckoutId = $this->_createCheckoutId();
                $this->_saveQuoteCheckoutId($quote, $paysonCheckoutId);
                $this->_paysonsaveInfo($quote, $paysonCheckoutId, $model);
                $this->_savePaymentMethod($quote);
                $this->_orderHelper->create()->updateCart($paysonCheckoutId);
            }
        } catch (\Exception $e) {
            $paysonLoggerHelper  = $this->_paysonHelper;
            $paysonLoggerHelper->error($e->getMessage());
        }
    }

    /**
     * @param $quote
     */
    protected  function _savePaymentMethod($quote)
    {
        try{
            /*magento code table quote id save get in payson checkout id*/
            $payment = $quote->getPayment();
            $payment->setMethod(\PaysonAB\PaysonCheckout2\Model\Paysoncheckout2ConfigProvider::CHECKOUT_CODE);
            $payment->save();
        } catch (\Exception $e){
            $paysonLoggerHelper  = $this->_paysonHelper;
            $paysonLoggerHelper->error($e->getMessage());
        }
    }

    /**
     * @param $quote
     * @param $paysonCheckoutId
     * @param $model
     */
    protected function _paysonsaveInfo($quote, $paysonCheckoutId, $model)
    {
        try{
            /* payson model save */
            $paysonResponse = $this->_paysonApi->GetCheckout($paysonCheckoutId);
            
            $collection = $model->getCollection()->addFieldToFilter('checkout_id', $paysonCheckoutId);

            if($collection->getFirstItem()->getId()) {
                $model->load($collection->getFirstItem()->getId());
                $model->setPaysonResponse($this->_orderHelper->create()->convertToJson($paysonResponse));
            } else {
                $model->setQuoteId($quote->getId());
                $model->setCheckoutId($paysonCheckoutId);
                $model->setCreatedAt($this->_datetime->gmtTimestamp());
                $model->setStatus($paysonResponse->status);
                $model->setPaysonResponse($this->_orderHelper->create()->convertToJson($paysonResponse));
            }
            $model->save();
        } catch (\Exception $e){
            $paysonLoggerHelper  = $this->_paysonHelper;
            $paysonLoggerHelper->error($e->getMessage());
        }


    }

    /**
     * @return array|null|string
     */
    protected  function _createCheckoutId()
    {
        try{
            $this->_orderHelper->create()->loadPaysonApi();
            /*
            * Step 2 Create checkout
            */
            $paysonCheckoutId = $this->_paysonApi->CreateCheckout();
            return $paysonCheckoutId;
        } catch (\Exception $e){
            $paysonLoggerHelper  = $this->_paysonHelper;
            $paysonLoggerHelper->error($e->getMessage());
        }

    }

    /**
     * @param $quote
     * @param $checkoutId
     */
    protected function _saveQuoteCheckoutId($quote, $checkoutId)
    {
        try{
            /* quote save */
            if (!$quote) {
                $quote = $this->checkoutSession->getQuote();
            }
            $quote->setData(\PaysonAB\PaysonCheckout2\Model\ConfigInterface::CHECKOUT_ID_COLUMN, $checkoutId);
            $quote->save();
        } catch (\Exception $e){
            $paysonLoggerHelper  = $this->_paysonHelper;
            $paysonLoggerHelper->error($e->getMessage());
        }
    }

    /**
     * @param $quote
     * @return mixed
     */
    public function saveShippingMethod($quote)
    {
        try {
            if ($shippingMethodName = $this->getShippingMethods($quote)) {
                $this->_quoteObj = $quote;
                $shippingMethod = is_null($this->_quoteObj->getShippingAddress()->getShippingMethod()) ? $shippingMethodName : $this->_quoteObj->getShippingAddress()->getShippingMethod();

                $shippingAddress = $this->_quoteObj->getShippingAddress();
                $shippingAddress->setCollectShippingRates(true);
                $shippingAddress->setShippingMethod($shippingMethod);
                $this->_quoteObj->setTotalsCollectedFlag(false);
                $this->_quoteObj->collectTotals()->save();


                return $this->_quoteObj;
            }
        } catch (\Exception $e) {
            $paysonLoggerHelper  = $this->_paysonHelper;
            $paysonLoggerHelper->error($e->getMessage());
        }
    }

    /**
     * @param $quote
     * @return string
     */
    public function getShippingMethods($quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        if(is_null($shippingAddress->getShippingMethod())) {
            $activeCarriers = $this->shipconfig->getActiveCarriers();
            if (!empty($activeCarriers)) {
                foreach ($activeCarriers as $carrierCode => $carrierModel) {
                    if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                        foreach ($carrierMethods as $methodCode => $method) {
                            $code = $carrierCode . '_' . $methodCode;
                        }
                    }
                }
                return $code;
            }
        }
        return $shippingAddress->getShippingMethod();
    }

    /**
     * @param $quoteId
     * @return array
     */
    public function paysonCollection($quoteId)
    {
        $paysoncheckout = $this->_paysoncheckoutQueue;
        $paysoncheckoutCollection = $paysoncheckout->getcollection();
        $paysoncheckoutCollection->addFieldToFilter('quote_id', $quoteId)
            ->addFieldToFilter('order_id', array('null' => true));

        $payson = [];
        foreach ($paysoncheckoutCollection as $paysoncheckout)
        {
            $payson = [
                'id' => $paysoncheckout->getId(),
                'payson_response' => $paysoncheckout->getPaysonResponse()
            ];
        }
        return $payson;
    }

    /**
     * @param $quote
     * @return mixed
     */
    public function setShippingAddress($quote)
    {
        if(!$quote->getCustomerId()) {
            $defaultValue = $this->scopeConfig->getValue(
                \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                ScopeInterface::SCOPE_STORE
            );
            $quote->getShippingAddress()->setCountryId($defaultValue)->save();
            return $quote;
        }
        $customer = $this->_customerRepository->getById($quote->getCustomerId());

        $defaultAddressId = $customer->getDefaultShipping();
        if(!is_null($defaultAddressId)){
            $defaultShippingAddress = $this->_addressRepository->getById($defaultAddressId);
            if ($quote->getCustomerId() && !$defaultShippingAddress->getCountryId()) {
                $defaultValue = $this->scopeConfig->getValue(
                    \Magento\Tax\Model\Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                    ScopeInterface::SCOPE_STORE
                );
                $quote->getShippingAddress()->setCountryId($defaultValue)->save();
                return $quote;
            }
            if ($quote->getCustomerId() && $defaultShippingAddress->getCountryId()) {
                $quote->getShippingAddress()->setCountryId($defaultShippingAddress->getCountryId())->save();
                return $quote;
            }
        }
    }

    public function currencyConvertCheckoutCreate()
    {
        $model = $this->_paysoncheckoutQueue;
        try {

            $quote = $this->_orderHelper->create()->getQuote();
            $paysonCheckoutId = $this->_createCheckoutId();
            $paysonResponse = $this->_paysonApi->GetCheckout($paysonCheckoutId);
            $this->_saveQuoteCheckoutId($quote, $paysonCheckoutId);

            $collection = $model->getCollection()->addFieldToFilter('quote_id', $quote->getId());
            if($collection->getFirstItem()->getId()) {
                $model->load($collection->getFirstItem()->getId());
                $model->setCheckoutId($paysonCheckoutId);
                $model->setPaysonResponse($this->_orderHelper->create()->convertToJson($paysonResponse));
            }
            $model->save();
            if(!$quote->getPayment()->getMethod()) {
                $this->_savePaymentMethod($quote);
            }
            $this->_orderHelper->create()->updateCart($paysonCheckoutId);
        } catch (\Exception $e) {
            $paysonLoggerHelper  = $this->_paysonHelper;
            $paysonLoggerHelper->error($e->getMessage());
        } 
    }
}
