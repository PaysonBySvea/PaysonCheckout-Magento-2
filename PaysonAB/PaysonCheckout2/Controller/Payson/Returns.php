<?php
namespace PaysonAB\PaysonCheckout2\Controller\Payson;
/**
 * Class Returns
 *
 * @package PaysonAB\PaysonCheckout2\Controller\Payson
 */
class Returns extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Data
     */
    protected $_paysonHelper;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Config
     */
    protected $paysonConfig;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Payment\CreateOrder
     */
    protected $_createOrder;
    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $_response;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * Returns constructor.
     *
     * @param \Magento\Framework\App\Action\Context               $context
     * @param \PaysonAB\PaysonCheckout2\Helper\Data               $paysonHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\Order              $orderHelper
     * @param \PaysonAB\PaysonCheckout2\Model\Config              $paysonConfig
     * @param \PaysonAB\PaysonCheckout2\Model\Payment\CreateOrder $createOrder
     * @param \Magento\Framework\App\Response\Http                $response
     * @param \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \PaysonAB\PaysonCheckout2\Helper\Data $paysonHelper,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig,
        \PaysonAB\PaysonCheckout2\Model\Payment\CreateOrder $createOrder,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_paysonHelper = $paysonHelper;
        $this->_orderHelper = $orderHelper;
        $this->paysonConfig = $paysonConfig;
        $this->_createOrder = $createOrder;
        $this->_url = $context->getUrl();
        $this->_response = $response;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return bool|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $paysonLoggerHelper  = $this->_paysonHelper;
            if ($this->paysonConfig->isEnabled()) {
                $quote = $this->_orderHelper->getQuote();
                if (true && $quote->getId()) {
                    $checkoutId = $quote->getData(\PaysonAB\PaysonCheckout2\Model\ConfigInterface::CHECKOUT_ID_COLUMN);
                    $api = $this->_orderHelper->getApi();

                    $checkout = $api->GetCheckout($checkoutId);
                    $this->_createOrder->createOrder($checkout, $checkoutId);
                    $response['redirectUrl'] = $this->_url->getUrl('checkout/payson/confirmation');
                    $resultJson = $this->_resultJsonFactory->create();
                    $resultJson->setData($response);
                    return $resultJson;
                }
                $checkoutPaysonUrl = $this->_url->getUrl('/');
                $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                return false;
            }
        } catch (\Exception $e) {
            $paysonLoggerHelper->error($e->getMessage());
            return false;
        }
    }

}
