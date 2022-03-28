<?php

namespace PaysonAB\PaysonCheckout2\Model\Payment;

use Exception;

class Notification
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
     * @var \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi
     */
    protected $_paysonApi;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Data
     */
    protected $_paysonHelper;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shipconfig;
    /**
     * @var CreateOrder
     */
    protected $_createOrder;
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $_invoiceSender;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Transaction
     */
    protected $_transactionHelper;

    /**
     * Notification constructor.
     *
     * @param \Magento\Framework\UrlInterface                       $url
     * @param \Magento\Framework\App\Response\Http                  $response
     * @param \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi         $paysonApi
     * @param \PaysonAB\PaysonCheckout2\Helper\Order                $orderHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\Data                 $paysonHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
     * @param \Magento\Shipping\Model\Config                        $shipconfig
     * @param CreateOrder                                           $createOrder
     * @param \Magento\Sales\Model\Service\InvoiceService           $invoiceService
     * @param \Magento\Framework\DB\Transaction                     $transaction
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \PaysonAB\PaysonCheckout2\Helper\Transaction          $transactionHelper
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $response,
        \PaysonAB\PaysonCheckout2\Model\Api\PaysonApi $paysonApi,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \PaysonAB\PaysonCheckout2\Helper\Data $paysonHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shipconfig,
        \PaysonAB\PaysonCheckout2\Model\Payment\CreateOrder $createOrder,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \PaysonAB\PaysonCheckout2\Helper\Transaction $transactionHelper
    ) {
    
        $this->_invoiceSender = $invoiceSender;
        $this->_url = $url;
        $this->_response = $response;
        $this->_paysonApi = $paysonApi;
        $this->_orderHelper = $orderHelper;
        $this->_paysonHelper = $paysonHelper;
        $this->shipconfig = $shipconfig;
        $this->scopeConfig = $scopeConfig;
        $this->_createOrder = $createOrder;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_transactionHelper = $transactionHelper;
    }

    /**
     * @param $order
     * @throws bool
     */
    public function process($order)
    {
        try {
            $api = $this->_orderHelper->getApi();
            $checkout = $api->GetCheckout($order->getPaysonCheckoutId());
            if ($order->getState() == \Magento\Sales\Model\Order::STATE_COMPLETE || $order->getState() == \Magento\Sales\Model\Order::STATE_CANCELED) {
                return;
            }
            $this->_paysonHelper->log('Order (' . $order->getIncrementId() . ') notified status update: ' . $checkout->status);


            switch ($checkout->status) {
            case 'readyToShip':
                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                break;

            case 'shipped':
                $message = __('Order has been marked as shipped at Payson.');
                $order->addStatusHistoryComment($message);

                // Prepare shipment and save
                if ($order->getId() && $order->canShip()) {
                        $this->_shipmentCreate($order);
                }

                // Crete invoice to complete order
                if ($order->canInvoice()) {
                    $invoice = $this->_invoiceService->prepareInvoice($order);
                    $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                    $invoice->register();
                    $invoice->save();
                    $transactionSave = $this->_transaction->addObject(
                        $invoice
                    )->addObject(
                        $invoice->getOrder()
                    );
                    $transactionSave->save();
                    $this->_invoiceSender->send($invoice);
                    //send notification code
                    $order->addStatusHistoryComment(
                        __('Notified customer about invoice #%1.', $invoice->getId())
                    )
                        ->setIsCustomerNotified(true)
                        ->save();
                }

                break;

            case 'credited':
                $service = $this->_invoiceService->prepareInvoice($order);

                foreach ($order->getInvoiceCollection() as $invoice) {
                    $creditmemo = $service->prepareInvoiceCreditmemo($invoice);
                    $creditmemo->register();
                    $creditmemo->save();

                    $creditmemo->sendEmail();
                    $order->addStatusHistoryComment(__('Notified customer about creditmemo #%s.', $creditmemo->getIncrementId()))
                        ->setIsCustomerNotified(true)
                        ->save();
                }

                break;

            case 'paidToAccount':
                $message = __('Money have been paid to account by Payson.');
                $order->addStatusHistoryComment($message);
                $order = $this->_transactionHelper->setTransaction($order);

                break;
            case 'expired':
                $message = __('The payment was expired by Payson.');
                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
                $order->addStatusHistoryComment($message);

                break;

            case 'canceled':
                $message = __('Order was canceled at Payson.');
                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
                $order->addStatusHistoryComment($message);

                break;

            case 'denied':
                $message = __('The order was denied by Payson.');
                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
                $order->addStatusHistoryComment($message);

                break;

            default:
                break;
            }
            $order->save();
        } catch (\Exception $e) {
            $this->_paysonHelper->log($e->getMessage());
        }
    }

    /**
     * @param $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _shipmentCreate($order)
    {

        // Initialize the order shipment object
        $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
        $shipment = $convertOrder->toShipment($order);

        // Loop through order items
        foreach ($order->getAllItems() AS $orderItem) {
            // Check if order item has qty to ship or is virtual
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qtyShipped = $orderItem->getQtyToShip();

            // Create shipment item with qty
            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

            // Add shipment item to shipment
            $shipment->addItem($shipmentItem);
        }

        // Register shipment
        $shipment->register();

        $shipment->getOrder()->setIsInProcess(true);

        try {
            // Save created shipment and order
            $shipment->save();
            $shipment->getOrder()->save();

            // Send email
            $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                ->notify($shipment);

            $shipment->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }
}