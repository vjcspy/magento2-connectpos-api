<?php
namespace SM\Shift\Model;
class Shift extends \Magento\Framework\Model\AbstractModel implements ShiftInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'sm_shift_shift';

    protected function _construct()
    {
        $this->_init('SM\Shift\Model\ResourceModel\Shift');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
