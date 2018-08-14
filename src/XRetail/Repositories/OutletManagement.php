<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 15/12/2016
 * Time: 16:52
 */

namespace SM\XRetail\Repositories;


use SM\Core\Api\Data\Outlet;
use SM\Core\Api\Data\Register;
use SM\Core\Model\DataObject;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class OutletManagement
 *
 * @package SM\XRetail\Repositories
 */
class OutletManagement extends ServiceAbstract {

    /**
     * @var \SM\XRetail\Model\OutletRepository
     */
    protected $outletRepository;
    /**
     * @var \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \SM\XRetail\Model\OutletFactory
     */
    protected $outletFactory;
    /**
     * @var \SM\XRetail\Model\Outlet\RegisterFactory
     */
    protected $registerFactory;
    /**
     * @var \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory
     */
    protected $registerCollectionFactory;

    /**
     * OutletManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                           $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                                     $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface                        $storeManager
     * @param \SM\XRetail\Model\OutletRepository                                $outletRepository
     * @param \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory          $collectionFactory
     * @param \SM\XRetail\Model\OutletFactory                                   $outletFactory
     * @param \SM\XRetail\Model\Outlet\RegisterFactory                          $registerFactory
     * @param \SM\XRetail\Model\ResourceModel\Outlet\Register\CollectionFactory $registerCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SM\XRetail\Model\OutletRepository $outletRepository,
        \SM\XRetail\Model\ResourceModel\Outlet\CollectionFactory $collectionFactory,
        \SM\XRetail\Model\OutletFactory $outletFactory,
        \SM\XRetail\Model\Outlet\RegisterFactory $registerFactory,
        \SM\XRetail\Model\ResourceModel\Outlet\Register\CollectionFactory $registerCollectionFactory
    ) {
        $this->registerCollectionFactory = $registerCollectionFactory;
        $this->registerFactory           = $registerFactory;
        $this->outletFactory             = $outletFactory;
        $this->collectionFactory         = $collectionFactory;
        $this->outletRepository          = $outletRepository;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     */
    public function getOutlets($searchCriteria = null) {
        if ($searchCriteria == null)
            $searchCriteria = $this->getSearchCriteria();
        $collection = $this->getOutletCollection($searchCriteria);
        $items      = [];

        if ($searchCriteria->getData('currentPage') > 1) {
        }
        else
            foreach ($collection as $objectModel) {
                $outlet              = new Outlet();
                $outlet['registers'] = $this->getRegisterByOutlet($objectModel);
                $items[]             = $outlet->addData($objectModel->getData());
            }

        return $this->getSearchResult()
                    ->setItems($items)
                    ->setTotalCount($collection->getSize())
                    ->setLastPageNumber(1)
                    ->getOutput();
    }

    public function save() {
        $outlet      = $this->getRequest()->getParam('data');
        $outletModel = $this->getOutletModel();
        if (isset($outlet['id']) && !!$outlet['id'])
            $outletModel->load($outlet['id']);
        unset($outlet['id']);
        $outlet['cashier_ids'] = json_encode(isset($outlet['cashier_ids']) ? $outlet['cashier_ids'] : []);
        $outletModel->addData($outlet);
        $outletModel->save();

        return $this->getOutlets(
            new DataObject(
                [
                    'ids' => $outletModel->getId()
                ]));
    }

    public function delete() {
        $outletId    = $this->getRequest()->getParam('id');
        $outletModel = $this->getOutletModel();
        if (!!$outletId) {
            $outletModel->load($outletId);
            $outletModel->delete();
        }
        else {
            throw new \Exception("Can't find outlet id");
        }

        return $outletId;
    }

    public function deleteRegister() {
        $registerId = $this->getRequest()->getParam('id');
        $outletId   = $this->getRequest()->getParam('outlet_id');
        if (!$outletId)
            throw new \Exception("Register must in outlet");

        if (!!$registerId) {
            $registerModel = $this->getRegisterModel();
            $registerModel->load($registerId);
            $registerModel->delete();
        }
        else {
            throw new \Exception("Can't find register id");
        }

        return $this->getOutlets(
            new DataObject(
                [
                    'ids' => $outletId
                ]));
    }

    public function saveRegister() {
        $register = $this->getRequest()->getParam('data');
        if (!isset($register['outlet_id']) || !$register['outlet_id'])
            throw new \Exception("Register must in outlet");

        $registerModel = $this->getRegisterModel();
        if (isset($register['id']) && !!$register['id'])
            $registerModel->load($register['id']);
        unset($register['id']);
        $registerModel->addData($register);
        $registerModel->save();

        return $this->getOutlets(
            new DataObject(
                [
                    'ids' => $register['outlet_id']
                ]));
    }

    /**
     * @param $searchCriteria
     *
     * @return \SM\XRetail\Model\ResourceModel\Outlet\Collection
     */
    protected function getOutletCollection($searchCriteria) {
        $collection = $this->collectionFactory->create();
        if ($searchCriteria->getData('ids')) {
            $collection->addFieldToFilter('id', ['in' => $searchCriteria->getData('ids')]);
        }

        return $collection;
    }

    protected function getRegisterByOutlet($outlet) {
        /** @var \SM\XRetail\Model\ResourceModel\Outlet\Register\Collection $collection */
        $collection = $this->registerCollectionFactory->create();
        $collection->addFieldToFilter('outlet_id', $outlet->getId());
        $registers = [];
        foreach ($collection as $register) {
            $r                = new Register($register->getData());
            $r['outlet_name'] = $outlet->getName();
            $registers[]      = $r->getOutput();
        }

        return $registers;
    }

    /**
     * @return \SM\XRetail\Model\Outlet
     */
    protected function getOutletModel() {
        return $this->outletFactory->create();
    }

    /**
     * @return \SM\XRetail\Model\Outlet\Register
     */
    protected function getRegisterModel() {
        return $this->registerFactory->create();
    }

    private function dummy($isSaveRegister) {
        if (!$isSaveRegister) {
            $data = [
                'id'                        => "",
                'name'                      => 'sdf' . random_int(0, 1000),
                'is_active'                 => 1,
                'warehouse_id'              => 1,
                'store_id'                  => 1,
                'cashier_ids'               => '1,2,3',
                'enable_guest_checkout'     => 1,
                'tax_calculation_based_on'  => 'outlet',
                'paper_receipt_template_id' => '1',
                'street'                    => 'absdf 1',
                'city'                      => 'sdfsdf',
                'country_id'                => 'us',
                'region_id'                 => 'sdfa',
                'postcode'                  => 'afsdf',
                'telephone'                 => '124234234'
            ];
        }
        else {
            $data = [
                'outlet_id'        => '1',
                'name'             => 'register ' . random_int(1, 1000),
                'is_active'        => 1,
                'is_print_receipt' => 1
            ];
        }
        $this->getRequest()->setParams(['data' => $data]);
    }
}