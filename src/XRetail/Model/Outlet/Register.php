<?php
namespace SM\XRetail\Model\Outlet;

class Register extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

    const CACHE_TAG = 'sm_xretail_register';

    protected function _construct() {
        $this->_init('SM\XRetail\Model\ResourceModel\Outlet\Register');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
