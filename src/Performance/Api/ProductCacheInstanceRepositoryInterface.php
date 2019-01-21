<?php
namespace SM\Performance\Api;

use SM\Performance\Api\Data\ProductCacheInstanceInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ProductCacheInstanceRepositoryInterface 
{
    public function save(ProductCacheInstanceInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(ProductCacheInstanceInterface $page);

    public function deleteById($id);
}
