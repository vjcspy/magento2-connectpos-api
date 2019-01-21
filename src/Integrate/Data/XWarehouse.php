<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/10/17
 * Time: 3:22 PM
 */

namespace SM\Integrate\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class XWarehouse extends ApiDataAbstract {

    public function getId() {
        return $this->getData('warehouse_id');
    }

    public function getName() {
        return $this->getData('warehouse_name');
    }

    public function getCode() {
        return $this->getData('warehouse_code');
    }

    public function getEmail() {
        return $this->getData('contact_email');
    }

    public function getTelephone() {
        return $this->getData('telephone');
    }

    public function getStreet1() {
        return $this->getData('street1');
    }

    public function getStreet2() {
        return $this->getData('street2');
    }

    public function getCity() {
        return $this->getData('city');
    }

    public function getCountryId() {
        return $this->getData('country_id');
    }

    public function getRegion() {
        return $this->getData('region');
    }

    public function getRegionId() {
        return $this->getData('region_id');
    }

    public function getPostcode() {
        return $this->getData('postcode');
    }

    public function getIsPrimary() {
        return $this->getData('is_primary') == 1;
    }

    public function getIsActive() {
        return $this->getData('is_active') == 1;
    }

    public function getAdditionData() {
        return $this->getData('addition_data');
    }

    public function getCompany() {
        return $this->getData('company');
    }

    public function getFax() {
        return $this->getData('fax');
    }
}
