<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SM\Payment\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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
        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->createPaymentTable($setup, $context);
        }
        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $this->dummyPayment($setup);
        }
        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            $this->addRpAndGcPayment($setup);
            $this->addPaypalPayment($setup);
        }
        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            $this->updateCashPaymentData($setup);
            $this->addRoundingCashPayment($setup);
            $this->addIzettlePayment($setup);
        }
        if (version_compare($context->getVersion(), '0.1.5', '<')) {
            $this->addPaymentApp($setup);
        }
        if (version_compare($context->getVersion(), '0.1.5', '<')) {
            $this->addRefundToGiftCard($setup);
        }
        if (version_compare($context->getVersion(), '0.1.6', '<')) {
            $this->addCardKnoxPayment($setup);
        }
    }

    protected function createPaymentTable(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('sm_payment'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('sm_payment')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            25,
            ['nullable' => true, 'unsigned' => true,],
            'Outlet Id'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'unsigned' => true,],
            'Title'
        )->addColumn(
            'payment_data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'unsigned' => true,],
            'Data'
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
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is Active'
        )->addColumn(
            'is_dummy',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Is Dummy'
        )->addColumn(
            'allow_amount_tendered',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1',],
            'Allow Amount Tendered'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    protected function dummyPayment(SchemaSetupInterface $setup) {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->truncateTable($paymentTable);
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type'         => "cash",
                    'title'        => "Cash",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type'         => "tyro",
                    'title'        => "Tyro Gateway",
                    'is_dummy'     => 0,
                    'payment_data' => json_encode(['mid' => 'provided by Tyro', 'tid' => 'provided by Tyro', 'api_key' => 'provided by Tyro']),
                ],
                [
                    'type'         => "credit_card",
                    'title'        => "Credit card",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type'         => "credit_card",
                    'title'        => "Debit card",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type'         => "credit_card",
                    'title'        => "Visa card",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ]
            ]
        );
    }

    protected function addRpAndGcPayment(SchemaSetupInterface $setup) {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type'         => \SM\Payment\Model\RetailPayment::GIFT_CARD_PAYMENT_TYPE,
                    'title'        => "GiftCard",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type'         => \SM\Payment\Model\RetailPayment::REWARD_POINT_PAYMENT_TYPE,
                    'title'        => "RewardPoint",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([]),
                ],
            ]
        );
    }

    protected function addPaypalPayment(SchemaSetupInterface $setup) {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type'         => \SM\Payment\Model\RetailPayment::PAYPAL_PAYMENT_TYPE,
                    'title'        => "Paypal",
                    'is_dummy'     => 0,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }


    protected function updateCashPaymentData(SchemaSetupInterface $setup) {
        $paymentTable = $setup->getTable('sm_payment');
        $cash_payment_data = json_encode(['round_to' => '0.01_cash_denomination', 'rounding_rule' => 'round_midpoint_down']);
        $setup->getConnection()->update(
            $paymentTable,
            ['payment_data' => $cash_payment_data],
            ['type = ?'     => "cash"]
        );
    }

    protected function addRoundingCashPayment(SchemaSetupInterface $setup) {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type'         => \SM\Payment\Model\RetailPayment::ROUNDING_CASH,
                    'title'        => "Cash Rounding",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }

    protected function addIzettlePayment(SchemaSetupInterface $setup) {

        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type'         => \SM\Payment\Model\RetailPayment::IZETTLE_PAYMENT_TYPE,
                    'title'        => "iZettle",

                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }

    protected function addRefundToGiftCard(SchemaSetupInterface $setup) {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type'         => \SM\Payment\Model\RetailPayment::REFUND_GC_PAYMENT_TYPE,
                    'title'        => "Refund To GC",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }


    protected function addPaymentApp(SchemaSetupInterface $setup) {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type'         => \SM\Payment\Model\RetailPayment::PAYMENT_EXPRESS,
                    'title'        => "Payment Express",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type'         => \SM\Payment\Model\RetailPayment::AUTHORIZE_NET,
                    'title'        => "Authorize NET",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type'         => \SM\Payment\Model\RetailPayment::USAEPAY,
                    'title'        => "Usaepay",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ],
                [
                    'type'         => \SM\Payment\Model\RetailPayment::MONERIS,
                    'title'        => "Moneris",
                    'is_dummy'     => 1,
                    'payment_data' => json_encode([])
                ],
            ]
        );
    }

    protected function addCardKnoxPayment(SchemaSetupInterface $setup) {
        $paymentTable = $setup->getTable('sm_payment');
        $setup->getConnection()->insertArray(
            $paymentTable,
            [
                'type',
                'title',
                'is_dummy',
                'payment_data'
            ],
            [
                [
                    'type'         => \SM\Payment\Model\RetailPayment::CARDKNOX,
                    'title'        => "CardKnox",
                    'is_dummy'     => 0,
                    'payment_data' => json_encode(['xKey' => 'provided by CardKnox', 'xSoftwareName' => 'provided by CardKnox', 'xSoftwareVersion' => 'provided by CardKnox'])
                ],
            ]
        );
    }
}
