<?php

namespace SM\XRetail\Model;

class UserOrderCounter extends \Magento\Framework\Model\AbstractModel
    implements UserOrderCounterInterface, \Magento\Framework\DataObject\IdentityInterface {

    const CACHE_TAG = 'sm_xretail_userordercounter';

    protected function _construct() {
        $this->_init('SM\XRetail\Model\ResourceModel\UserOrderCounter');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getOrderCount() {
        return $this->getData('order_count');
    }

    public function loadOrderCount($outletId, $registerId, $userId) {
        if (!$outletId || !$userId)
            throw new \Exception("Must have param outlet_id and user_id");
        $collection = $this->getCollection();

        return $collection->addFieldToFilter('outlet_id', $outletId)
            // Không filter user bởi vì nếu như thế các user khác nhau sẽ có chung id trên cùng 1 register
            //->addFieldToFilter('user_id', $userId)
                          ->addFieldToFilter('register_id', $registerId)
                          ->getFirstItem();
    }
}
