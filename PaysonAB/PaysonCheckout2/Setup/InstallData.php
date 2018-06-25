<?php


namespace PaysonAB\PaysonCheckout2\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

     /**
     * @var \Magento\Sales\Model\Order\StatusFactory
     */
    protected $statusFactory;
    /**
     * @param \Magento\Sales\Model\Order\StatusFactory $statusFactory
     */
    public function __construct(\Magento\Sales\Model\Order\StatusFactory $statusFactory)
    {
        $this->statusFactory = $statusFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        
        // add processing status to Payson Processing state
        /**
 * @var \Magento\Sales\Model\Order\Status $status 
*/
        $status = $this->statusFactory->create();
        $status->setData(
            [
            'status' => \PaysonAB\PaysonCheckout2\Model\Payment\CreateOrder::STATE_PAYSON_PROCESSING,
            'label' => 'Payson Processing'
            ]
        )->save();
        $status->assignState(\PaysonAB\PaysonCheckout2\Model\Payment\CreateOrder::STATE_PAYSON_PROCESSING, false, true);
    }
}
