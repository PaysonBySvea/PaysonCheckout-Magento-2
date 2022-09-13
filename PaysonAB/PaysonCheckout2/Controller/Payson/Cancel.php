<?php
namespace PaysonAB\PaysonCheckout2\Controller\Payson;
/**
 * Class Cancel
 *
 * @package PaysonAB\PaysonCheckout2\Controller\Payson
 */

use Exception;

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Data (Deprecated)
     * @var \PaysonAB\PaysonCheckout2\Helper\DataLogger
     */
    protected $_paysonHelper;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Payment\CancelOrder
     */
    protected $_cancelOrder;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Config
     */
    protected $paysonConfig;
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * Cancel constructor.
     *
     * @param \Magento\Framework\App\Action\Context               $context
     * @param \PaysonAB\PaysonCheckout2\Helper\DataLogger         $paysonHelper
     * @param \Magento\Framework\View\Result\PageFactory          $resultPageFactory
     * @param \PaysonAB\PaysonCheckout2\Model\Payment\CancelOrder $cancelOrder
     * @param \PaysonAB\PaysonCheckout2\Model\Config              $paysonConfig
     * @param \PaysonAB\PaysonCheckout2\Helper\Order              $orderHelper
     * @param \Magento\Checkout\Model\Session                     $checkoutSession
     * @param \Magento\Framework\Controller\Result\JsonFactory    $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \PaysonAB\PaysonCheckout2\Helper\DataLogger $paysonHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \PaysonAB\PaysonCheckout2\Model\Payment\CancelOrder $cancelOrder,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_paysonHelper = $paysonHelper;
        $this->_cancelOrder = $cancelOrder;
        $this->paysonConfig = $paysonConfig;
        $this->_orderHelper = $orderHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $paysonLoggerHelper  = $this->_paysonHelper;
        try{

            $resultPage = $this->resultPageFactory->create();
            $resultRedirect = $this->resultRedirectFactory->create();
            if (($this->paysonConfig->isEnabled() && ($this->_checkoutSession->getQuoteId() == $quoteId) )) {
                $cancelMessage = sprintf(__('Order was canceled at Payson.'));
                $cancelOrderId = $this->_cancelOrder->cancelOrder($cancelMessage, $quoteId);
                if($cancelOrderId) {
                    $response['redirectUrl'] = $this->_url->getUrl('checkout/payson/cancelorder');
                    $resultJson = $this->_resultJsonFactory->create();
                    $resultJson->setData($response);
                    return $resultJson;
                }else{
                    $this->messageManager->addError(
                        __('Order has been not canceled.')
                    );
                    $resultRedirect->setPath('/');
                }
                return $resultPage;
            }
            return $resultRedirect->setPath('/');
        }catch (\Exception $e) {
            $paysonLoggerHelper->debug($e->getMessage());
        }
    }
}
