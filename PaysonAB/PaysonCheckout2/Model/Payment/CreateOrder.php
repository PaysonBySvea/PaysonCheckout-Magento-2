<?php

namespace PaysonAB\PaysonCheckout2\Model\Payment;

use Exception;

/**
 * Class CreateOrder
 * @package PaysonAB\PaysonCheckout2\Model\Payment
 */
class CreateOrder
{
    const STATE_PAYSON_PROCESSING = 'payson_processing';
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $_response;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Data (Deprecated)
     * @var \PaysonAB\PaysonCheckout2\Helper\DataLogger
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
     * @var \PaysonAB\PaysonCheckout2\Helper\Transaction
     */
    protected $_transactionHelper;
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_dataObject;
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue
     */
    protected $_paysoncheckoutQueue;
    /**
     * @var CancelOrder
     */
    protected $_cancelOrder;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * CreateOrder constructor.
     * @param \PaysonAB\PaysonCheckout2\Helper\DataLogger $paysonHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper
     * @param \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartManagementInterface $quoteManagement
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \PaysonAB\PaysonCheckout2\Helper\Transaction $transactionHelper
     * @param \Magento\Framework\DataObject $dataObject
     * @param \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue $paysoncheckoutQueue
     * @param CancelOrder $cancelOrder
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(

        \PaysonAB\PaysonCheckout2\Helper\DataLogger $paysonHelper,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \PaysonAB\PaysonCheckout2\Helper\Transaction $transactionHelper,
        \Magento\Framework\DataObject $dataObject,
        \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue $paysoncheckoutQueue,
        \PaysonAB\PaysonCheckout2\Model\Payment\CancelOrder $cancelOrder,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->_url = $url;
        $this->_response = $response;
        $this->_paysonHelper = $paysonHelper;
        $this->_orderHelper = $orderHelper;
        $this->paysonConfig = $paysonConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteManagement = $quoteManagement;
        $this->_orderRepository = $orderRepository;
        $this->_transactionHelper = $transactionHelper;
        $this->_dataObject = $dataObject;
        $this->_paysoncheckoutQueue = $paysoncheckoutQueue;
        $this->_cancelOrder = $cancelOrder;
        $this->_orderFactory = $orderFactory;
        $this->_messageManager = $messageManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @param $checkoutResponse
     * @param $checkoutId
     * @return bool|int
     */

    public function createOrder($checkoutResponse, $checkoutId)
    {
        try {
            $api = $this->_orderHelper->getApi();
            $quote = $this->_orderHelper->convertQuoteToOrder($checkoutResponse->customer);
            $orderId = $this->_quoteManagement->placeOrder($quote->getId());
            $order = $this->_orderRepository->get($orderId);
            $this->eventManager->dispatch('sales_order_save_after', ['order' => $order]);
//            echo 123;
//            echo $checkoutResponse->status; die;
            switch ($checkoutResponse->status) {
                case 'readyToShip':
                    $order->setState(self::STATE_PAYSON_PROCESSING)->setStatus(self::STATE_PAYSON_PROCESSING)
                        ->setOrderId($orderId);
                    $order->setData(\PaysonAB\PaysonCheckout2\Model\ConfigInterface::CHECKOUT_ID_COLUMN, $checkoutId);
                    $order->setPaymentReference($checkoutResponse->purchaseId);
                    $order->save();
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                    $emailSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
                    $emailSender->send($order);
                    // Send order reference to Payson
                    $checkoutResponse->merchant->reference = $order->getIncrementId();
                    $api->UpdateCheckout($checkoutResponse);
                    $this->_savePaysonInfo($order, $checkoutId, $api);
                    $this->_saveItemsId($orderId, $checkoutResponse);
                    $responseObject = $api->GetCheckout($checkoutId);
                    $this->_transactionHelper->addTransaction($order, $responseObject);
                    break;

                case 'created':
                case 'processingPayment':

                    $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT)
                        ->setOrderId($orderId);
                    $order->save();

                    // Send order reference to Payson
                    $checkoutResponse->merchant->reference = $order->getIncrementId();
                    $api->UpdateCheckout($checkoutResponse);

                    $this->_savePaysonInfo($order, $checkoutId, $api);

                    $this->_messageManager->addErrorMessage(
                        __('Your payment is being processed by Payson.')
                    );

                    $checkoutPaysonUrl = $this->_url->getUrl('checkout/payson');
                    $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                    break;

                case 'denied':
                    $message = sprintf(__('The payment was denied by Payson.'));

                    $order = $this->setCancelStatus($message, $order);
                    $order->save();

                    $this->_savePaysonInfo($order, $checkoutId, $api);

                    $this->_messageManager->addErrorMessage(
                        __($message)
                    );

                    $checkoutPaysonUrl = $this->_url->getUrl('checkout/payson');
                    $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                    break;

                case 'expired':
                    $message = sprintf(__('The payment was expired by Payson.'));

                    $order = $this->setCancelStatus($message, $order);
                    $order->save();

                    $this->_savePaysonInfo($order, $checkoutId, $api);

                    $this->_messageManager->addErrorMessage(
                        __($message)
                    );

                    $checkoutPaysonUrl = $this->_url->getUrl('checkout/payson');
                    $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                    break;

                case 'canceled': {
                    $message = sprintf(__('Order was canceled at Payson.'));

                    $order = $this->setCancelStatus($message, $order);
                    $order->save();

                    $this->_savePaysonInfo($order, $checkoutId, $api);

                    $this->_messageManager->addErrorMessage(
                        __($message)
                    );

                    $checkoutPaysonUrl = $this->_url->getUrl('checkout/payson');
                    $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();

                    break;

                }
                default: {
                    $message = sprintf(__('Something went wrong with the payment.'));

                    $this->_messageManager->addErrorMessage(
                        __($message)
                    );

                    $checkoutPaysonUrl = $this->_url->getUrl('checkout/payson');
                    $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                    break;
                }
            }

        } catch (\Exception $e) {
            $this->_paysonHelper->debug($e->getMessage());
            return false;
        }
    }

    /**
     * @param $order
     * @param $checkoutId
     * @param $api
     */

    protected function _savePaysonInfo($order, $checkoutId, $api)
    {
        try {
            $response = $api->GetCheckout($checkoutId);

            /* Order information save in payson table start */
            $model = $this->_paysoncheckoutQueue;
            $model->load($checkoutId, 'checkout_id');
            $model->setOrderId($order->getId());
            $model->setStatus($response->status);
            $model->setPaysonResponse($this->_orderHelper->convertToJson($response));
            $model->save();
            /* Order information save in payson table end */
        } catch (\Exception $e) {
            $this->_paysonHelper->debug($e->getMessage());
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
            $paysonLoggerHelper->debug($e->getMessage());
        }
    }

    /**
     * @param $orderId
     * @param $checkoutResponse
     */
    protected function _saveItemsId($orderId, $checkoutResponse)
    {
        try{
            $order = $this->_orderRepository->get($orderId);
            $orderItems = $order->getAllItems();
            $paysonItemSku = [];
            foreach ($checkoutResponse->payData->items as $paysonItem)
            {
                if($paysonItem->type == \PaysonAB\PaysonCheckout2\Helper\Order::PHYSICAL){
                    $paysonItemSku[$paysonItem->reference] = $paysonItem->itemId;
                }
            }

            foreach($orderItems as $item)
            {
                if(in_array($item->getSku(), array_keys($paysonItemSku))){
                    $item->setPaysonItemId($paysonItemSku[$item->getSku()]);
                }
                $item->save();
            }
        }catch (\Exception $e) {
             $this->_paysonHelper->debug($e->getMessage());
        }
    }
}
