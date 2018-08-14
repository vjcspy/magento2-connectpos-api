<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 27/11/2016
 * Time: 22:18
 */

namespace SM\Core\Api\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class TaxClass extends ApiDataAbstract {

    public function getId() {
        return $this->getData('class_id');
    }

    public function getName() {
        return $this->getData('class_name');
    }

    public function getType() {
        return $this->getData('class_type');
    }
}