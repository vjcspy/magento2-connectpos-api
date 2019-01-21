<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 3/5/18
 * Time: 17:01
 */

namespace SM\Payment\Helper;

class PaymentHelper {

    private $paymentCollection;

    public function __construct(
        \SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory $paymentCollection
    ) {
        $this->paymentCollection = $paymentCollection;
    }

    public function getPaymentIdByType($type) {
        $paymentCollection = $this->getPaymentCollection()->addFieldToFilter('type', $type)->getFirstItem();

        return $paymentCollection ? $paymentCollection->getData('id') : null;
    }

    public function getPaymentDataByType($type) {
        $paymentCollection = $this->getPaymentCollection()->addFieldToFilter('type', $type)->getFirstItem();

        return $paymentCollection ? $paymentCollection : null;
    }

    /**
     * @return \SM\Payment\Model\ResourceModel\RetailPayment\Collection
     */
    protected function getPaymentCollection() {
        return $this->paymentCollection->create();
    }
}
