<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 15/11/2016
 * Time: 18:29
 */

namespace SM\Core\Api\Data;


class XCategory extends \SM\Core\Api\Data\Contract\ApiDataAbstract {

    public function getId() {
        return $this->getData('entity_id');
    }

    public function getName() {
        return $this->getData('name');
    }

    public function getParentId() {
        return $this->getData('parent_id');
    }

    public function getProductIds() {
        return $this->getData('product_ids');
    }

    public function getIsActive() {
        return $this->getData('is_active');
    }

    public function getLevel() {
        return $this->getData('level');
    }

    public function getPosition() {
        return $this->getData('position');
    }

    public function getPath() {
        return $this->getData('path');
    }

    public function getImageUrl() {
        return $this->getData('image_url');
    }

}