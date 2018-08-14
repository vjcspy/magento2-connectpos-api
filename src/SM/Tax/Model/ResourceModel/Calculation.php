<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 02/11/2016
 * Time: 11:42
 */

namespace SM\Tax\Model\ResourceModel;


class Calculation extends \Magento\Tax\Model\ResourceModel\Calculation {

    public function getRates() {

        // Make SELECT and get data
        $select = $this->getConnection()->select();
        $select->from(
            ['main_table' => $this->getMainTable()],
            [
                'tax_calculation_id',
                'tax_calculation_rate_id',
                'tax_calculation_rule_id',
                'customer_tax_class_id',
                'product_tax_class_id'
            ]
        );
        //$ifnullTitleValue   = $this->getConnection()->getCheckSql(
        //    'title_table.value IS NULL',
        //    'rate.code',
        //    'title_table.value'
        //);
        $ruleTableAliasName = $this->getConnection()->quoteIdentifier('rule.tax_calculation_rule_id');
        $select->join(
            ['rule' => $this->getTable('tax_calculation_rule')],
            $ruleTableAliasName . ' = main_table.tax_calculation_rule_id',
            ['rule.priority', 'rule.position', 'rule.calculate_subtotal']
        )->join(
            ['rate' => $this->getTable('tax_calculation_rate')],
            'rate.tax_calculation_rate_id = main_table.tax_calculation_rate_id',
            [
                'value' => 'rate.rate',
                'rate.tax_country_id',
                'rate.tax_region_id',
                'rate.zip_from',
                'rate.zip_to',
                'rate.tax_postcode',
                'rate.zip_is_range',
                'rate.tax_calculation_rate_id',
                'rate.code'
            ]
        );

        /*FIXME: Not yet support Title*/
        //    ->joinLeft(
        //    ['title_table' => $this->getTable('tax_calculation_rate_title')],
        //    "rate.tax_calculation_rate_id = title_table.tax_calculation_rate_id " .
        //    "AND title_table.store_id = '{$storeId}'",
        //    ['title' => $ifnullTitleValue]
        //)

        $select->order(
            'priority ' . \Magento\Framework\DB\Select::SQL_ASC
        )->order(
            'tax_calculation_rule_id ' . \Magento\Framework\DB\Select::SQL_ASC
        )->order(
            'tax_country_id ' . \Magento\Framework\DB\Select::SQL_DESC
        )->order(
            'tax_region_id ' . \Magento\Framework\DB\Select::SQL_DESC
        )->order(
            'tax_postcode ' . \Magento\Framework\DB\Select::SQL_DESC
        )->order(
            'value ' . \Magento\Framework\DB\Select::SQL_DESC
        );

        $fetchResult = $this->getConnection()->fetchAll($select);
        $filteredRates = [];
        if ($fetchResult) {
            foreach ($fetchResult as $rate) {
                if (!isset($filteredRates[$rate['tax_calculation_rate_id']])) {
                    $filteredRates[$rate['tax_calculation_rate_id']] = $rate;
                }
            }
        }

        return $fetchResult;
    }
}