<?php
namespace SM\XRetail\Model\ResourceModel\Outlet;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\XRetail\Model\Outlet','SM\XRetail\Model\ResourceModel\Outlet');
    }
}
