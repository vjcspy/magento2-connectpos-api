<?php
namespace SM\Shift\Model;

class RetailTransaction extends \Magento\Framework\Model\AbstractModel
    implements RetailTransactionInterface, \Magento\Framework\DataObject\IdentityInterface {

    const CACHE_TAG = 'sm_retail_transaction';

    protected function _construct() {
        $this->_init('SM\Shift\Model\ResourceModel\RetailTransaction');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
