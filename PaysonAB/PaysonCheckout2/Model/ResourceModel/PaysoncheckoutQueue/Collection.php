<?php
namespace PaysonAB\PaysonCheckout2\Model\ResourceModel\PaysoncheckoutQueue;
/**
 * Class Collection
 *
 * @package PaysonAB\PaysonCheckout2\Model\ResourceModel\PaysoncheckoutQueue
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
            'PaysonAB\PaysonCheckout2\Model\PaysoncheckoutQueue',
            'PaysonAB\PaysonCheckout2\Model\ResourceModel\PaysoncheckoutQueue'
        );
    }
}
