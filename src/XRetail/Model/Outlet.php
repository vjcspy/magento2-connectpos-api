<?php
namespace SM\XRetail\Model;
class Outlet extends \Magento\Framework\Model\AbstractModel implements OutletInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'sm_xretail_outlet';

    protected function _construct()
    {
        $this->_init('SM\XRetail\Model\ResourceModel\Outlet');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
