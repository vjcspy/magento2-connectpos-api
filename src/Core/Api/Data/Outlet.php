<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 15/12/2016
 * Time: 17:11
 */

namespace SM\Core\Api\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class Outlet extends ApiDataAbstract {

    public function getId() {
        return $this->getData('id');
    }

    public function getName() {
        return $this->getData('name');
    }

    public function getStoreId() {
        return $this->getData('store_id');
    }

    public function getIsActive() {
        return $this->getData('is_active') == 1;
    }

    public function getWarehouseId() {
        return $this->getData('warehouse_id');
    }

    public function getRegisters() {
        return $this->getData('registers');
    }

    public function getCashierIds() {
        if (is_string($this->getData('cashier_ids'))) {
            return json_decode($this->getData('cashier_ids'), true);
        }
        else {
            return [];
        }
    }

    public function getEnableGuestCheckout() {
        return $this->getData('enable_guest_checkout') == 1;
    }

    public function getTaxCalculationBasedOn() {
        return $this->getData('tax_calculation_based_on');
    }

    public function getPaperReceiptTemplateId() {
        return $this->getData('paper_receipt_template_id');
    }

    public function getStreet() {
        return $this->getData('street');
    }

    public function getCity() {
        return $this->getData('city');
    }

    public function getCountryId() {
        return $this->getData('country_id');
    }

    public function getRegionId() {
        return $this->getData('region_id');
    }

    public function getPostcode() {
        return $this->getData('postcode');
    }

    public function getTelephone() {
        return $this->getData('telephone');
    }
}