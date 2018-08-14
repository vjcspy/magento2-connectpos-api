<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SM\XRetail\Setup;

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
        if (version_compare($context->getVersion(), '0.0.8', '<')) {
            $this->addOutletTable($setup, $context);
            $this->addRegister($setup, $context);
        }
        if (version_compare($context->getVersion(), '0.1.0', '<')) {
            $this->addReceiptTable($setup, $context);
        }
        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            $this->addUserOrderCounterTable($setup, $context);
        }
        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->createRoleTable($setup, $context);
            $this->createPermissionTable($setup, $context);
            $this->definePermission($setup);
        }
        if (version_compare($context->getVersion(), '0.1.6', '<')) {
            $this->addReceiptTable($setup, $context);
            $this->dummyReceipt($setup);
        }
        if (version_compare($context->getVersion(), '0.1.7', '<')) {
            $this->dummyReceipt($setup);
        }
        if (version_compare($context->getVersion(), '0.2.3', '<')) {
            $this->modifyColumnHeaderReceipt($setup);
        }
        if (version_compare($context->getVersion(), '0.2.4', '<')) {
            $this->addNewColumnMapForOutlet($setup);
        }
        if (version_compare($context->getVersion(), '0.2.5', '<')) {
            $this->addNewColumnCustomDateTimeReceipt($setup);
            $this->updateDefaultDateTimeReceipt($setup);
        }
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    protected function addOutletTable(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('sm_xretail_outlet'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_xretail_outlet')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Demo Title'
        )->addColumn(
            'warehouse_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'WareHouse ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'Store ID'
        )->addColumn(
            'cashier_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Cashier Ids'
        )->addColumn(
            'enable_guest_checkout',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Enable Guest Checkout'
        )->addColumn(
            'tax_calculation_based_on',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            ['nullable' => false,],
            'Tax Calculation Based On'
        )->addColumn(
            'paper_receipt_template_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            5,
            ['nullable' => false,],
            "Paper Receipt's template"
        )->addColumn(
            'street',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Street'
        )->addColumn(
            'city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'City'
        )->addColumn(
            'country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false,],
            'Region Id'
        )->addColumn(
            'region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false,],
            'Region Id'
        )->addColumn(
            'postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false,],
            'Postcode'
        )->addColumn(
            'telephone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            ['nullable' => false,],
            'Telephone'
        )->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,],
            'Creation Time'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,],
            'Modification Time'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is Active'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     */
    protected function addRegister(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('sm_xretail_register'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_xretail_register')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'outlet_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Demo Title'
        )->addColumn(
            'creation_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,],
            'Creation Time'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,],
            'Modification Time'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is Active'
        )->addColumn(
            'is_print_receipt',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is Always Print Receipt'
        );
        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addForeignKey(
            $installer->getFkName('id', 'outlet_id', $installer->getTable('sm_xretail_outlet'), 'id'),
            $installer->getTable('sm_xretail_register'),
            'outlet_id',
            $installer->getTable('sm_xretail_outlet'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $installer->endSetup();
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     */
    protected function addUserOrderCounterTable(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('sm_xretail_userordercounter'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_xretail_userordercounter')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'user_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'User ID'
        )->addColumn(
            'outlet_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'Outlet ID'
        )->addColumn(
            'register_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'Outlet ID'
        )->addColumn(
            'order_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'unsigned' => true,],
            'Order Count'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     */
    protected function addReceiptTable(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('sm_xretail_receipt'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_xretail_receipt')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'logo_image_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false,],
            'Logo image status'
        )->addColumn(
            'logo_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false,],
            'Logo Url'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Name'
        )->addColumn(
            'footer_image_status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false,],
            'Logo image status'
        )->addColumn(
            'footer_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false,],
            'Footer Url'
        )->addColumn(
            'header',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Header'
        )->addColumn(
            'footer',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Footer'
        )->addColumn(
            'customer_info',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            5,
            ['nullable' => false,],
            'Customer Info'
        )->addColumn(
            'font_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            5,
            ['nullable' => false,],
            'Font Type'
        )->addColumn(
            'barcode_symbology',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            ['nullable' => false,],
            'Barcode Symbology'
        )->addColumn(
            'row_total_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false,],
            'Row total Incl tax'
        )->addColumn(
            'subtotal_incl_tax',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false,],
            'Subtotal Incl Tax'
        )->addColumn(
            'enable_barcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false,],
            'Enable Barcode'
        )->addColumn(
            'enable_power_text',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false,],
            'Enable Power text'
        )->addColumn(
            'order_info',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Order Info'
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
        )->addColumn(
            'is_default',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false,],
            'default receipt'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    protected function createRoleTable(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        //START table setup
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_role')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false,],
            'Demo Title'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,],
            'Creation Time'
        )->addColumn(
            'updated_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,],
            'Modification Time'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is Active'
        );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }

    protected function createPermissionTable(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        //START table setup
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_permission')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'role_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true,],
            'Role ID'
        )->addColumn(
            'group',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false,],
            'Group'
        )->addColumn(
            'permission',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false,],
            'Permission'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,],
            'Creation Time'
        )->addColumn(
            'updated_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,],
            'Modification Time'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is Active'
        );
        $installer->getConnection()->createTable($table);
        $installer->getConnection()->addForeignKey(
            $installer->getFkName('id', 'role_id', $installer->getTable('sm_role'), 'id'),
            $installer->getTable('sm_permission'),
            'role_id',
            $installer->getTable('sm_role'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->endSetup();
    }

    public function definePermission(SchemaSetupInterface $setup) {
        $roleTable       = $setup->getTable('sm_role');
        $permissionTable = $setup->getTable('sm_permission');
        $setup->getConnection()->truncateTable($roleTable);
        $setup->getConnection()->truncateTable($permissionTable);

        $setup->getConnection()->insertArray(
            $roleTable,
            [
                'name',
            ],
            [
                [
                    'name' => "Admin",
                ],
                [
                    'name' => "Manager",
                ],
                [
                    'name' => "Accountant",
                ],
                [
                    'name' => "Cashier",
                ]
            ]
        );
    }

    protected function dummyReceipt(SchemaSetupInterface $setup) {
        $receiptTable = $setup->getTable('sm_xretail_receipt');
        $setup->getConnection()->truncateTable($receiptTable);
        $setup->getConnection()->insertArray(
            $receiptTable,
            [
                'customer_info',
                'order_info',
                'row_total_incl_tax',
                "logo_image_status",
                "footer_image_status",
                'subtotal_incl_tax',
                'header',
                'footer',
                'enable_barcode',
                'barcode_symbology',
                'enable_power_text',
                'name',
                'is_default'
            ],
            [
                [
                    "customer_info"       => "1",
                    "order_info"          => json_encode(
                        [
                            "shipping_address"  => true,
                            "sales_person"      => true,
                            "discount_shipment" => true
                        ],
                        true),
                    "row_total_incl_tax"  => true,
                    "logo_image_status"   => true,
                    "footer_image_status" => true,
                    "subtotal_incl_tax"   => true,
                    "header"              => "<h2>X-POS</h2>",
                    "footer"              => "Thank you for shopping!",
                    "enable_barcode"      => true,
                    "barcode_symbology"   => "CODE128",
                    "enable_power_text"   => true,
                    "name"                => "X-Retail default receipt",
                    "is_default"          => true,
                ]
            ]
        );
    }

    protected function modifyColumnHeaderReceipt(SchemaSetupInterface $setup) {
        $receiptTable = $setup->getTable('sm_xretail_receipt');
        $setup->startSetup();

        $setup->getConnection()->changeColumn(
            $setup->getTable($receiptTable),
            'header',
            'header',
            [
                'type'   => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255000,
                ['nullable' => false,],
                'Header'
            ]
        );

        $setup->endSetup();
    }

    protected function addNewColumnMapForOutlet(SchemaSetupInterface $setup) {
        $installer = $setup;

        $installer->getConnection()->addColumn(
            $installer->getTable('sm_xretail_outlet'),
            'place_id',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 25500,
                'nullable' => false,
                'comment'  => 'Place ID Google Map'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sm_xretail_outlet'),
            'url',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 25500,
                'nullable' => false,
                'comment'  => 'URL Google Map'
            ]
        );

        $setup->endSetup();
    }

    protected function addNewColumnCustomDateTimeReceipt(SchemaSetupInterface $setup) {
        $installer = $setup;

        $installer->getConnection()->addColumn(
            $installer->getTable('sm_xretail_receipt'),
            'day_of_week',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 25500,
                'nullable' => false,
                'comment'  => 'Day of Week'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sm_xretail_receipt'),
            'day_of_month',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 25500,
                'nullable' => false,
                'comment'  => 'Day of Month'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sm_xretail_receipt'),
            'month',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 25500,
                'nullable' => false,
                'comment'  => 'Month'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sm_xretail_receipt'),
            'year',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 25500,
                'nullable' => false,
                'comment'  => 'Year'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sm_xretail_receipt'),
            'time',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 25500,
                'nullable' => false,
                'comment'  => 'Time'
            ]
        );

        $setup->endSetup();
    }

    protected function updateDefaultDateTimeReceipt(SchemaSetupInterface $setup) {
        $installer = $setup;

        $installer->getConnection()->update(
            $installer->getTable('sm_xretail_receipt'),
            [
                'day_of_week'  => 'dddd',
                'day_of_month' => 'Do',
                'month'        => 'MMM',
                'year'         => 'YYYY',
                'time'         => 'h:mm a',
            ],
            ['day_of_week = ?' => null]
        );
    }
}
