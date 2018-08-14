<?php
namespace SM\XRetail\Api;

use SM\XRetail\Model\UserOrderCounterInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface UserOrderCounterRepositoryInterface 
{
    public function save(UserOrderCounterInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(UserOrderCounterInterface $page);

    public function deleteById($id);
}
