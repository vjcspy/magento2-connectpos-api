<?php

namespace SM\Sales\Model\ResourceModel;

class OrderSyncError extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct() {
        $this->_init('sm_order_sync_error', 'id');
    }
}
