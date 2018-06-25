<?php
namespace Eastlane\PaysonCheckout2\Controller\Payson;

use Exception;

/**
 * Class Notification
 *
 * @package Eastlane\PaysonCheckout2\Controller\Payson
 */
class Notification extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var \Eastlane\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \Eastlane\PaysonCheckout2\Helper\Data
     */
    protected $_paysonHelper;
    /**
     * @var \Eastlane\PaysonCheckout2\Model\Payment\Notification
     */
    protected $_notification;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $_orderRepository;
    /**
     * @var \Eastlane\PaysonCheckout2\Model\Config
     */
    protected $paysonConfig;

    /**
     * Notification constructor.
     *
     * @param \Magento\Framework\App\Action\Context                $context
     * @param \Magento\Framework\View\Result\PageFactory           $resultPageFactory
     * @param \Eastlane\PaysonCheckout2\Helper\Order               $orderHelper
     * @param \Eastlane\PaysonCheckout2\Helper\Data                $paysonHelper
     * @param \Eastlane\PaysonCheckout2\Model\Payment\Notification $notification
     * @param \Magento\Sales\Model\OrderRepository                 $orderRepository
     * @param \Eastlane\PaysonCheckout2\Model\Config               $paysonConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Eastlane\PaysonCheckout2\Helper\Order $orderHelper,
        \Eastlane\PaysonCheckout2\Helper\Data $paysonHelper,
        \Eastlane\PaysonCheckout2\Model\Payment\Notification $notification,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Eastlane\PaysonCheckout2\Model\Config $paysonConfig
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
            $paysonLoggerHelper = $this->_paysonHelper;
            try {
                $quoteId = $this->getRequest()->getParam('id');
                $paysonObject = $this->_orderHelper->getPaysonInfo($quoteId);
                $paysonQuoteId = $paysonObject->getQuoteId();
                $orderId = $paysonObject->getOrderId();
                if (!$paysonQuoteId) {
                    $this->_paysonHelper->log("Quote not found for queue ID `{$paysonObject->getPaysoncheckoutQueueId()}`");
                    $resultPage->setHttpResponseCode('503');

                    return false;
                }
                if (!$orderId) {
                    $this->_paysonHelper->log("Order not found for queue ID `{$paysonObject->getPaysoncheckoutQueueId()}`");
                    $resultPage->setHttpResponseCode('503');
                    return false;
                }

                $order = $this->_orderRepository->get($orderId);
                $this->_notification->process($order);
            } catch (\Exception $e) {
                $paysonLoggerHelper->error($e->getMessage());
                return false;
            }
        }
        return false;
    }

}
