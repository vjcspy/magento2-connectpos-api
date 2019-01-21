<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 28/10/2016
 * Time: 10:24
 */

namespace SM\Store\Repositories;


use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;
use SM\Core\Api\Data\Store as XStore;

/**
 * Class StoreManagement
 *
 * @package SM\Store\Repositories
 */
class StoreManagement extends ServiceAbstract {

    /**
     * @var \Magento\Store\Model\ResourceModel\Store\CollectionFactory
     */
    protected $storeCollectionFactory;
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;
    /**
     * @var \Magento\Framework\Locale\Format
     */
    protected $localFormat;

    /**
     * StoreManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                    $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                              $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface                 $storeManager
     * @param \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Locale\Format $format
    ) {
        $this->localFormat            = $format;
        $this->storeCollectionFactory = $storeCollectionFactory;
        $this->storeFactory           = $storeFactory;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     */
    public function getStoreData() {
        return $this->loadStore($this->getSearchCriteria())->getOutput();
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return \SM\Core\Api\SearchResult
     */
    public function loadStore(\Magento\Framework\DataObject $searchCriteria) {
        if (is_null($searchCriteria) || !$searchCriteria)
            $searchCriteria = $this->getSearchCriteria();

        $this->getSearchResult()->setSearchCriteria($searchCriteria);
        $collection = $this->getStoreCollection($searchCriteria);

        $items = [];
        if ($collection->getLastPageNumber() < $searchCriteria->getData('currentPage')) {
        }
        else {
            foreach ($collection as $store) {
                $xStore = new XStore();

                $xStore->addData($store->getData());

                $baseCurrency = $store->getBaseCurrency();
                $xStore->setData('base_currency', $baseCurrency->getData());

                $currentCurrency = $this->getCurrentCurrencyBaseOnStore($store);
                $xStore->setData('current_currency', ["currency_code" => $currentCurrency]);

                $rate = $baseCurrency->getRate($currentCurrency);
                $xStore->setData('rate', $rate);
                $xStore->setData('price_format', $this->localFormat->getPriceFormat(null, $currentCurrency));

                $items[] = $xStore;
            }
        }

        return $this->getSearchResult()
                    ->setItems($items)
                    ->setLastPageNumber($collection->getLastPageNumber())
                    ->setTotalCount($collection->getSize());
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    protected function getStoreCollection(\Magento\Framework\DataObject $searchCriteria) {
        /** @var \Magento\Store\Model\ResourceModel\Store\Collection $collection */
        $collection = $this->storeCollectionFactory->create();
        $collection->setLoadDefault(true);
        $collection->setCurPage(is_nan($searchCriteria->getData('currentPage')) ? 1 : $searchCriteria->getData('currentPage'));
        $collection->setPageSize(
            is_nan($searchCriteria->getData('pageSize')) ? DataConfig::PAGE_SIZE_LOAD_DATA : $searchCriteria->getData('pageSize')
        );

        return $collection;
    }

    /**
     * @return \Magento\Store\Model\ResourceModel\Store
     */
    protected function getStoreModel() {
        return $this->storeFactory->create();
    }


    /**
     * @param \Magento\Store\Model\Store $store
     *
     * @return mixed|string
     */
    protected function getCurrentCurrencyBaseOnStore(\Magento\Store\Model\Store $store) {
        // try to get currently set code among allowed
        $code = $store->getDefaultCurrencyCode();
        if (in_array($code, $store->getAvailableCurrencyCodes(true))) {
            return $code;
        }

        // take first one of allowed codes
        $codes = array_values($store->getAvailableCurrencyCodes(true));
        if (empty($codes)) {
            // return default code, if no codes specified at all
            return $store->getDefaultCurrencyCode();
        }

        return array_shift($codes);
    }
}