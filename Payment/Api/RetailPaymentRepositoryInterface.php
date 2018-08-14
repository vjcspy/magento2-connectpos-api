<?php
namespace SM\Payment\Api;

use SM\Payment\Model\RetailPaymentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface RetailPaymentRepositoryInterface 
{
    public function save(RetailPaymentInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(RetailPaymentInterface $page);

    public function deleteById($id);
}
