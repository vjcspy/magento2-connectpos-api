<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/10/17
 * Time: 3:09 PM
 */

namespace SM\Integrate\Warehouse\Contract;


use Magento\Framework\ObjectManagerInterface;
use SM\Integrate\Data\XWarehouse;

/**
 * Class AbstractWarehouseIntegrate
 *
 * @package SM\Integrate\Warehouse\Contract
 */
abstract class AbstractWarehouseIntegrate {

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \SM\Integrate\Helper\Data
     */
    protected $integrateData;

    /**
     * AbstractWarehouseIntegrate constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \SM\Integrate\Helper\Data                 $integrateData
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \SM\Integrate\Helper\Data $integrateData
    ) {
        $this->integrateData = $integrateData;
        $this->objectManager = $objectManager;
    }

    protected $_transformWarehouseData
        = [
            "warehouse_id"   => "warehouse_id",
            "warehouse_name" => "warehouse_name",
            "warehouse_code" => "warehouse_code",
            "contact_email"  => "contact_email",
            "telephone"      => "telephone",
            "city"           => "city",
            "country_id"     => "country_id",
            "region"         => "region",
            "region_id"      => "region_id",
            "is_active"      => "is_active",
            "is_primary"     => "is_primary",
            "company"        => "company",
            "street1"        => "street1",
            "street2"        => "street2",
            "fax"            => "fax",
        ];

    /**
     * @param $searchCriteria
     *
     * @return \SM\Core\Api\SearchResult
     */
    public function loadWarehouseData($searchCriteria) {
        // TODO: Implement loadWarehouseData() method.
        $searchResult   = new \SM\Core\Api\SearchResult();
        $items          = [];
        $size           = 0;
        $lastPageNumber = 0;
        if ($this->integrateData->isIntegrateWH()) {
            $warehouseCollection = $this->getWarehouseCollection($searchCriteria);
            $size                = $warehouseCollection->getSize();
            $lastPageNumber      = $warehouseCollection->getLastPageNumber();

            if ($warehouseCollection->getLastPageNumber() < $searchCriteria->getData('currentPage')) {

            }
            else {
                foreach ($warehouseCollection as $item) {
                    $_data = new XWarehouse();

                    foreach ($this->_transformWarehouseData as $k => $v) {
                        $_data->setData($k, $item->getData($v));
                    }

                    array_push($items, $_data);
                }
            }
        }

        return $searchResult
            ->setItems($items)
            ->setTotalCount($size)
            ->setLastPageNumber($lastPageNumber);
    }
}
