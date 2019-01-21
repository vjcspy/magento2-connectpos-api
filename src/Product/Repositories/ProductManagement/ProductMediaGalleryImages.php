<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 25/11/2016
 * Time: 14:11
 */

namespace SM\Product\Repositories\ProductManagement;


use Magento\Catalog\Model\Product;

/**
 * Class ProductMediaGalleryImages
 *
 * @package SM\Product\Repositories\ProductManagement
 */
class ProductMediaGalleryImages {

    /**
     * @var array
     */
    private $_cacheImage = [];

    /**
     * @param \Magento\Catalog\Model\Product $item
     *
     * @return mixed
     */
    public function getMediaGalleryImages(Product $item) {
        if (!isset($this->_cacheImage[$item->getId()])) {
            $media = [];
            foreach ($item->getMediaGalleryImages() as $mediaGalleryImage) {
                $media[] = $mediaGalleryImage['url'];
            }
            $this->_cacheImage[$item->getId()] = $media;
        }

        return $this->_cacheImage[$item->getId()];
    }

}