<?php
namespace PaysonAB\PaysonCheckout2\Plugin\Controller\Currency;

class SwitchAction
{
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $_response;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Config
     */
    protected $paysonConfig;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Payment\BuildOrder
     */
    protected $_buildOrder;
    /**
     * @var
     */
    protected $currency;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;


    /**
     * SwitchAction constructor.
     *
     * @param \PaysonAB\PaysonCheckout2\Helper\Order             $orderHelper
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Message\ManagerInterface        $messageManager
     * @param \Magento\Framework\UrlInterface                    $url
     * @param \Magento\Framework\App\Response\Http               $response
     * @param \PaysonAB\PaysonCheckout2\Model\Config             $paysonConfig
     * @param \PaysonAB\PaysonCheckout2\Model\Payment\BuildOrder $buildOrder
     */
    public function __construct(
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $response,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig,
        \PaysonAB\PaysonCheckout2\Model\Payment\BuildOrder $buildOrder,
        \Magento\Framework\Registry $registry
    ) {
        $this->_orderHelper = $orderHelper;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_url = $url;
        $this->_response = $response;
        $this->paysonConfig = $paysonConfig;
        $this->_buildOrder = $buildOrder;
        $this->_registry = $registry;

    }

    /**
     * @param \Magento\Directory\Controller\Currency\SwitchAction $subject
     * @param callable                                            $proceed
     * @return $this
     */
    public function aroundExecute(\Magento\Directory\Controller\Currency\SwitchAction $subject, callable $proceed)
    {
        if ($this->paysonConfig->isEnabled() && $this->_orderHelper->hasActiveQuote()) {
            $this->currency = (string)$subject->getRequest()->getParam('currency');
            $this->_registry->register('currency', $this->currency);
            if($this->_orderHelper->getCurrencyAllowed()) {
                $this->_buildOrder->currencyConvertCheckoutCreate();
                if ($this->currency) {
                    $this->_storeManager->getStore()->setCurrentCurrencyCode($this->currency);
                }
            }else{
                $message = sprintf('Payson checkout %s currency not allowed', $this->currency);
                $this->_messageManager->addErrorMessage(
                    __($message)
                );
            }
            $redirectUrl = $this->_url->getUrl('checkout/payson');
            $this->_response->setRedirect($redirectUrl)->sendResponse();
        }else{
            $proceed();
        }
    }
}
