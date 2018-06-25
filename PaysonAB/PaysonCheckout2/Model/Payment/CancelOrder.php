<?php

namespace PaysonAB\PaysonCheckout2\Model\Payment;
/**
 * Class CancelOrder
 *
 * @package PaysonAB\PaysonCheckout2\Model\Payment
 */
class CancelOrder
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
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $_quoteManagement;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $_orderRepository;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue
     */
    protected $_paysoncheckoutQueue;

    /**
     * CancelOrder constructor.
     *
     * @param \PaysonAB\PaysonCheckout2\Helper\Data               $paysonHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\Order              $orderHelper
     * @param \Magento\Quote\Api\CartManagementInterface          $quoteManagement
     * @param \Magento\Sales\Model\OrderRepository                $orderRepository
     * @param \Magento\Checkout\Model\Session                     $checkoutSession
     * @param \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue $paysoncheckoutQueue
     */

    public function __construct(
        \PaysonAB\PaysonCheckout2\Helper\Data $paysonHelper,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue $paysoncheckoutQueue
    ) {
    
        $this->_paysonHelper = $paysonHelper;
        $this->_orderHelper = $orderHelper;
        $this->_quoteManagement = $quoteManagement;
        $this->_orderRepository = $orderRepository;
        $this->_checkoutSession = $checkoutSession;
        $this->_paysoncheckoutQueue = $paysoncheckoutQueue;
    }

    /**
     * @param $message
     * @param $quoteId
     * @return mixed
     */
    public function cancelOrder($message, $quoteId)
    {
        try {
            $paysonObject = $this->_orderHelper->getPaysonInfo($quoteId);
            $api = $this->_orderHelper->getApi();
            $checkoutId = $paysonObject->getCheckoutId();

            $quotePaysonResponse = $api->GetCheckout($checkoutId);

            $quote = $this->_orderHelper->convertQuoteToOrder($quotePaysonResponse->customer);
            $orderId = $this->_quoteManagement->placeOrder($paysonObject->getQuoteId());
            $order = $this->_orderRepository->get($orderId);

            $order->setData(\PaysonAB\PaysonCheckout2\Model\ConfigInterface::CHECKOUT_ID_COLUMN, $checkoutId);

            $order = $this->setCancelStatus($message, $order);
            if (($order->getPayment()->getMethod() == \PaysonAB\PaysonCheckout2\Model\Paysoncheckout2ConfigProvider::CHECKOUT_CODE) && ($order->getState() === \Magento\Sales\Model\Order::STATE_CANCELED || $order->getState() === \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)) {

                $checkoutId = $order->getData(\PaysonAB\PaysonCheckout2\Model\ConfigInterface::CHECKOUT_ID_COLUMN);
                $checkout = $api->GetCheckout($checkoutId);

                $api->CancelCheckout($checkout);
                $order->setIsTransactionClosed(1);
                $order->save();
            }

            $orderPaysonResponse = $api->GetCheckout($order->getPaysonCheckoutId());
            /* Order information save in payson table start */
            $model = $this->_paysoncheckoutQueue;
            $model->load($order->getPaysonCheckoutId(), 'checkout_id');
            $model->setOrderId($order->getId());
            $model->setStatus($orderPaysonResponse->status);
            $model->setPaysonResponse($this->_orderHelper->convertToJson($orderPaysonResponse));
            $model->save();
            /* Order information save in payson table end */
            return $order->getId();
        } catch (\Exception $e) {
            $paysonLoggerHelper = $this->_paysonHelper;
            $paysonLoggerHelper->error($e->getMessage());
        }
    }

    /**
     * @param string $message
     * @param $order
     * @return mixed
     */
    public function setCancelStatus($message = '', $order)
    {
        try {
            if (!is_null($order)) {
                $order->cancel();

                if ($message != '') {
                    $order->addStatusHistoryComment($message);
                }
            }
            return $order;
        } catch (\Exception $e) {
            $paysonLoggerHelper = $this->_paysonHelper;
            $paysonLoggerHelper->error($e->getMessage());
        }
    }
}