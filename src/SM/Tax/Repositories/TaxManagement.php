<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 02/11/2016
 * Time: 11:41
 */

namespace SM\Tax\Repositories;


use SM\Core\Api\Data\TaxClass;
use SM\Core\Api\Data\TaxRate;
use SM\Core\Model\DataObject;
use SM\Tax\Model\ResourceModel\Calculation;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class TaxManagement
 *
 * @package SM\Tax\Repositories
 */
class TaxManagement extends ServiceAbstract {

    /**
     * @var \SM\Tax\Model\ResourceModel\Calculation
     */
    protected $taxCalculation;
    /**
     * @var \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory
     */
    protected $taxClassCollectionFactory;

    /**
     * TaxManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface    $requestInterface
     * @param \SM\XRetail\Helper\DataConfig              $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \SM\Tax\Model\ResourceModel\Calculation    $taxCalculation
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory
    ) {
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
        $this->taxCalculation            = $taxCalculation;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     */
    public function getTaxRatesData() {
        if ($this->getSearchCriteria()->getData('currentPage') > 1)
            return $this->getSearchResult()->setItems([])->getOutput();

        return $this->loadTaxRates()->getOutput();
    }

    /**
     * @return \SM\Core\Api\SearchResult
     */
    public function loadTaxRates() {
        $rates = $this->taxCalculation->getRates();
        $items = [];
        foreach ($rates as $rate) {
            $xrate = new TaxRate();
            $xrate->addData($rate);
            $items[] = $xrate;
        }

        return $this->getSearchResult()
                    ->setItems($items)
                    ->setLastPageNumber(1)
                    ->setTotalCount(count($items));
    }

    public function getTaxClassData() {
        $items      = [];
        $collection = $this->getTaxClassCollection($this->getSearchCriteria());
        if ($collection->getLastPageNumber() < $this->getSearchCriteria()->getData('currentPage')) {
        }
        else {
            foreach ($collection as $class) {
                $g = new TaxClass();
                /** @var \Magento\Tax\Model\ClassModel $group */
                $g->addData(
                    [
                        'class_id'   => $class->getId(),
                        'class_name' => $class->getData('class_name'),
                        'class_type' => $class->getData('class_type')
                    ]);
                $items[] = $g;
            }
        }

        return $this->getSearchResult()
                    ->setSearchCriteria($this->getSearchCriteria())
                    ->setItems($items)
                    ->setTotalCount($collection->getSize())
                    ->getOutput();
    }

    /**
     * @param $searchCriteria
     *
     * @return \Magento\Tax\Model\ResourceModel\TaxClass\Collection
     */
    protected function getTaxClassCollection($searchCriteria) {
        /** @var \Magento\Tax\Model\ResourceModel\TaxClass\Collection $collection */
        $collection = $this->taxClassCollectionFactory->create();
        $collection->setCurPage(is_nan($searchCriteria->getData('currentPage')) ? 1 : $searchCriteria->getData('currentPage'));
        $collection->setPageSize(
            is_nan($searchCriteria->getData('pageSize')) ? DataConfig::PAGE_SIZE_LOAD_CUSTOMER : $searchCriteria->getData('pageSize')
        );

        return $collection;
    }
}