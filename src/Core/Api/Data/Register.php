<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 17/12/2016
 * Time: 15:16
 */

namespace SM\Core\Api\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class Register extends ApiDataAbstract {

    public function getId() {
        return $this->getData('id');
    }

    public function getName() {
        return $this->getData('name');
    }

    public function getOutletName() {
        return $this->getData('outlet_name');
    }

    public function getOutletId() {
        return $this->getData('outlet_id');
    }

    public function getIsActive() {
        return $this->getData('is_active') == 1;
    }

    public function getIsPrintReceipt() {
        return $this->getData('is_print_receipt');
    }

}