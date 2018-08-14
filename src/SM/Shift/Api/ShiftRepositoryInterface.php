<?php
namespace SM\Shift\Api;

use SM\Shift\Model\ShiftInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ShiftRepositoryInterface 
{
    public function save(ShiftInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(ShiftInterface $page);

    public function deleteById($id);
}
