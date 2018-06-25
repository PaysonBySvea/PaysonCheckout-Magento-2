<?php
namespace PaysonAB\PaysonCheckout2\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RefundPaysonAmount implements ObserverInterface
{
    protected $_messageManager;
    protected $_paysonHelper;
    protected $_orderHelper;
    protected $paysonConfig;
    protected $_url;
    protected $_response;
    protected $_paysoncheckoutQueue;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \PaysonAB\PaysonCheckout2\Helper\Data $paysonHelper,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $response,
        \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue $paysoncheckoutQueue
    ) {
        $this->_messageManager = $messageManager;
        $this->_paysonHelper= $paysonHelper;
        $this->_orderHelper = $orderHelper;
        $this->paysonConfig = $paysonConfig;
        $this->_url = $url;
        $this->_response = $response;
        $this->_paysoncheckoutQueue = $paysoncheckoutQueue;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if($this->paysonConfig->getCaptureInMagento()) {
            $api = $this->_orderHelper->getApi();
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $order = $creditmemo->getOrder();
            $items = $creditmemo->getAllItems();
            $checkout = $api->GetCheckout($order->getPaysonCheckoutId());

            $order_id = $order->getIncrementId();
            $message = __('Payment was credited at Payson');

            if ($order->getPayment()->getMethod() == \PaysonAB\PaysonCheckout2\Model\Paysoncheckout2ConfigProvider::CHECKOUT_CODE) {
                if ($checkout->status == 'shipped') {
                    try{
                        $orderItemId = [];
                        foreach ($items as $item)
                        {
                            if($item->getOrderItem()->isDummy()) {
                                continue;
                            }

                            //$tax = $item->getTaxAmount() ? $item->getTaxAmount() : 0;
                            $price = $item->getPrice() != $item->getPriceInclTax() ? $item->getRowTotalInclTax() : $item->getRowTotal();
                            $itemTotalRefunded = $price - $item->getDiscountAmount();
                            $orderItemId[$item->getOrderItem()->getPaysonItemId()] = $itemTotalRefunded;
                        }

                        // Credits each order item
                        $totalCreditedAmount = 0;
                        foreach($checkout->payData->items as $paysonItem)
                        {
                            if(in_array($paysonItem->itemId, array_keys($orderItemId))) {
                                $paysonItem->creditedAmount = $orderItemId[$paysonItem->itemId];
                                $totalCreditedAmount +=  $orderItemId[$paysonItem->itemId];
                            }

                            if($paysonItem->type == \PaysonAB\PaysonCheckout2\Helper\Order::FEE) {
                                $paysonItem->creditedAmount = $order->getShippingInclTax();
                                $totalCreditedAmount += $order->getShippingInclTax();
                            }
                        }
                        $checkout->payData->totalCreditedAmount = (float) $totalCreditedAmount;
                        $api->UpdateCheckout($checkout);
                        $order->addStatusHistoryComment($message);
                        $orderPaysonResponse = $api->GetCheckout($order->getPaysonCheckoutId());
                        $model = $this->_paysoncheckoutQueue;
                        $model->load($order->getPaysonCheckoutId(), 'checkout_id');
                        $model->setPaysonResponse($this->_orderHelper->convertToJson($orderPaysonResponse));
                        $model->save();

                        $this->_messageManager->addSuccess(__($message));
                    } catch (\Exception $e) {
                        $this->_paysonHelper->error($e->getMessage());
                    }
                } else {
                    $errorMessage = __('Unable to refund order: %1. It must have status "shipped" but it´s current status is: '.$checkout->status, $order_id);
                    $this->_messageManager->addError(__($errorMessage));
                    $checkoutPaysonUrl = $this->_url->getUrl('*/*/new', ['order_id' => $order->getEntityId()]);
                    $this->_response->setRedirect($checkoutPaysonUrl)->sendResponse();
                    throw new \Exception($errorMessage);
                }
            }
        }
    }
}