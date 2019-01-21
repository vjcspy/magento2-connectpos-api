<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 7/5/18
 * Time: 10:46
 */

namespace SM\Integrate\Model;


use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use SM\Product\Helper\ProductImageHelper;
use SM\XRetail\Repositories\Contract\ServiceAbstract;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class BmsStockTransfer extends ServiceAbstract {

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    private $productCollectionFactory;

    /**
     * @var \SM\Integrate\Model\WarehouseIntegrateManagement
     */
    private $warehouseIntegrateManagement;
    /**
     * @var \SM\Product\Helper\ProductImageHelper
     */
    private $productImageHelper;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;

    /**
     * BmsStockTransfer constructor.
     *
     * @param \Magento\Framework\App\RequestInterface    $requestInterface
     * @param \SM\XRetail\Helper\DataConfig              $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        CollectionFactory $collectionFactory,
        \SM\Integrate\Model\WarehouseIntegrateManagement $warehouseIntegrateManagement,
        ProductImageHelper $productImageHelper
    ) {
        $this->objectManager                = $objectManager;
        $this->productFactory               = $productFactory;
        $this->productCollectionFactory     = $collectionFactory;
        $this->warehouseIntegrateManagement = $warehouseIntegrateManagement;
        $this->productImageHelper           = $productImageHelper;
        $this->_backendAuthSession          = $backendAuthSession;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     *
     * @param null $searchCriteria
     *
     * @return array
     * @throws \Exception
     */
    public function getListStockTransfer($searchCriteria = null) {
        if ($searchCriteria == null) {
            $searchCriteria = $this->getSearchCriteria();
        }
        $collection = $this->getStockTransferCollection($searchCriteria);
        $collection->setCurPage($searchCriteria->getData('currentPage'));
        $collection->setPageSize($searchCriteria->getData('pageSize'));
        $items = [];
        if ($collection->getLastPageNumber() < $searchCriteria->getData('currentPage')) {
        }
        else {
            foreach ($collection as $transfer) {
                $transferData = $transfer->getData();

                $itemCollection = $this->getStockTransferItemCollection(
                    new DataObject(
                        [
                            'st_transfer_id' => $transfer->getData("st_id")
                        ]));
                $products       = [];
                foreach ($itemCollection as $item) {
                    $product     = $this->getProductModel()->load($item->getData("st_product_id"));
                    $productData = $item->getData();

                    array_push(
                        $products,
                        array_merge(
                            $productData,
                            [
                                'si_id'    => $item->getData('sti_id'),
                                'isDelete' => false,
                                'stock_transfer_qty' => $item->getData('st_qty'),
                                'name'      => $product->getName(),
                                'sku'       => $product->getSku(),
                                'stocks'    => $this->getStockProduct($product->getEntityId()),
                                'image_url' => $this->productImageHelper->getImageUrl($product)
                            ])
                    );
                }

                $transferData['products'] = $products;

                array_push($items, $transferData);
            }
        }

        return $this->getSearchResult()
                    ->setSearchCriteria($searchCriteria)
                    ->setItems($items)
                    ->setTotalCount($collection->getSize())
                    ->setLastPageNumber($collection->getLastPageNumber())
                    ->getOutput();
    }

    public function getProductsForTransferStock() {
        $collection = $this->getProductsForTransferStockCollection($this->getSearchCriteria());
        $items      = [];
        foreach ($collection as $item) {
            $product                   = $this->getProductModel()->load($item->getData("entity_id"));
            $transferData['name']      = $item->getData('name');
            $transferData['sku']       = $item->getData('sku');
            $transferData['st_product_id']       = $item->getData('entity_id');

            $transferData['image_url'] = $this->productImageHelper->getImageUrl($product);
            $stockData                 = $this->getStockProduct($item->getEntityId());
            if (count($stockData) > 0) {
                $transferData['stocks'] = $stockData;
                array_push($items, $transferData);
            }
        }

        return $this->getSearchResult()
                    ->setSearchCriteria($this->getSearchCriteria())
                    ->setItems($items)
                    ->setTotalCount($collection->getSize())
                    ->setLastPageNumber($collection->getLastPageNumber())
                    ->getOutput();
    }


    public function getProductsForTransferStockCollection(DataObject $searchCriteria) {
        $storeId = $searchCriteria->getData('store_id');
        if (is_null($storeId)) {
            throw new \Exception(__('Must have param storeId'));
        }
        else {
            $this->getStoreManager()->setCurrentStore($storeId);
        }
        //$fieldSearch = ['entity_id', 'name', 'sku'];
        $fieldSearch = ['name', 'sku'];
        $collection  = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addStoreFilter($storeId);
        $collection->setPageSize($searchCriteria->getData('pageSize'));
        $collection->addAttributeToFilter('type_id', ['in' => 'simple']);
        $searchValue = $searchCriteria->getData('searchString');
        $searchValue = str_replace(',', ' ', $searchValue);
        foreach (explode(" ", $searchValue) as $value) {
            $_fieldFilters = [];
            foreach ($fieldSearch as $field) {
                $_fieldFilters[] = ['attribute' => $field, 'like' => '%' . $value . '%'];
            }
            $collection->addAttributeToFilter($_fieldFilters, null, 'left');
        }

        return $collection;
    }

    protected function getStockProduct($productId) {
        $stockProduct = $this->warehouseIntegrateManagement->getCurrentIntegrateModel()->getWarehouseItemCollection(
            new DataObject(
                [
                    'wi_product_id' => $productId,
                ]));

        return $stockProduct->toArray();
    }

    protected function getStockTransferCollection(DataObject $searchCriteria) {
        /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection */
        $collection = $this->objectManager->create("\BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Collection");
        $collection->setOrder('st_created_at');
        if( $searchCriteria->getData('warehouse_id')){
            $collection->addFieldToFilter(
                ["st_from", "st_to"],
                [
                    $searchCriteria->getData('warehouse_id'),
                    $searchCriteria->getData('warehouse_id'),
                ]);
        }

        if ($searchCriteria->getData('st_id')) {
            $collection->addFieldToFilter('st_id', $searchCriteria->getData("st_id"));
        }

        if ($searchCriteria->getData('searchString')) {
            $collection->addFieldToFilter('st_reference', ['like' => '%' . trim($searchCriteria->getData('searchString')) . '%']);
        }
        if ($searchCriteria->getData('dateFrom')) {
            $collection->getSelect()
                       ->where('st_created_at >= ?', $searchCriteria->getData('dateFrom'));
        }
        if ($searchCriteria->getData('dateTo')) {
            $collection->getSelect()
                       ->where('st_created_at <= ?', $searchCriteria->getData('dateTo') . ' 23:59:59');
        }
        if ($searchCriteria->getData('status') && $searchCriteria->getData('status') != 'null') {
            $collection->addFieldToFilter('st_status', $searchCriteria->getData('status'));
        }

        if ($searchCriteria->getData('type') && $searchCriteria->getData('warehouse_id') && $searchCriteria->getData('type') == 'out') {
            $collection->addFieldToFilter('st_to', ['neq' => $searchCriteria->getData('warehouse_id')]);
        }
        else if ($searchCriteria->getData('type') && $searchCriteria->getData('warehouse_id') && $searchCriteria->getData('type') == 'in') {
            $collection->addFieldToFilter('st_to', $searchCriteria->getData('warehouse_id'));
        }

        return $collection;
    }

    protected function getStockTransferItemCollection(DataObject $searchCriteria) {
        /** @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection */
        $collection = $this->objectManager->create("BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item\Collection");
        $collection->addFieldToFilter(
            "st_transfer_id",
            $searchCriteria->getData('st_transfer_id')
        );

        return $collection;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProductModel() {
        return $this->productFactory->create();
    }

    public function saveStockTransfer() {
        $data = $this->getRequestData()->getData();
        if (!empty($data['st_id'])) {
            /** @var \BoostMyShop\AdvancedStock\Model\Transfer $transfer */
            $transfer = $this->objectManager->create("BoostMyShop\AdvancedStock\Model\Transfer")->load($data['st_id'])
                                            ->setData('st_reference', $data['st_reference'])
                                            ->setData('st_from', $data['st_from'])
                                            ->setData('st_to', $data['st_to'])
                                            ->setData('st_status', $data['st_status'])
                                            ->setData('st_notes',$this->getRequest()->getParam('st_notes'))
                                            ->save();

            //foreach ($transfer->getItems() as $item) {
            //
            //    if (isset($data['delete'][$item->getsti_id()])) {
            //
            //        $item->delete();
            //
            //    }
            //    else {
            //        $qtyToTransfer
            //            = (isset($data['products'][$item->getst_product_id()]['qty_to_transfer'])) ? $data['products'][$item->getst_product_id()]['qty_to_transfer'] : null;
            //
            //        if (!is_null($qtyToTransfer)) {
            //            $item->setData('st_qty', $qtyToTransfer);
            //        }
            //
            //        if ($item->getOrigData('st_qty') != $item->getData('st_qty')) {
            //            $item->save();
            //        }
            //
            //    }
            //
            //    unset($data['products'][$item->getst_product_id()]);
            //
            //}


            if (isset($data['products']) && is_array($data['products'])) {
                foreach ($data['products'] as $product) {
                    if ($product['si_id'] == null) {
                        $this->objectManager->create("BoostMyShop\AdvancedStock\Model\Transfer\Item")
                                            ->setData('st_transfer_id', $transfer->getId())
                                            ->setData('st_product_id', $product['id'])
                                            ->setData('st_qty', isset($product['qty']) ? $product['qty'] : 1)
                                            ->save();
                    }
                    else {
                        $this->objectManager->create("BoostMyShop\AdvancedStock\Model\Transfer\Item")->load($product['si_id'])
                                            ->setData('st_transfer_id', $transfer->getId())
                                            ->setData('st_product_id', $product['id'])
                                            ->setData('st_qty', isset($product['qty']) ? $product['qty'] : 1)
                                            ->save();
                    }

                }
            }
            if (isset($data['delete']) && is_array($data['delete'])) {
                foreach ($data['delete'] as $productRemove) {
                    foreach ($transfer->getItems() as $item) {
                        if ($productRemove['si_id'] == $item->getData('sti_id')) {
                            $item->delete();
                        }
                    }
                }
            }
        }
        else {
            $transfer = $this->objectManager->create("BoostMyShop\AdvancedStock\Model\Transfer")
                                            ->setData('st_reference', $data['st_reference'])
                                            ->setData('st_from', $data['st_from'])
                                            ->setData('st_to', $data['st_to'])
                                            ->setData('st_status', $data['st_status'])
                                            ->setData('st_notes', $this->getRequest()->getParam('st_notes'))
                                            ->save();

            if (isset($data['products']) && is_array($data['products'])) {

                foreach ($data['products'] as $productId => $qties) {

                    $this->objectManager->create("BoostMyShop\AdvancedStock\Model\Transfer\Item")
                                        ->setData('st_transfer_id', $transfer->getId())
                                        ->setData('st_product_id', $productId)
                                        ->setData('st_qty', isset($qties['qty_to_transfer']) ? $qties['qty_to_transfer'] : 0)
                                        ->setData('st_qty_transfered', 0)
                                        ->save();
                }
            }
        }
        return  $this->getListStockTransfer(new DataObject(['st_id' => $transfer->getData("st_id")]));
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function applyTransfer() {
        $data = $this->getRequestData()->getData()['data'];
        if (isset($data['st_id']) || isset($data['transferData'])) {

            $transfer = $this->objectManager
                ->create("BoostMyShop\AdvancedStock\Model\Transfer")
                ->load($data['st_id']);

            if ($transfer->getId()) {
                $this->_backendAuthSession->setUser(new \Magento\Framework\DataObject());
                $transfer->processReception($data['transferData']);
                //$this->processTransferReception($data['transferData'],$transfer);
            }

            return $this->getListStockTransfer(
                new DataObject(
                    [
                        "st_id" => $data['st_id']
                    ]));
        }
        else {
            throw new \Exception("wrong_data");
        }
    }
}
