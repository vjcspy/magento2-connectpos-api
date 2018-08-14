<?php

namespace SM\Sales\Model\ResourceModel\OrderSyncError;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected function _construct() {
        $this->_init('SM\Sales\Model\OrderSyncError', 'SM\Sales\Model\ResourceModel\OrderSyncError');
    }
}
