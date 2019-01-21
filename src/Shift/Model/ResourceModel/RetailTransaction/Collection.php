<?php
namespace SM\Shift\Model\ResourceModel\RetailTransaction;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\Shift\Model\RetailTransaction','SM\Shift\Model\ResourceModel\RetailTransaction');
    }
}
