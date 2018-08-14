<?php

namespace SM\DiscountWholeOrder\Observer\WholeOrderDiscount;

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 12/10/2016
 * Time: 14:45
 */
class PercentOfProductPriceDiscount extends AbstractWholeOrderDiscountRule {

    /**
     * @param $data
     *
     * @return array
     */
    public function getRule($data) {
        parent::getRule($data);

        return $this->fixCompatibleMage22(
            [
                'rule_id'               => self::RULE_ID,
                'name'                  => 'X-Retail Discount Whole Order',
                'description'           => NULL,
                'from_date'             => '2013-05-03',
                'to_date'               => NULL,
                'uses_per_customer'     => '0',
                'is_active'             => '1',
                'conditions_serialized' => 'a:6:{s:4:"type";s:32:"salesrule/rule_condition_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}',
                'actions_serialized'    => 'a:6:{s:4:"type";s:40:"salesrule/rule_condition_product_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}',
                'stop_rules_processing' => '0',
                'is_advanced'           => '1',
                'product_ids'           => NULL,
                'sort_order'            => '0',
                'simple_action'         => 'by_percent',
                'discount_amount'       => $this->getValue(),
                'discount_qty'          => NULL,
                'discount_step'         => '0',
                'simple_free_shipping'  => '0',
                'apply_to_shipping'     => '0',
                'times_used'            => '2',
                'is_rss'                => '1',
                'coupon_type'           => '1',
                'use_auto_generation'   => '0',
                'uses_per_coupon'       => '0',
            ]);
    }
}