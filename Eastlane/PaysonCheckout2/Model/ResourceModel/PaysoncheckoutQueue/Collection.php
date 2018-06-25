<?php
namespace Eastlane\PaysonCheckout2\Model\ResourceModel\PaysoncheckoutQueue;
/**
 * Class Collection
 *
 * @package Eastlane\PaysonCheckout2\Model\ResourceModel\PaysoncheckoutQueue
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Eastlane\PaysonCheckout2\Model\PaysoncheckoutQueue',
            'Eastlane\PaysonCheckout2\Model\ResourceModel\PaysoncheckoutQueue'
        );
    }
}
