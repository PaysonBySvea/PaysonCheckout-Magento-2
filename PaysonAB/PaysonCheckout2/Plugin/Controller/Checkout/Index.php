<?php
namespace PaysonAB\PaysonCheckout2\Plugin\Controller\Checkout;
/**
 * Class Index
 *
 * @package PaysonAB\PaysonCheckout2\Plugin\Controller\Checkout
 */
class Index
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
     * @var \PaysonAB\PaysonCheckout2\Model\Config
     */
    protected $paysonConfig;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Data (Deprecated)
     * @var \PaysonAB\PaysonCheckout2\Helper\DataLogger
     */
    protected $_paysonHelper;
    
    /**
     * Index constructor.
     *
     * @param \Magento\Framework\UrlInterface        $url
     * @param \Magento\Framework\App\Response\Http   $response
     * @param \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig
     * @param \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $response,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \PaysonAB\PaysonCheckout2\Helper\DataLogger $paysonHelper
    ) {
        $this->_url = $url;
        $this->_response = $response;
        $this->paysonConfig = $paysonConfig;
        $this->_orderHelper = $orderHelper;
        $this->_paysonHelper = $paysonHelper;
    }

    public function beforeExecute()
    {
        if ($this->paysonConfig->isEnabled() && $this->_orderHelper->getCurrencyAllowed()) {
            $paysonLoggerHelper  = $this->_paysonHelper;
            try {
                $api = $this->_orderHelper->getApi();
                $valid = $api->Validate();
                $accountEmail = $valid->accountEmail;
            } catch(\Exception $e) {
                $accountEmail = null;
                $paysonLoggerHelper->debug($e->getMessage());
            }
            
            if ($accountEmail != null) {
                $checkoutPaysonUrl = $this->_url->getUrl('checkout/payson');
                $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
            }
        }
    }
}
