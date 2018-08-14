<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 8/7/17
 * Time: 9:17 AM
 */

namespace SM\Category\Model\ResourceModel\Catalog\Category;


class Product extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        // TODO: Implement _construct() method.
    }

    public function getAllProductIdsByCategory($catId) {
        $catalogProductTable = $this->getTable('catalog_category_product');
        $query               = 'SELECT * FROM ' . $catalogProductTable . ' WHERE category_id=' . $catId;
        $cats                = $this->getConnection()->fetchAll($query);

        return array_reduce(
            $cats,
            function ($carrier, $item) {
                array_push($carrier, $item['product_id']);

                return $carrier;
            },
            []);
    }
}