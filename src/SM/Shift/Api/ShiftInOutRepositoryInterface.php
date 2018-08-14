<?php
namespace SM\Shift\Api;

use SM\Shift\Model\ShiftInOutInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ShiftInOutRepositoryInterface 
{
    public function save(ShiftInOutInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(ShiftInOutInterface $page);

    public function deleteById($id);
}
