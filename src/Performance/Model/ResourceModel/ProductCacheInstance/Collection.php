<?php
namespace SM\Performance\Model\ResourceModel\ProductCacheInstance;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\Performance\Model\ProductCacheInstance','SM\Performance\Model\ResourceModel\ProductCacheInstance');
    }
}
