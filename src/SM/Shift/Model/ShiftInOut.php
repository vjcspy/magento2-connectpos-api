<?php
namespace SM\Shift\Model;

class ShiftInOut extends \Magento\Framework\Model\AbstractModel implements ShiftInOutInterface, \Magento\Framework\DataObject\IdentityInterface {

    const CACHE_TAG = 'sm_shift_shiftinout';

    protected function _construct() {
        $this->_init('SM\Shift\Model\ResourceModel\ShiftInOut');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getInOutData($shiftId) {
        if (!$shiftId)
            throw new \Exception("Please define shift id");
        $collection = $this->getCollection();
        $collection->addFieldToFilter('shift_id', $shiftId);

        $items = [];
        foreach ($collection as $inOut) {
            $items[] = $inOut->getData();
        }

        return $items;
    }
}
