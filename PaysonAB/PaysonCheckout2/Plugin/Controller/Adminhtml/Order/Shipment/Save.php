<?php
namespace PaysonAB\PaysonCheckout2\Plugin\Controller\Adminhtml\Order\Shipment;

use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save as Subject;

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
     * @var \PaysonAB\PaysonCheckout2\Helper\Data (Deprecated)
     * @var \PaysonAB\PaysonCheckout2\Helper\DataLogger
     */
    protected $_paysonHelper;
    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
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
     * @var \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueueFactory
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
     * @param \PaysonAB\PaysonCheckout2\Helper\DataLogger           $paysonHelper
     * @param \PaysonAB\PaysonCheckout2\Helper\Order                $orderHelper
     * @param \Magento\Sales\Model\Service\InvoiceService           $invoiceService
     * @param \Magento\Framework\DB\Transaction                     $transaction
     * @param \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueueFactory   $paysoncheckoutQueue
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \PaysonAB\PaysonCheckout2\Model\Config                $paysonConfig
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \PaysonAB\PaysonCheckout2\Helper\DataLogger $paysonHelper,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueueFactory $paysoncheckoutQueue,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \PaysonAB\PaysonCheckout2\Model\Config $paysonConfig
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
     * @param Subject $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(Subject $subject, $result)
    {
        try {
            if($this->paysonConfig->isEnabled()) {
                $orderId = $this->_request->getParam('order_id');
                $order = $this->_orderRepository->get($orderId);

                if (($order->getPayment()->getMethodInstance()->getCode() == \PaysonAB\PaysonCheckout2\Model\Paysoncheckout2ConfigProvider::CHECKOUT_CODE)) {

                    $api = $this->_orderHelper->getApi();
                    $checkoutId = $order->getData(\PaysonAB\PaysonCheckout2\Model\ConfigInterface::CHECKOUT_ID_COLUMN);
                    $checkout = $api->GetCheckout($checkoutId);
                    if($this->paysonConfig->getCaptureInMagento()) {
                        if ($checkout->status === 'readyToShip') {
                            $api->ShipCheckout($checkout);
                            /* Order information save in payson table start */
                            $model = $this->_paysoncheckoutQueue->create();
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

            return $result;

        } catch (\Exception $e) {
            $this->_paysonHelper->debug($e->getMessage());
        }
    }
}