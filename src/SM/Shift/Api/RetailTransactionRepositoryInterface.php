<?php
namespace SM\Shift\Api;

use SM\Shift\Model\RetailTransactionInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface RetailTransactionRepositoryInterface 
{
    public function save(RetailTransactionInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(RetailTransactionInterface $page);

    public function deleteById($id);
}
