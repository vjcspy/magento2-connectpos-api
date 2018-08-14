<?php
namespace SM\Performance\Model\ResourceModel\RealtimeStorage;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\Performance\Model\RealtimeStorage', 'SM\Performance\Model\ResourceModel\RealtimeStorage');
    }
}
