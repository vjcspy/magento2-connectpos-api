<?php
namespace SM\XRetail\Model\ResourceModel\Role;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\XRetail\Model\Role','SM\XRetail\Model\ResourceModel\Role');
    }
}
