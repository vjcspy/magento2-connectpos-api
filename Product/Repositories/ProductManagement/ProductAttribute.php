<?php
namespace SM\Product\Repositories\ProductManagement;

use SM\XRetail\Helper\DataConfig;


/**
 * Class ProductAttribute
 *
 * @package SM\Product\Repositories\ProductManagement
 */
class ProductAttribute {

    /**
     * @var
     */
    protected $_data;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $productAttributeCollection;
    /**
     * @var \SM\XRetail\Helper\DataConfig
     */
    private $dataConfig;

    /**
     * ProductAttribute constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributeCollection
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributeCollection,
        DataConfig $dataConfig
    ) {
        $this->dataConfig                 = $dataConfig;
        $this->productAttributeCollection = $productAttributeCollection;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getCustomAttributes(\Magento\Catalog\Model\Product $product) {

        if ($this->dataConfig->getApiGetCustomAttributes()) {
            $customAtt  = [];
            $attributes = $this->getAllCustomAttributes();
            foreach ($attributes as $attribute) {
                $val = $product->getData($attribute['value']);
                if (!is_null($val))
                    $customAtt[$attribute['value']] = $val;
            }

            return $customAtt;
        }
        else
            return [];
    }

    /**
     * @return mixed
     */
    public function getAllCustomAttributes() {
        $result     = [];
        $attributes = $this->productAttributeCollection
            ->addVisibleFilter();
        if ($attributes != null && $attributes->count() > 0)
            foreach ($attributes as $attribute) {
                $result[] = ['value' => $attribute->getAttributeCode(), 'key' => $attribute->getFrontendLabel()];
            }

        return $result;
    }
}