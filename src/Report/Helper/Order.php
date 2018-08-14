<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/20/17
 * Time: 5:03 PM
 */

namespace SM\Report\Helper;

use Magento\Framework\App\ObjectManager;

class Order extends \Magento\Backend\Helper\Dashboard\Order {

    private $orderCollectionFactory;
    private $_orderItemCollectionFactory;

    /**
     * @var \SM\Region\Model\RegionDependFactory
     */
    protected $regionDepend;

    /**
     * @param \Magento\Framework\App\Helper\Context           $context
     * @param \SM\Report\Model\ResourceModel\Order\Collection $orderCollection
     * @param \SM\Report\Model\ResourceModel\Order\Item\CollectionFactory $salesReportOrderItemCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \SM\Report\Model\ResourceModel\Order\Collection $orderCollection,
        \SM\Report\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \SM\Report\Model\ResourceModel\Order\Item\CollectionFactory $salesReportOrderItemCollectionFactory
    ) {
        $this->_orderCollection       = $orderCollection;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->_orderItemCollectionFactory = $salesReportOrderItemCollectionFactory;
        parent::__construct($context, $orderCollection);
    }

    /**
     * The getter function to get the new StoreManager dependency
     *
     * @return \Magento\Store\Model\StoreManagerInterface
     *
     * @deprecated
     */
    private function getStoreManager() {
        if ($this->_storeManager === null) {
            $this->_storeManager = ObjectManager::getInstance()->get('Magento\Store\Model\StoreManagerInterface');
        }

        return $this->_storeManager;
    }

    private function getRegionDependModel() {
        return $this->regionDepend->create();
    }

    /**
     * @return void
     */
    protected function _initCollection() {
        $isFilter = $this->getParam('store_id') || $this->getParam('website_id')
                    || $this->getParam('outlet_id')
                    || $this->getParam('region_id');

        $product_sold = empty($this->getParam('product_sold')) ? false : $this->getParam('product_sold');

        if (!$product_sold) {
            $this->_collection = $this->orderCollectionFactory->create()->prepareSummary(
                $this->getParam('period'),
                $this->getParam('start_date'),
                $this->getParam('end_date'),
                $isFilter);
        } else {
            $this->_collection = $this->_orderItemCollectionFactory->create()->prepareSummary(
                $this->getParam('period'),
                $this->getParam('start_date'),
                $this->getParam('end_date'),
                $isFilter);
        }

        if ($this->getParam('store_id')) {
            $this->_collection->addFieldToFilter('store_id', $this->getParam('store_id'));
        }
        elseif ($this->getParam('website_id')) {
            $storeIds = $this->getStoreManager()->getWebsite($this->getParam('website_id'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', ['in' => implode(',', $storeIds)]);
        }
        elseif ($this->getParam('outlet_id')) {
            $this->_collection->addFieldToFilter('outlet_id', $this->getParam('outlet_id'));
        }
        elseif ($this->getParam('region_id')) {
            $outletsDependRegion = $this->getRegionDependModel()->getRegionData($this->getParam('region_id'));
            $this->_collection->addFieldToFilter('outlet_id', ['in' => implode(',', $outletsDependRegion)]);
        }
        elseif ($this->getParam('product_sku')) {
            $this->_collection->addFieldToFilter('sku', $this->getParam('product_sku'));
        }

        $this->_collection->load();
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setParam($name, $value) {
        $this->_params[$name] = $value;

        return $this;
    }
}