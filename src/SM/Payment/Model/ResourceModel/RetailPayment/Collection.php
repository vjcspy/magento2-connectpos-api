<?php
namespace SM\Payment\Model\ResourceModel\RetailPayment;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('SM\Payment\Model\RetailPayment','SM\Payment\Model\ResourceModel\RetailPayment');
    }
}
