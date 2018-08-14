<?php
namespace SM\XRetail\Model;
class Permission extends \Magento\Framework\Model\AbstractModel implements PermissionInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'sm_xretail_permission';

    protected function _construct()
    {
        $this->_init('SM\XRetail\Model\ResourceModel\Permission');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
