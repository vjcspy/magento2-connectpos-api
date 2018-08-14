<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 27/11/2016
 * Time: 15:25
 */

namespace SM\Core\Api\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class CountryRegion extends ApiDataAbstract {

    public function getId() {
        return $this->getData('country_id');
    }

    public function getName() {
        return $this->getData('name');
    }

    public function getRegions() {
        return $this->getData('regions');
    }
}