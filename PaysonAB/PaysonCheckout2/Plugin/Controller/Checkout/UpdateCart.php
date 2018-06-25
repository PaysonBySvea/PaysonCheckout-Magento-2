<?php
namespace PaysonAB\PaysonCheckout2\Plugin\Controller\Checkout;
/**
 * Class UpdateCart
 *
 * @package PaysonAB\PaysonCheckout2\Plugin\Controller\Checkout
 */
class UpdateCart
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
     * UpdateCart constructor.
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
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper
    ) {
        $this->_url = $url;
        $this->_response = $response;
        $this->paysonConfig = $paysonConfig;
        $this->_orderHelper = $orderHelper;
    }

    public function afterExecute()
    {
        /* If module items cart empty when return parent controller*/
        if ($this->paysonConfig->isEnabled()) {
            if ($this->_orderHelper->hasActiveQuote()) {
                $quote =  $this->_orderHelper->getQuote();
                if ($quote->getPaysonCheckoutId()) {
                    $this->_orderHelper->updateCart($quote->getPaysonCheckoutId());
                }
                $checkoutPaysonUrl = $this->_url->getUrl('checkout/payson');
                $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
            } else {
                $checkoutPaysonUrl = $this->_url->getUrl('checkout/payson/emptycart');
                $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
            }
        }
    }
}
