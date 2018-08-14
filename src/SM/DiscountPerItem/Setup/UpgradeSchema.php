<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SM\DiscountPerItem\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface {

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $this->addDiscountPerItemColumn($setup, $context);
        }
    }

    protected function addDiscountPerItemColumn(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->getConnection()->dropColumn($installer->getTable('quote'), 'discount_per_item');
        $installer->getConnection()->dropColumn($installer->getTable('quote'), 'base_discount_per_item');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'discount_per_item');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'base_discount_per_item');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'discount_per_item');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'base_discount_per_item');

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'discount_per_item',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length'  => '12,4',
                'comment' => 'Discount per item',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'base_discount_per_item',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length'   => '12,4',
                'nullable' => true,
                'comment'  => 'Discount per item',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'discount_per_item',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length'   => '12,4',
                'nullable' => true,
                'comment'  => 'Discount per item',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'base_discount_per_item',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length'   => '12,4',
                'nullable' => true,
                'comment'  => 'Discount per item',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'discount_per_item',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length'   => '12,4',
                'nullable' => true,
                'comment'  => 'Discount per item',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'discount_per_item',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length'   => '12,4',
                'nullable' => true,
                'comment'  => 'Discount per item',
            ]
        );

        $setup->endSetup();
    }
}