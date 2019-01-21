<?php
namespace SM\Performance\Model;
class ProductCacheInstance extends \Magento\Framework\Model\AbstractModel implements \SM\Performance\Api\Data\ProductCacheInstanceInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'sm_performance_productcacheinstance';

    protected function _construct()
    {
        $this->_init('SM\Performance\Model\ResourceModel\ProductCacheInstance');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
