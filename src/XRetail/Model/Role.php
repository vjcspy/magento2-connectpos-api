<?php
namespace SM\XRetail\Model;
class Role extends \Magento\Framework\Model\AbstractModel implements RoleInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'sm_xretail_role';

    protected function _construct()
    {
        $this->_init('SM\XRetail\Model\ResourceModel\Role');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
