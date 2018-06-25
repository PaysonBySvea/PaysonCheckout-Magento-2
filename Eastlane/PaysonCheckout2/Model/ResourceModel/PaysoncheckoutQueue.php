<?php
namespace Eastlane\PaysonCheckout2\Model\ResourceModel;
/**
 * Class PaysoncheckoutQueue
 *
 * @package Eastlane\PaysonCheckout2\Model\ResourceModel
 */
class PaysoncheckoutQueue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('paysoncheckout_queue', 'paysoncheckout_queue_id');
    }
}
