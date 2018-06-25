<?php
namespace Eastlane\PaysonCheckout2\Plugin\Controller\Adminhtml\Order\Shipment;

class Save
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $_orderRepository;
    /**
     * @var \Eastlane\PaysonCheckout2\Helper\Data
     */
    protected $_paysonHelper;
    /**
     * @var \Eastlane\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;
    /**
     * @var \Eastlane\PaysonCheckout2\Model\PaysoncheckoutQueue
     */
    protected $_paysoncheckoutQueue;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $_invoiceSender;

    /**
     * Save constructor.
     *
     * @param \Magento\Framework\App\RequestInterface               $request
     * @param \Magento\Sales\Model\OrderRepository                  $orderRepository
     * @param \Eastlane\PaysonCheckout2\Helper\Data                 $paysonHelper
     * @param \Eastlane\PaysonCheckout2\Helper\Order                $orderHelper
     * @param \Magento\Sales\Model\Service\InvoiceService           $invoiceService
     * @param \Magento\Framework\DB\Transaction                     $transaction
     * @param \Eastlane\PaysonCheckout2\Model\PaysoncheckoutQueue   $paysoncheckoutQueue
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Eastlane\PaysonCheckout2\Model\Config                $paysonConfig
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Eastlane\PaysonCheckout2\Helper\Data $paysonHelper,
        \Eastlane\PaysonCheckout2\Helper\Order $orderHelper,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Eastlane\PaysonCheckout2\Model\PaysoncheckoutQueue $paysoncheckoutQueue,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Eastlane\PaysonCheckout2\Model\Config $paysonConfig
    ) {
        $this->_request = $request;
        $this->_orderRepository = $orderRepository;
        $this->_paysonHelper= $paysonHelper;
        $this->_orderHelper = $orderHelper;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->_paysoncheckoutQueue = $paysoncheckoutQueue;
        $this->_invoiceSender = $invoiceSender;
        $this->paysonConfig = $paysonConfig;
    }

    /**
     * @throws bool
     */
    public function afterExecute()
    {
        try {
            if($this->paysonConfig->isEnabled()) {
                $orderId = $this->_request->getParam('order_id');
                $order = $this->_orderRepository->get($orderId);

                if (($order->getPayment()->getMethodInstance()->getCode() == \Eastlane\PaysonCheckout2\Model\Paysoncheckout2ConfigProvider::CHECKOUT_CODE)) {

                    $api = $this->_orderHelper->getApi();
                    $checkoutId = $order->getData(\Eastlane\PaysonCheckout2\Model\ConfigInterface::CHECKOUT_ID_COLUMN);
                    $checkout = $api->GetCheckout($checkoutId);
                    if($this->paysonConfig->getCaptureInMagento()) {
                        if ($checkout->status === 'readyToShip') {
                            $api->ShipCheckout($checkout);
                            /* Order information save in payson table start */
                            $model = $this->_paysoncheckoutQueue;
                            $model->load($checkoutId, 'checkout_id');
                            $model->setStatus($checkout->status);
                            $model->setPaysonResponse($this->_orderHelper->convertToJson($checkout));
                            $model->save();
                            /* Order information save in payson table end */
                        }
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

                }
            }
        } catch (\Exception $e) {
            $this->_paysonHelper->error($e->getMessage());
        }
    }
}