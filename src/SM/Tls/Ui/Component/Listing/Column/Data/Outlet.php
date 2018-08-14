<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 11/21/17
 * Time: 14:24
 */

namespace SM\Tls\Ui\Component\Listing\Column\Data;


use Magento\Framework\Data\OptionSourceInterface;

class Outlet implements OptionSourceInterface {

    private $outletCollectionFactory;

    public function __construct(
        \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory $outletCollectionFactory
    ) {
        $this->outletCollectionFactory = $outletCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray() {
        $result = [];
        foreach ($this->getOutletCollection() as $item) {
            array_push(
                $result,
                [
                    'label' => $item['name'],
                    'value' => $item['id']
                ]);
        }

        return $result;
    }

    /**
     * @return \SM\XRetail\Model\ResourceModel\Outlet\Collection
     */
    protected function getOutletCollection() {
        return $this->outletCollectionFactory->create();
    }
}
