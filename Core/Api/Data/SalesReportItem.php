<?php

namespace SM\Core\Api\Data;

use SM\Core\Api\Data\Contract\ApiDataAbstract;

class SalesReportItem extends ApiDataAbstract {

    public function getDataReportType() {
        return $this->getData('data_report_type');
    }

    public function getDataReportValue() {
        return $this->getData('data_report_value');
    }

    public function getRevenue() {
        return $this->getData('revenue');
    }

    public function getTotalCost() {
        return $this->getData('total_cost');
    }

    public function getGrossProfit() {
        return $this->getData('gross_profit');
    }

    public function getMargin() {
        return $this->getData('margin');
    }

    public function getTotalTax() {
        return $this->getData('total_tax');
    }

    public function getGrandTotal() {
        return $this->getData('grand_total');
    }

    public function getCartSize() {
        return $this->getData('cart_size');
    }

    public function getCartValue() {
        return $this->getData('cart_value');
    }

    public function getCartValueInclTax() {
        return $this->getData('cart_value_incl_tax');
    }

    public function getCustomerCount() {
        return $this->getData('customer_count');
    }

    public function getDiscountAmount() {
        return $this->getData('discount_amount');
    }

    public function getDiscountPercent() {
        return $this->getData('discount_percent');
    }

    public function getFirstSale() {
        return $this->getData('first_sale');
    }

    public function getItemSold() {
        return $this->getData('item_sold');
    }

    public function getLastSale() {
        return $this->getData('last_sale');
    }

    public function getOrderCount() {
        return $this->getData('order_count');
    }

    public function getReturnPercent() {
        return $this->getData('return_percent');

    }

    public function getReturnCount() {
        return $this->getData('return_count');
    }

    public function getTransactionCount() {
        return $this->getData('transaction_count');
    }

    public function getShippingAmount() {
        return $this->getData('shipping_amount');
    }

    public function getShippingTax() {
        return $this->getData('shipping_tax');
    }

    public function getShippingTaxRefunded() {
        return $this->getData('shipping_tax_refunded');
    }

    public function getSubTotalRefunded() {
        return $this->getData('subtotal_refunded');
    }

    public function getTotalRefunded() {
        return $this->getData('total_refunded');
    }
}