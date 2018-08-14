<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 18/03/2017
 * Time: 11:50
 */

namespace SM\Shift\Helper;


/**
 * Class Data
 *
 * @package SM\Shift\Helper
 */
class Data {

    /**
     * @var \SM\Shift\Model\ResourceModel\Shift\CollectionFactory
     */
    private $shiftCollectionFactory;

    private $_shift = [];

    /**
     * Data constructor.
     *
     * @param \SM\Shift\Model\ResourceModel\Shift\CollectionFactory $collectionFactory
     */
    public function __construct(\SM\Shift\Model\ResourceModel\Shift\CollectionFactory $collectionFactory) {
        $this->shiftCollectionFactory = $collectionFactory;
    }

    /**
     * @return \SM\Shift\Model\ResourceModel\Shift\Collection
     */
    protected function getShiftCollection() {
        return $this->shiftCollectionFactory->create();
    }

    /**
     * @param $outletId
     * @param $registerId
     *
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    public function getShiftOpening($outletId, $registerId) {
        if (is_null($outletId) || is_null($registerId))
            throw new \Exception("Must define required data");

        if (!isset($this->_shift[$outletId . "|" . $registerId])) {
            /** @var \SM\Shift\Model\ResourceModel\Shift\Collection $collection */
            $collection = $this->shiftCollectionFactory->create();
            $collection->addFieldToFilter('outlet_id', $outletId)
                       ->addFieldToFilter('register_id', $registerId)
                       ->addFieldToFilter('is_open', 1);
            $this->_shift[$outletId . "|" . $registerId] = $collection->getFirstItem();
        }

        return $this->_shift[$outletId . "|" . $registerId];
    }
}