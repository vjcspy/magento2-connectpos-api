<?php
namespace SM\Shift\Model\ResourceModel\ShiftInOut;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\Shift\Model\ShiftInOut','SM\Shift\Model\ResourceModel\ShiftInOut');
    }
}
