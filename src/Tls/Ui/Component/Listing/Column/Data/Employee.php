<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 11/21/17
 * Time: 14:47
 */

namespace SM\Tls\Ui\Component\Listing\Column\Data;


use Magento\Framework\Data\OptionSourceInterface;

class Employee implements OptionSourceInterface {

    private $collectionFactory;

    public function __construct(
        \Magento\User\Model\ResourceModel\User\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray() {
        $result = [];
        foreach ($this->getCollection() as $item) {
            array_push(
                $result,
                [
                    'label' => $item['fistname'] . ' ' . $item['lastname'],
                    'value' => $item['user_id']
                ]);
        }

        return $result;
    }

    /**
     * @return \Magento\User\Model\ResourceModel\User\Collection
     */
    protected function getCollection() {
        return $this->collectionFactory->create();
    }
}
