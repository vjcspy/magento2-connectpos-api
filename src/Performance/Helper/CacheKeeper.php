<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 5/15/17
 * Time: 3:51 PM
 */

namespace SM\Performance\Helper;

use Magento\Framework\ObjectManagerInterface;


/**
 * Class CacheKeeper
 *
 * @package SM\Performance\Helper
 */
class CacheKeeper {

    /**
     * @var bool
     */
    static $USE_CACHE = true;

    static $IS_PULL_FROM_CACHE = 'IS_PULL_FROM_CACHE';

    /**
     * @var array
     */
    private $_cachedInstance = [];
    /**
     * @var \SM\Performance\Model\ResourceModel\ProductCacheInstance\CollectionFactory
     */
    protected $productCacheInstanceCollectionFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \SM\Performance\Model\ProductCacheInstanceFactory
     */
    private $productCacheInstanceFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \SM\Performance\Model\IzProductFactory
     */
    private $izProductFactory;
    /**
     * @var \Magento\Framework\Setup\SchemaSetupInterface
     */
    private $schemaSetup;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * CacheKeeper constructor.
     *
     * @param \SM\Performance\Model\ResourceModel\ProductCacheInstance\CollectionFactory $productCacheInstanceCollectionFactory
     * @param \Magento\Framework\ObjectManagerInterface                                  $objectManager
     */
    public function __construct(
        \SM\Performance\Model\ResourceModel\ProductCacheInstance\CollectionFactory $productCacheInstanceCollectionFactory,
        \SM\Performance\Model\ProductCacheInstanceFactory $productCacheInstanceFactory,
        ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SM\Performance\Model\IzProductFactory $izProductFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->objectManager                         = $objectManager;
        $this->productCacheInstanceCollectionFactory = $productCacheInstanceCollectionFactory;
        $this->productCacheInstanceFactory           = $productCacheInstanceFactory;
        $this->storeManager                          = $storeManager;
        $this->izProductFactory                      = $izProductFactory;
        $this->resource                              = $resource;
        $this->connection                            = $resource->getConnection();
    }

    /**
     * @param      $storeId
     * @param null $warehouseId
     *
     * @return \SM\Performance\Model\AbstractProductCache
     * @throws \Exception
     */
    public function getInstance($storeId, $warehouseId = null) {
        if ($this->storeManager->getStore()->getId() != $storeId) {
            $this->storeManager->setCurrentStore($storeId);
        }
        $instanceInfo = $this->getCacheInstanceInfo($storeId, $warehouseId);
        if ($instanceInfo === null) {
            $this->buildTable($storeId, $warehouseId);
            $_m = $this->getProductCacheInstanceModel();
            $_m->setData('is_over', 0)
               ->setData('store_id', $storeId)
               ->setData('warehouse_id', $warehouseId)
               ->save();
        }

        return $this->getIzProductModel();
    }

    /**
     * @return \SM\Performance\Model\IzProduct
     */
    protected function getIzProductModel() {
        return $this->izProductFactory->create();
    }

    /**
     * @param $storeId
     * @param $warehouseId
     *
     * @return \SM\Performance\Model\ProductCacheInstance|null
     * @throws \Exception
     */
    public function getCacheInstanceInfo($storeId, $warehouseId = null) {
        $cacheKey = $this->getCacheKey([$storeId, $warehouseId]);
        if (!isset($this->_cachedInstance[$cacheKey])) {
            $collection = $this->getProductCacheInstanceCollection();

            $collection->addFieldToFilter('store_id', $storeId);

            if (!is_null($warehouseId)) {
                $collection->addFieldToFilter('warehouse_id', $warehouseId);
            }

            $instanceInfo = $collection->getFirstItem();

            if ($instanceInfo->getId()) {
                if (!$this->connection->isTableExists($this->getTableName($storeId, $warehouseId))) {
                    throw new \Exception("can not find iz cache table in server, please flush cache");
                }
                $this->_cachedInstance[$cacheKey] = $instanceInfo;
            }
            else {
                $this->_cachedInstance[$cacheKey] = null;
            }
        }

        return $this->_cachedInstance[$cacheKey];
    }

    /**
     * @return \SM\Performance\Model\ResourceModel\ProductCacheInstance\Collection
     */
    protected function getProductCacheInstanceCollection() {
        return $this->productCacheInstanceCollectionFactory->create();
    }

    /**
     * @return \SM\Performance\Model\ProductCacheInstance
     */
    protected function getProductCacheInstanceModel() {
        return $this->productCacheInstanceFactory->create();
    }

    /**
     * @param $arrayKey
     *
     * @return mixed
     */
    public function getCacheKey($arrayKey) {
        return array_reduce(
            $arrayKey,
            function ($carry, $item) {
                $carry .= $item . "|";

                return $carry;
            },
            "");
    }

    /**
     * @return int
     */
    static function getCacheTime() {
        return intval(microtime(true) * 1000);
    }

    /**
     * @param      $storeId
     * @param null $warehouseId
     *
     * @throws \Zend_Db_Exception
     */
    protected function buildTable($storeId, $warehouseId = null) {
        $table = $this->connection->newTable($this->getTableName($storeId, $warehouseId));
        $table
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
                'Product id'
            )
            ->addColumn(
                'data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'unsigned' => true,],
                'Product Data'
            )
            ->addColumn(
                'creation_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Modification Time'
            );
        $table->addIndex(
            $this->resource->getIdxName($this->getTableName($storeId, $warehouseId), ['id']),
            ['id']
        );
        $this->dropCacheTable($storeId, $warehouseId);
        $this->connection->createTable($table);
    }

    public function dropCacheTable($storeId, $warehouseId) {
        $this->connection->dropTable($this->getTableName($storeId, $warehouseId));
    }

    /**
     * @param      $storeId
     * @param null $warehouseId
     *
     * @return string
     */
    protected function getTableName($storeId, $warehouseId = null) {
        return $this->resource->getTableName('iz_product_' . $storeId . '_' . $warehouseId);
    }

    public function deleteEntity($ids) {
        $collection = $this->getProductCacheInstanceCollection();
        foreach ($collection as $cacheInstanceInfo) {
            \SM\Integrate\Model\WarehouseIntegrateManagement::setWarehouseId($cacheInstanceInfo->getData('warehouse_id'));
            try{
                $this->getIzProductModel()
                     ->getCollection()
                     ->addFieldToFilter('id', ['in' => explode(",", $ids)])
                     ->walk('delete');
            }catch (\Exception $e){

            }

        }
    }
}
