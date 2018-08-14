<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 10/01/2017
 * Time: 09:50
 */

namespace SM\Core\Api\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class XUserOrderCount extends ApiDataAbstract {

    public function getId() {
        return $this->getData('id');
    }

    public function getOutletId() {
        return $this->getData('outlet_id');
    }

    public function getRegisterId() {
        return $this->getData('register_id');
    }

    public function getUserId() {
        return $this->getData('user_id');
    }

    public function getOrderCount() {
        return $this->getData('order_count');
    }
}