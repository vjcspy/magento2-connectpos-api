<?php

namespace SM\Core\Api\Data;

class XProduct extends \SM\Core\Api\Data\Contract\ApiDataAbstract {

    public function __construct(
        array $data = []
    ) {
        parent::__construct($data);
    }

    public function getId() {

        return $this->getData('entity_id');
    }

    public function getSku() {

        return $this->getData('sku');
    }

    public function getName() {

        return $this->getData('name');
    }

    public function getAttributeSetId() {

        return $this->getData('attribute_set_id');
    }

    public function getPrice() {

        return $this->getData('price');
    }

    public function getTierPrices() {
        return $this->getData('tier_prices');
    }

    public function getStatus() {

        return $this->getData('status');
    }

    public function getVisibility() {

        return $this->getData('visibility');
    }

    public function getTypeId() {

        return $this->getData('type_id');
    }

    public function getTaxClassId() {

        return $this->getData('tax_class_id');
    }

    /*public function getCategoryId() {
        if (!!$this->getData('category_id'))
            return explode(',', $this->getData('category_id'));
        else
            return [];
    }*/

    /* public function getWebsites() {
         if (!!$this->getData('website_ids'))
             return explode(',', $this->getData('website_ids'));
         else
             return [];
     }*/

    public function getCustomAttributes() {

        return $this->getData('custom_attributes');
    }

    public function getXOptions() {

        return $this->getData('x_options');
    }

    public function getCustomizableOptions() {
        return $this->getData('customizable_options');
    }

    public function getStockItems() {

        return $this->getData('stock_items');
    }

    /* public function getDescription() {

         return $this->getData('description');
     }*/

    /*public function getShortDescription() {

        return $this->getData('short_description');
    }*/

    public function getSpecialPrice() {

        return $this->getData('special_price');
    }

    public function getSpecialFromDate() {

        return $this->getData('special_from_date');
    }

    public function getSpecialToDate() {

        return $this->getData('special_to_date');
    }

    public function getOriginImage() {

        return $this->getData('origin_image');
    }

    public function getMediaGallery() {

        return $this->getData('media_gallery');
    }

    public function getAdditionSearchFields() {
        return $this->getData('addition_search_fields');
    }
    /* public function getUrlKey() {
         return $this->getData('url_key');
     }*/
}
