<?php
namespace SM\Product\Repositories\ProductManagement;


/**
 * Class ProductPrice
 *
 * @package SM\Product\Repositories\ProductManagement
 */
class ProductPrice {

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param                                $key
     * @param bool                           $returnRawData
     *
     * @return array|mixed|null
     */
    public function getExistingPrices(\Magento\Catalog\Model\Product $product, $key, $returnRawData = false) {
        $prices = $product->getData($key);

        if ($prices === null) {
            $attribute = $product->getResource()->getAttribute($key);
            if ($attribute) {
                $attribute->getBackend()->afterLoad($product);
                $prices = $product->getData($key);
            }
        }

        if ($prices === null || !is_array($prices)) {
            return ($returnRawData ? $prices : []);
        }

        return $prices;
    }
}