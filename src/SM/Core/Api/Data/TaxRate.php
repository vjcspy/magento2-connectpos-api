<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 02/11/2016
 * Time: 11:52
 */

namespace SM\Core\Api\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class TaxRate extends ApiDataAbstract {

    public function getId() {
        return $this->getData('tax_calculation_id');
    }

    public function getTaxCalculationId() {
        return $this->getData('tax_calculation_id');
    }

    public function getTaxCalculationRateId() {
        return $this->getData('tax_calculation_rate_id');
    }

    public function getCustomerTaxClassId() {
        return $this->getData('customer_tax_class_id');
    }

    public function getTaxCalculationRuleId() {
        return $this->getData('tax_calculation_rule_id');
    }

    public function getTaxCountryId() {
        return $this->getData('tax_country_id');
    }

    public function getTaxRegionId() {
        return $this->getData('tax_region_id');
    }

    public function getTaxPostcode() {
        return $this->getData('tax_postcode');
    }

    public function getCode() {
        return $this->getData('code');
    }

    public function getRate() {
        return $this->getData('value');
    }

    public function getZipIsRange() {
        return $this->getData('zip_is_range');
    }

    public function getZipFrom() {
        return $this->getData('zip_from');
    }

    public function getZipTo() {
        return $this->getData('zip_to');
    }

    public function getPriority() {
        return $this->getData('priority');
    }

    public function getPosition() {
        return $this->getData('position');
    }

    public function getCalculateSubtotal() {
        return $this->getData('calculate_subtotal');
    }

    public function getProductTaxClassId() {
        return $this->getData('product_tax_class_id');
    }
}