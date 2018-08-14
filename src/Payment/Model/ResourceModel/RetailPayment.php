<?php
namespace SM\Payment\Model\ResourceModel;

class RetailPayment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct() {
        $this->_init('sm_payment', 'id');
    }
}
