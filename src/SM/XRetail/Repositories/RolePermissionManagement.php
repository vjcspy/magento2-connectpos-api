<?php

namespace SM\XRetail\Repositories;

use SM\Core\Api\Data\XRole;
use SM\Core\Model\DataObject;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 27/03/2017
 * Time: 15:19
 */
class RolePermissionManagement extends ServiceAbstract {

    /**
     * @var \SM\XRetail\Model\ResourceModel\Role\CollectionFactory
     */
    protected $roleCollection;
    /**
     * @var \SM\XRetail\Model\RoleFactory
     */
    protected $roleFactory;
    /**
     * @var \SM\XRetail\Model\ResourceModel\Permission\CollectionFactory
     */
    protected $permissionCollectionFactory;
    /**
     * @var \SM\XRetail\Model\ResourceModel\Permission\CollectionFactory
     */
    protected $permissionFactory;

    /**
     * RolePermissionManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                          $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface             $storeManager
     * @param \SM\XRetail\Model\ResourceModel\Role\CollectionFactory $roleCollection
     * @param \SM\XRetail\Model\RoleFactory                          $roleFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SM\XRetail\Model\ResourceModel\Role\CollectionFactory $roleCollection,
        \SM\XRetail\Model\RoleFactory $roleFactory,
        \SM\XRetail\Model\ResourceModel\Permission\CollectionFactory $permissionCollection,
        \SM\XRetail\Model\PermissionFactory $permissionFactory
    ) {
        $this->permissionFactory           = $permissionFactory;
        $this->roleFactory                 = $roleFactory;
        $this->roleCollection              = $roleCollection;
        $this->permissionCollectionFactory = $permissionCollection;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     */
    public function getRoles() {
        return $this->loadRoles($this->getRequestData())->getOutput();
    }

    /**
     * @param $requestData
     *
     * @return \SM\Core\Api\SearchResult
     */
    public function loadRoles($requestData = null) {
        $item = [];
        foreach ($this->getRoleCollection($requestData) as $role) {
            $e      = new XRole($role->getData());
            $item[] = $e;
        }
        $result = $this->getSearchResult();

        return $result->setItems($item)->setSearchCriteria($requestData);
    }

    /**
     * @return array
     */
    public function getPermission() {
        return $this->loadPermission($this->getRequestData())->getOutput();
    }

    /**
     * @param null $requestData
     *
     * @throws \Exception
     * @return \SM\Core\Api\SearchResult
     */
    public function loadPermission($requestData = null) {
        if (is_null($requestData)) {
            $requestData = $this->getRequestData();
        }

        $searchResult = $this->getSearchResult();
        if (isset($requestData->getData('searchCriteria')['currentPage']) && 1 < $requestData->getData('searchCriteria')['currentPage']) {
            $searchResult->setItems([]);
        }
        else {
            $searchResult->setItems($this->getPermissionCollection($requestData)->toArray()['items']);
        }

        return $searchResult;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function savePermission() {
        $requestData = $this->getRequestData();
        if ($requestData->getData('role_id') && $requestData->getData('permissions')) {
            /** @var \SM\XRetail\Model\ResourceModel\Permission\Collection $collection */
            $collection = $this->permissionCollectionFactory->create();
            $collection->getResource()->getConnection()->delete(
                $collection->getResource()->getTable('sm_permission'),
                ["role_id = ?" => $requestData->getData('role_id')]);

            foreach ($requestData->getData('permissions') as $permission) {
                $e = $this->getPermissionModel();
                $e->setData($permission)
                  ->setData('role_id', $requestData->getData('role_id'))
                  ->save();
            }

            return $this->loadRoles(new DataObject(['entity_id' => $requestData->getData('role_id')]))->getOutput();
        }
        else {
            throw new \Exception("require field role_id");
        }
    }

    /**
     * @return \SM\XRetail\Model\Permission
     */
    protected function getPermissionModel() {
        return $this->permissionFactory->create();
    }

    /**
     * @param $requestData
     *
     * @return \SM\XRetail\Model\ResourceModel\Permission\Collection
     */
    protected function getPermissionCollection($requestData) {
        /** @var \SM\XRetail\Model\ResourceModel\Permission\Collection $collection */
        $collection = $this->permissionCollectionFactory->create();
        if ($requestData->getData('entity_id')) {
            $collection->addFieldToFilter('id', ['in' => explode(",", $requestData->getData('entity_id'))]);
        }
        if ($requestData->getData('role_id')) {
            $collection->addFieldToFilter('role_id', $requestData->getData('role_id'));
        }

        return $collection;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function saveRole() {
        $data      = $this->getRequestData();
        $roleModel = $this->getRoleModel();
        if (!!$data->getData('id')) {
            $roleModel->load($data->getData('id'));

            if (!$roleModel->getId()) {
                throw new \Exception("Can't find role with id:" . $data->getData('id'));
            }
        }
        $data->unsetData('id');
        $roleModel->addData($data->getData())->save();

        return $this->loadRoles(new DataObject(['entity_id' => $roleModel->getId()]))->getOutput();
    }

    /**
     * @throws \Exception
     */
    public function deleteRole() {
        $roleModel = $this->getRoleModel();
        $data      = $this->getRequestData();
        if ($data->getData('id')) {
            $roleModel->load($data->getData('id'));

            if (!$roleModel->getId()) {
                throw new \Exception("Can't find role with id:" . $data->getData('id'));
            }

            $roleModel->delete();
        }
        else
            throw new \Exception("Require field id");
    }

    /**
     * @param $requestData
     *
     * @return \SM\XRetail\Model\ResourceModel\Role\Collection
     */
    protected function getRoleCollection($requestData) {
        /** @var \SM\XRetail\Model\ResourceModel\Role\Collection $collection */
        $collection = $this->roleCollection->create();
        if ($requestData->getData('entity_id')) {
            $collection->addFieldToFilter('id', ['in' => explode(",", $requestData->getData('entity_id'))]);
        }

        return $collection;
    }

    /**
     * @return \SM\XRetail\Model\Role
     */
    protected function getRoleModel() {
        return $this->roleFactory->create();
    }
}