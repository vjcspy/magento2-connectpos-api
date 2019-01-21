<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 19/12/2016
 * Time: 10:43
 */

namespace SM\Core\Api\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class RetailConfig extends ApiDataAbstract {

    public function getKey() {
        return $this->getData('key');
    }

    public function getValue() {
        return $this->getData('value');
    }
}