<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SM\Setting\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface {

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->dummySettingCategories($setup);
            $this->addUseProductOnlineModeSetting($setup);
        }
        if(version_compare($context->getVersion(), '0.0.4', '<')) {
            $this->dummyIntergrateGCExtension($setup);
        }
        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            $this->dummySelectSeller($setup);
        }
    }

    protected function dummySettingCategories(ModuleDataSetupInterface $setup) {
        $configData  = $setup->getTable('core_config_data');
        $setup->getConnection()->insertArray(
            $configData,
            [
                'path',
                'value',
                'scope',
                'scope_id'
            ],
            [
                [
                    'path'     => "xretail/pos/use_large_categories",
                    'value'    => 0,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/display_selected_category",
                    'value'    => 'sub_categories_product',
                    'scope'    => 'default',
                    'scope_id' => 0
                ]
            ]
        );
    }


    protected function dummySelectSeller(ModuleDataSetupInterface $setup) {
        $configData = $setup->getTable('core_config_data');

        $data = [
            'path'     => "xretail/pos/allow_select_seller",
            'value'    => 0,
            'scope'    => 'default',
            'scope_id' => 0
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function dummyIntergrateGCExtension(ModuleDataSetupInterface $setup) {
        $configData = $setup->getTable('core_config_data');
        $data = [
            'path'     => "xretail/pos/integrate_gc",
            'value'    => "none",
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }

    protected function addUseProductOnlineModeSetting(ModuleDataSetupInterface $setup) {
        $configData = $setup->getTable('core_config_data');
        $data = [
            'path'     => "xretail/pos/use_product_online_mode",
            'value'    => 0,
            'scope'    => 'default',
            'scope_id' => 0,
        ];
        $setup->getConnection()->insertOnDuplicate($configData, $data, ['value']);
    }
}
