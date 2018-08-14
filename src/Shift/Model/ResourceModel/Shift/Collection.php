<?php
namespace SM\Shift\Model\ResourceModel\Shift;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\Shift\Model\Shift','SM\Shift\Model\ResourceModel\Shift');
    }
}
