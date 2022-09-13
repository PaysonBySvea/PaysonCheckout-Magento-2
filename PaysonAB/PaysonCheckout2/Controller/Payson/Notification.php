<?php
namespace PaysonAB\PaysonCheckout2\Controller\Payson;

use Exception;

/**
 * Class Notification
 *
 * @package PaysonAB\PaysonCheckout2\Controller\Payson
 */
class Notification extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
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
     * @var \PaysonAB\PaysonCheckout2\Model\Payment\Notification
     */
    protected $_notification;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $_orderRepository;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\Config
     */
    protected $paysonConfig;

    /**
     * Notification constructor.
     *
     * @param \Magento\Framework\App\Action\Context                $context
     * @param \Magento\Framework\View\Result\PageFactory           $resultPageFactory
     * @param \PaysonAB\PaysonCheckout2\Helper\Order               $orderHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\DataLogger          $paysonHelper
     * @param \PaysonAB\PaysonCheckout2\Model\Payment\Notification $notification
     * @param \Magento\Sales\Model\OrderRepository                 $orderRepository
     * @param \PaysonAB\PaysonCheckout2\Model\Config               $paysonConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \PaysonAB\PaysonCheckout2\Helper\DataLogger $paysonHelper,
        \PaysonAB\PaysonCheckout2\Model\Payment\Notification $notification,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig
    ) {
        $this->_notification = $notification;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_orderHelper = $orderHelper;
        $this->_paysonHelper = $paysonHelper;
        $this->_orderRepository = $orderRepository;
        $this->paysonConfig = $paysonConfig;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->paysonConfig->isEnabled()) {
            $resultPage = $this->_resultPageFactory->create();
            try {
                $quoteId = $this->getRequest()->getParam('id');
                $paysonObject = $this->_orderHelper->getPaysonInfo($quoteId);
                $paysonQuoteId = $paysonObject->getQuoteId();
                $orderId = $paysonObject->getOrderId();
                if (!$paysonQuoteId) {
                    $this->_paysonHelper->info("Quote not found for queue ID `{$paysonObject->getPaysoncheckoutQueueId()}`");
                    $resultPage->setHttpResponseCode('503');

                    return false;
                }
                if (!$orderId) {
                    $this->_paysonHelper->info("Order not found for queue ID `{$paysonObject->getPaysoncheckoutQueueId()}`");
                    $resultPage->setHttpResponseCode('503');
                    return false;
                }

                $order = $this->_orderRepository->get($orderId);
                $this->_notification->process($order);
            } catch (\Exception $e) {
                $this->_paysonHelper->debug($e->getMessage());
                return false;
            }
        }
        return false;
    }

}
