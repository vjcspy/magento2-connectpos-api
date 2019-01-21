<?php
namespace SM\Shipping\Model;

use SM\Shipping\Api\OutletManagementInterface;
use Magento\Framework\DataObject;

class OutletManagement implements OutletManagementInterface
{
    /**
     * @var \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory
     */
    protected $collectionFactory;

    protected $outletFactory;

    /**
     * OfficeManagement constructor.
     *
     * @param \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory $collectionFactory
     * @param \SM\Shipping\Api\Data\OutletInterfaceFactory             $outletFactory
     */
    public function __construct(\SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory $collectionFactory) {
        $this->collectionFactory = $collectionFactory;
    }

    public function fetchOutlets() {
        $result     = [];
        $collection = $this->collectionFactory->create();

        foreach ($collection as $item) {
            if ($item->getData('allow_click_and_collect') === '1') {
                $result[] = [
                    'id'      => $item->getData('id'),
                    'name'     => $item->getData('name'),
                    'address'  => $item->getData('street') . ',' . $item->getData('city') . ',' . $item->getData('country_id'),
                    'location' => $item->getData('lat') . ',' . $item->getData('lng')
                ];
            }
        }
        return $result;
    }
}