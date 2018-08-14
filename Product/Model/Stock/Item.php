<?php
/**
 * Created by mr.vjcspy@gmail.com/khoild@smartosc.com.
 * Date: 2/18/16
 * Time: 2:57 PM
 */
namespace SM\Product\Model\Stock;

/**
 * Catalog Inventory Stock Model
 *
 * @method \Magento\CatalogInventory\Model\ResourceModel\Stock\Item _getResource()
 *
 * @author      mr.vjcspy@gmail.com
 */
class Item extends \Magento\CatalogInventory\Model\Stock\Item {

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStockItemById($productId) {
        $this->_getResource()->loadByProductId($this, $productId,$this->storeManager->getWebsite()->getId());
        $this->setOrigData();

        return $this;
    }
}
