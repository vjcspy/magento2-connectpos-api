<?php
namespace SM\XRetail\Model;
class Receipt extends \Magento\Framework\Model\AbstractModel implements ReceiptInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'sm_xretail_receipt';

    protected function _construct()
    {
        $this->_init('SM\XRetail\Model\ResourceModel\Receipt');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
