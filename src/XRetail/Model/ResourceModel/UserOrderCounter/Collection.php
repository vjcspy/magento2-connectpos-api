<?php
namespace SM\XRetail\Model\ResourceModel\UserOrderCounter;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\XRetail\Model\UserOrderCounter','SM\XRetail\Model\ResourceModel\UserOrderCounter');
    }
}
