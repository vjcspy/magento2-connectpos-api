<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 08/11/2016
 * Time: 16:26
 */

namespace SM\Core\Api\Data;


class XSetting extends \SM\Core\Api\Data\Contract\ApiDataAbstract {

    public function getKey() {
        return $this->getData('key');
    }

    public function getValue() {
        return $this->getData('value');
    }

}