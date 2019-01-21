<?php

namespace SM\Core\Api\Data;


class LoadCategory extends \SM\Core\Api\Data\Contract\ApiDataAbstract {

    public function getId() {
        return $this->getData('entity_id');
    }

    public function getName() {
        return $this->getData('name');
    }

}