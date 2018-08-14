<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 7/9/17
 * Time: 4:40 PM
 */

namespace SM\Product\Helper;


class ProductHelper {

    protected $_productAdditionAttribute;

    protected $_productSearchAttribute;
    /**
     * @var \Magento\Config\Model\Config\Loader
     */
    protected $configLoader;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    protected $entityType;
    public function __construct(
        \Magento\Config\Model\Config\Loader $loader,
        \Magento\Eav\Model\Entity\Type $entityType,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
        $this->configLoader = $loader;
        $this->entityType     = $entityType;
        $this->_objectManager = $objectManager;
    }

    public function getProductAdditionAttribute() {
        if (is_null($this->_productAdditionAttribute)) {
            $configData       = $this->configLoader->getConfigByPath('xretail/' . 'pos', 'default', 0);
            $productAttribute = array_filter(
                $configData,
                function ($key) {
                    return $key === 'xretail/pos/addition_field_search';
                },
                ARRAY_FILTER_USE_KEY);

            $this->_productAdditionAttribute = count($productAttribute) > 0 && is_array(json_decode(current($productAttribute)['value'], true)) ?
                json_decode(
                    current($productAttribute)['value'],
                    true) : [];
        }

        return $this->_productAdditionAttribute;
    }

    public function getDefaultProductAttributeSearch() {
        if (is_null($this->_productSearchAttribute)) {
            $configData       = $this->configLoader->getConfigByPath('xretail/' . 'pos', 'default', 0);
            $productAttribute = array_filter(
                $configData,
                function ($key) {
                    return $key === 'xretail/pos/search_product_attribute';
                },
                ARRAY_FILTER_USE_KEY);

            $this->_productSearchAttribute = count($productAttribute) > 0 && is_array(json_decode(current($productAttribute)['value'], true)) ?
                json_decode(
                    current($productAttribute)['value'],
                    true) : [];
        }

        return $this->_productSearchAttribute;
    }

    public function getSearchOnlineAttribute($defaultSearchField = null) {
        if (!!$defaultSearchField) {
            $defaultAttributeSearch = $defaultSearchField;
        }
        else {
            $defaultAttributeSearch = $this->getDefaultProductAttributeSearch();
        }
        $productAttribute       = $this->getProductAdditionAttribute();

        return array_merge($defaultAttributeSearch, $productAttribute);
    }

    ///**
    // * @return array
    // */
    //public function getProductAttributes() {
    //    $attributes     = $this->getProductModel()->getAttributes();
    //    $attributeArray = [];
    //
    //    foreach ($attributes as $attribute) {
    //        $attributeArray[] = [
    //            'label' => $attribute->getFrontend()->getLabel(),
    //            'value' => $attribute->getAttributeCode()
    //        ];
    //    }
    //
    //    return $attributeArray;
    //}

    /**
     * @return array
     */
    public function getProductAttributes() {
        //$attributes     = $this->productFactory->create()->getAttributes();
        $productEntityTypeId       = $this->entityType->loadByCode('catalog_product')->getId();
        $coll = $this->_objectManager->create(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class);
        $coll->addFieldToFilter(\Magento\Eav\Model\Entity\Attribute\Set::KEY_ENTITY_TYPE_ID, $productEntityTypeId);
        $attributes = $coll->load()->getItems();
        $attributeArray = [];

        foreach ($attributes as $attribute) {
            $attributeArray[] = [
                'label' => $attribute->getFrontend()->getLabel(),
                'value' => $attribute->getAttributeCode()
            ];
        }

        return $attributeArray;
    }

    /**
     * @return  \Magento\Catalog\Model\Product
     */
    protected function getProductModel() {
        return $this->productFactory->create();
    }
}