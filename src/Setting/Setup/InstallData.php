<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SM\Setting\Setup;
use \Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface {

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->dummySetting($setup);
        }else if (version_compare($context->getVersion(), '0.0.3', '<')){
            $this->addUseProductOnlineModeSetting($setup);
        }
    }

    protected function dummySetting(ModuleDataSetupInterface $setup) {
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
                    'path'     => "xretail/pos/show_product_by_type",
                    'value'    => json_encode(["simple", "bundle", "configurable", "grouped", "virtual"]),
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/show_product_by_visibility",
                    'value'    => json_encode(["2", "4", "3", "1"]),
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/sort_product_base_on",
                    'value'    => "sku",
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/sort_product_sorting",
                    'value'    => "asc",
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/search_product_attribute",
                    'value'    => json_encode(["type_id", "name", "price", "sku", "id"]),
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/enable_select_custom_sale_tax_class",
                    'value'    => 0,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/sort_category_base_on",
                    'value'    => "position",
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/sort_category_sorting",
                    'value'    => "asc",
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/search_customer_by_attribute",
                    'value'    => json_encode(["last_name", "first_name", "email", "telephone", "id"]),
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/search_order",
                    'value'    => json_encode(["email", "first_name", "last_name", "magento_order_id", "client_order_id", "customer_id", "telephone"]),
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/show_disable_product",
                    'value'    => 1,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/show_outofstock_product",
                    'value'    => 1,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/enable_custom_sale",
                    'value'    => 1,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/allow_split_payment",
                    'value'    => 1,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/display_discount_incl_discount_peritem",
                    'value'    => 1,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/allow_partial_payment",
                    'value'    => 1,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/customer_search_max_result",
                    'value'    => 8,
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/integrate_wh",
                    'value'    => "none",
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/integrate_rp",
                    'value'    => "none",
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
                [
                    'path'     => "xretail/pos/new_customer_default_country",
                    'value'    => "US",
                    'scope'    => 'default',
                    'scope_id' => 0
                ],
            ]
        );
    }
}
