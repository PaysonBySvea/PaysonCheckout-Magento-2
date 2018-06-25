<?php


namespace PaysonAB\PaysonCheckout2\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            $installer->getConnection()->addColumn(
                $installer->getTable('quote'),
                \PaysonAB\PaysonCheckout2\Model\ConfigInterface::CHECKOUT_ID_COLUMN,
                [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Payson checkout 2 Id',
                ]
            );
            
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                \PaysonAB\PaysonCheckout2\Model\ConfigInterface::CHECKOUT_ID_COLUMN,
                [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Payson checkout 2 Id',
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order_item'),
                'payson_item_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Payson item id',
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'payment_reference',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Payment reference',
                ]
            );
        }
        $installer->endSetup();
    }
}
