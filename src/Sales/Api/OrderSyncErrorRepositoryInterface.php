<?php

namespace SM\Sales\Api;

use SM\Sales\Api\Data\OrderSyncErrorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface OrderSyncErrorRepositoryInterface {

    public function save(OrderSyncErrorInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(OrderSyncErrorInterface $page);

    public function deleteById($id);
}
