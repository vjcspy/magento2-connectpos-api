<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 27/03/2017
 * Time: 16:10
 */

namespace SM\Core\Api\Data;


class XRole extends \SM\Core\Api\Data\Contract\ApiDataAbstract {

    public function getId() {
        return $this->getData('id');
    }

    public function getName() {
        return $this->getData('name');
    }

    public function getIsActive() {
        return $this->getData('is_active');
    }
}