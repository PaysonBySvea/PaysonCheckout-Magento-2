<?php
namespace PaysonAB\PaysonCheckout2\Cron;
/**
 * Class DeleteOldResources
 *
 * @package PaysonAB\PaysonCheckout2\Cron
 */
class DeleteOldResources
{
    /**
     * @var \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue
     */
    protected $_paysoncheckoutQueue;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \PaysonAB\PaysonCheckout2\Helper\Order
     */
    protected $_orderHelper;

    /**
     * DeleteOldResources constructor.
     *
     * @param \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue $paysoncheckoutQueue
     * @param \Magento\Framework\App\ResourceConnection           $resource
     * @param \PaysonAB\PaysonCheckout2\Helper\Order              $orderHelper
     */
    public function __construct(
        \PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue $paysoncheckoutQueue,
        \Magento\Framework\App\ResourceConnection $resource,
        \PaysonAB\PaysonCheckout2\Helper\Order $orderHelper
    ) {
        $this->_paysoncheckoutQueue = $paysoncheckoutQueue;
        $this->resource = $resource;
        $this->_orderHelper = $orderHelper;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $paysonCollection = $this->_paysoncheckoutQueue;
        $getQuoteAllIds = $paysonCollection->getCollection()
            ->addFieldToFilter(
                array(
                    'order_id',
                    'quote_id',
                ),
                array(
                    array('order_id' => array('null' => true)),
                    array('quote_id' => array('null' => true)),
                )
            );
        $queueAllIds = $this->_orderStatusCollection();
        $paysonIds = $this->_checkoutIdsExpired();

        /*merge filterd ids*/
        $paysonAllIds = array_unique(array_merge($getQuoteAllIds->getAllIds(), $queueAllIds, $paysonIds));
        if(!empty($paysonAllIds)) {
            /*load and delete paysoncheckout_queue_id*/
            $collection = $paysonCollection->getcollection()
                ->addFieldToFilter('paysoncheckout_queue_id', array('in' => $paysonAllIds));
            try {
                $collection->walk('delete');
            } catch (\Exception $e) {
                $this->_paysonHelper->error($e->getMessage());
            }
        }
    }

    private function _orderStatusCollection()
    {
        $paysonCollection = $this->_paysoncheckoutQueue;

        $collections = $paysonCollection->getCollection();
        $connection  = $this->resource->getConnection();

        $collections->getSelect()->join(
            array('salesOrder'=> $connection->getTableName('sales_order')),
            'main_table.order_id = salesOrder.entity_id', array('salesOrder.status')
        );
        $orderIds = [];
        foreach ($collections as $collection){
            if($collection->getStatus() === \Magento\Sales\Model\Order::STATE_COMPLETE 
                || $collection->getStatus() === \Magento\Sales\Model\Order::STATE_CANCELED 
                || $collection->getStatus() === \Magento\Sales\Model\Order::STATE_CLOSED 
                || $collection->getStatus() === \Magento\Sales\Model\Order::STATUS_FRAUD
            ) {
                $orderIds[] = $collection->getId();
            }
        }
        if(!empty($orderIds)) {
            return $orderIds;
        }
        return $orderIds;
    }

    private function _checkoutIdsExpired()
    {
        $paysonCollection = $this->_paysoncheckoutQueue;
        $collection = $paysonCollection->getcollection();
        $checkoutIds = $collection->getColumnValues('checkout_id');

        $callPaysonApi = $this->_orderHelper->getApi();
        $deleteIds = [];
        foreach ($checkoutIds as $checkoutId)
        {
            // Fetch checkout and set new paydata
            $checkout = $callPaysonApi->GetCheckout($checkoutId);

            if($checkout->status == "denied" || $checkout->status == "expired" || $checkout->status == "canceled" || $checkout->status == "shipped") {
                $deleteIds[] = $checkout->id;
            }
        }
        if(!empty($deleteIds)) {
            $getQueueAllIds = $paysonCollection->getcollection()
                ->addFieldToFilter('checkout_id', array('in' => $deleteIds));
            return $getQueueAllIds->getAllIds();
        }
        return $deleteIds;
    }
}
