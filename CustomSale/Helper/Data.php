<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 12/01/2017
 * Time: 10:46
 */

namespace SM\CustomSale\Helper;


class Data {

    /**
     * @var int
     */
    static $COUNT = 0;
    /**
     *
     */
    const CUSTOM_SALES_PRODUCT_SKU = 'custom_sales_product_for_retail';
    /**
     * @var
     */
    protected $customSalesProductId;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;
    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $entityType;
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $entityAttrSet;

    /**
     * Data constructor.
     *
     * @param \Magento\Catalog\Model\ProductFactory   $productFactory
     * @param \Magento\Framework\App\State            $state
     * @param \Magento\Eav\Model\Entity\Type          $entityType
     * @param \Magento\Eav\Model\Entity\Attribute\Set $entityAttrSet
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\State $state,
        \Magento\Eav\Model\Entity\Type $entityType,
        \Magento\Eav\Model\Entity\Attribute\Set $entityAttrSet
    ) {
        $this->entityAttrSet  = $entityAttrSet;
        $this->entityType     = $entityType;
        $this->appState       = $state;
        $this->productFactory = $productFactory;
    }

    /**
     * @return false|int
     */
    public function getCustomSaleId() {
        if (is_null($this->customSalesProductId)) {
            /** @var \Magento\Catalog\Model\Product $productModel */
            $productModel               = $this->productFactory->create();
            $this->customSalesProductId = $productModel->getResource()->getIdBySku(self::CUSTOM_SALES_PRODUCT_SKU);
            if (!$this->customSalesProductId) {
                $this->customSalesProductId = $this->createNewCustomSalesProduct()->getId();
            }
        }

        return $this->customSalesProductId;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getCustomSaleProduct() {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productFactory->create();

        return $product->load($this->getCustomSaleId());
    }

    /**
     * @return mixed
     */
    protected function createNewCustomSalesProduct() {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productFactory->create();
        $product->setUrlKey(uniqid("custom_sale"));
        $product->setName('Custom Sale');
        $product->setTypeId('simple');
        $product->setStatus(2);
        $product->setAttributeSetId($this->getAttributeSetForCustomSalesProduct());
        $product->setSku(self::CUSTOM_SALES_PRODUCT_SKU);
        $product->setVisibility(4);
        $product->setPrice(0);
        $product->setStockData(
            [
                'use_config_manage_stock'          => 0, //'Use config settings' checkbox
                'manage_stock'                     => 0, //manage stock
                'min_sale_qty'                     => 1, //Minimum Qty Allowed in Shopping Cart
                'max_sale_qty'                     => 2, //Maximum Qty Allowed in Shopping Cart
                'is_in_stock'                      => 1, //Stock Availability
                'qty'                              => 999999, //qty,
                'original_inventory_qty'           => '999999',
                'use_config_min_qty'               => '0',
                'use_config_min_sale_qty'          => '0',
                'use_config_max_sale_qty'          => '0',
                'is_qty_decimal'                   => '1',
                'is_decimal_divided'               => '0',
                'use_config_backorders'            => '1',
                'use_config_notify_stock_qty'      => '0',
                'use_config_enable_qty_increments' => '0',
                'use_config_qty_increments'        => '0',
            ]
        );

        return $product->save();
    }

    /**
     * PERFECT CODE
     *
     * @return int
     */
    public function getAttributeSetForCustomSalesProduct() {
        $productEntityTypeId       = $this->entityType->loadByCode('catalog_product')->getId();
        $eavAttributeSetCollection = $this->entityAttrSet->getCollection();

        // FIXME: We will implement setting for admin select attribute set of customer later.
        $eavAttributeSetCollection->addFieldToFilter('attribute_set_name', 'Default')->addFieldToFilter('entity_type_id', $productEntityTypeId);

        $id = $eavAttributeSetCollection->getFirstItem()->getId();

        if (is_null($id)) {
            $eavAttributeSetCollection = $this->entityAttrSet->getCollection();

            return $eavAttributeSetCollection->addFieldToFilter('entity_type_id', $productEntityTypeId)->getFirstItem()->getId();
        }

        return $id;
    }
}