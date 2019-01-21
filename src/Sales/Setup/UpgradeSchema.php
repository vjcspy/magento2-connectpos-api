<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SM\Sales\Setup;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface {

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\State $state
    ) {
        $this->orderFactory = $orderFactory;
        try {
            $state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        }
        catch (LocalizedException $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.1.1', '<')) {
            $this->addRetailDataToOrder($setup, $context);
        }
        if (version_compare($context->getVersion(), '0.1.6', '<')) {
            $this->addOrderSyncErrorTable($setup, $context);
        }
        if (version_compare($context->getVersion(), '0.1.7', '<')) {
            $this->updateRetailToOrder($setup, $context);
            $this->updateRetailStatusOrder($setup, $context);
        }
        if (version_compare($context->getVersion(), '0.2.3', '<')) {
            $this->addXRefNumOrderCardKnox($setup, $context);
        }
        if (version_compare($context->getVersion(), '0.2.4', '<')) {
            $this->addCashierUserInOrder($setup, $context);
        }
    }

    protected function addRetailDataToOrder(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;

        if ($installer->getConnection()->tableColumnExists($installer->getTable('quote'), 'user_id')
            && $installer->getConnection()->tableColumnExists($installer->getTable('quote'), 'retail_has_shipment')) {
            return;
        }

        $installer->getConnection()->dropColumn($installer->getTable('quote'), 'outlet_id');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'outlet_id');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'outlet_id');

        $installer->getConnection()->dropColumn($installer->getTable('quote'), 'retail_id');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'retail_id');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'retail_id');

        $installer->getConnection()->dropColumn($installer->getTable('quote'), 'retail_status');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'retail_status');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'retail_status');

        $installer->getConnection()->dropColumn($installer->getTable('quote'), 'retail_note');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'retail_note');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'retail_note');

        $installer->getConnection()->dropColumn($installer->getTable('quote'), 'retail_has_shipment');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'retail_has_shipment');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'retail_has_shipment');

        $installer->getConnection()->dropColumn($installer->getTable('quote'), 'is_exchange');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'is_exchange');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'is_exchange');

        $installer->getConnection()->dropColumn($installer->getTable('quote'), 'user_id');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'user_id');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'user_id');

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'outlet_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'comment' => 'Outlet id',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'outlet_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'comment' => 'Outlet id',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'outlet_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'comment' => 'Outlet id',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'retail_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'  => '32',
                'comment' => 'Client id',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'retail_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'  => '32',
                'comment' => 'Client id',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'retail_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'  => '32',
                'comment' => 'Client id',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'retail_status',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Client Status',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'retail_status',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Client Status',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'retail_status',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Client Status',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'retail_note',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Retail Note',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'retail_note',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Retail Note',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'retail_note',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Retail Note',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'retail_has_shipment',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Retail Shipment',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'retail_has_shipment',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Retail Shipment',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'retail_has_shipment',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Retail Shipment',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'is_exchange',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Retail Shipment',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'is_exchange',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Retail Shipment',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'is_exchange',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'comment' => 'Retail Shipment',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'user_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Cashier Id',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'user_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Cashier Id',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'user_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Cashier Id',
            ]
        );
        $setup->endSetup();
    }

    protected function addOrderSyncErrorTable(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('sm_order_sync_error'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_order_sync_error')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'retail_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false,],
            'retail_id'
        )->addColumn(
            'outlet_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false,],
            'retail_id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false,],
            'retail_id'
        )->addColumn(
            'message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false,],
            'error'
        )->addColumn(
            'order_offline',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false,],
            'Order offline data'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,],
            'Creation Time'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,],
            'Modification Time'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    protected function updateRetailToOrder(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->getConnection()->dropColumn($installer->getTable('quote'), 'register_id');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'register_id');
        $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'register_id');

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'register_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'comment' => 'Register id',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'register_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'comment' => 'Register id',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'register_id',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'comment' => 'Register id',
            ]
        );
    }

    protected $newOrderStatus
        = [
            "1" => 13, //Partially Paid - Shipped
            "2" => 12, //Partially Paid - Not Shipped
            "3" => 11, //Partially Paid

            "4" => 33, //Partially Refund - Shipped
            "5" => 32, //Partially Refund - Not Shipped
            "6" => 31, //Partially Refund

            "7" => 40, //Fully Refund

            "8"  => 53, //Exchange - Shipped
            "9"  => 52, //Exchange - Not Shipped
            "10" => 51, //Exchange

            "11" => 23, //Complete - Shipped
            "12" => 22, //Complete - Not Shipped
            "13" => 21, //Complete
        ];

    protected function updateRetailStatusOrder(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();

        $collection = $this->orderFactory->create()->getCollection();
        $collection->addFieldToFilter('retail_status', ['notnull' => true]);
        $collection->addFieldToFilter('retail_status', ['lteq' => 13]);

        foreach ($collection as $order) {
            $retail_status = $order->getRetailStatus();
            if ($retail_status) {
                $order->setData('retail_status', $this->newOrderStatus[$retail_status]);
                $order->save();
            }
        }

        $installer->endSetup();
    }

    protected function addXRefNumOrderCardKnox(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;

        if ($installer->getConnection()->tableColumnExists($installer->getTable('quote'), 'xRefNum')) {
            $installer->getConnection()->dropColumn($installer->getTable('quote'), 'xRefNum');
        }
        if ($installer->getConnection()->tableColumnExists($installer->getTable('sales_order'), 'xRefNum')) {
            $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'xRefNum');
        }
        if ($installer->getConnection()->tableColumnExists($installer->getTable('sales_order_grid'), 'xRefNum')) {
            $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'xRefNum');
        }

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'xRefNum',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'xRefNum',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'xRefNum',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'xRefNum',
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'xRefNum',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'xRefNum',
            ]
        );
    }

    protected function addCashierUserInOrder(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        if ($installer->getConnection()->tableColumnExists($installer->getTable('quote'), 'sm_seller_ids')) {
            $installer->getConnection()->dropColumn($installer->getTable('quote'), 'sm_seller_ids');
        }
            $installer->getConnection()->addColumn(
                $installer->getTable('quote'),
                'sm_seller_ids',
                [
                    'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'comment' => 'Seller Ids',
                ]
            );

        if ($installer->getConnection()->tableColumnExists($installer->getTable('sales_order'), 'sm_seller_ids')) {
            $installer->getConnection()->dropColumn($installer->getTable('sales_order'), 'sm_seller_ids');
        }
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'sm_seller_ids',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Seller Ids',
            ]
        );


        if ($installer->getConnection()->tableColumnExists($installer->getTable('sales_order_grid'), 'sm_seller_ids')) {
            $installer->getConnection()->dropColumn($installer->getTable('sales_order_grid'), 'sm_seller_ids');
        }
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'sm_seller_ids',
            [
                'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Seller Ids',
            ]
        );
    }
}
