<?php

namespace SM\Sales\Model;

class OrderSyncError extends \Magento\Framework\Model\AbstractModel
    implements \SM\Sales\Api\Data\OrderSyncErrorInterface, \Magento\Framework\DataObject\IdentityInterface {

    const CACHE_TAG = 'sm_order_sync_error';

    protected function _construct() {
        $this->_init('SM\Sales\Model\ResourceModel\OrderSyncError');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
