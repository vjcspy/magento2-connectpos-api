<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 15/12/2016
 * Time: 17:11
 */

namespace SM\Core\Api\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class XPayment extends ApiDataAbstract {

    public function getId() {
        return $this->getData('id');
    }

    public function getType() {
        return $this->getData('type');
    }

    public function getTitle() {
        return $this->getData('title');
    }

    public function getPaymentData() {
        if (is_string($this->getData("payment_data"))) {
            return json_decode($this->getData('payment_data'), true);
        }

        return [];
    }

    public function getCreatedAt() {
        return $this->getData('created_at');
    }

    public function getUpdated_at() {
        return $this->getData('updated_at');
    }

    public function getIsActive() {
        return $this->getData('is_active') == 1;
    }

    public function getIsDummy() {
        return $this->getData('is_dummy') == 1;
    }

    public function getAllowAmountTendered() {
        return $this->getData('allow_amount_tendered') == 1;
    }
}