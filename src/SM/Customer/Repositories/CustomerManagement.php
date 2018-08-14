<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 24/10/2016
 * Time: 15:22
 */

namespace SM\Customer\Repositories;


use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use SM\Core\Api\Data\CountryRegion;
use SM\Core\Api\Data\CustomerAddress;
use SM\Core\Api\Data\CustomerGroup;
use SM\Core\Api\Data\XCustomer;
use SM\Core\Model\DataObject;
use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;
use Magento\Catalog\Model\ProductFactory;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class CustomerManagement
 *
 * @package SM\Customer\Repositories
 */
class CustomerManagement extends ServiceAbstract {

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $countryCollection;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $customerGroupCollectionFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    protected $configShare;
    /**
     * @var \SM\Customer\Helper\Data
     */
    protected $customerHelper;
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;
    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollectionFactory;
    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $customerConfigShare;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Sale\CollectionFactory
     */
    protected $salesCollectionFactory;
    /**
     * @var \SM\Integrate\Helper\Data
     */
    private $integrateHelper;
    /**
     * @var \SM\Wishlist\Repositories\WishlistManagement
     */
    private $wishlistManagement;

    /**
     * @var \SM\Customer\Model\ResourceModel\Grid\CollectionFactory
     */
    private $customerGridCollectionFactory;

    /**
     *
     * @var \Magento\Newsletter\Model\Subscriber
     */
    private $subscriberFactory;

    /**
     * CustomerManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                          $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                                    $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface                       $storeManager
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Customer\Model\Config\Share                             $customerConfigShare
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection                        $resource
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory    $groupCollectionFactory
     * @param \Magento\Customer\Model\CustomerFactory                          $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface                $customerRepository
     * @param \SM\Customer\Helper\Data                                         $customerHelper
     * @param \Magento\Customer\Api\AddressRepositoryInterface                 $addressRepository
     * @param \Magento\Customer\Model\AddressFactory                           $addressFactory
     * @param \Magento\Sales\Model\ResourceModel\Sale\CollectionFactory        $salesCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory                            $productFactory
     * @param \SM\Integrate\Helper\Data                                        $integrateHelperData
     * @param \SM\Wishlist\Repositories\WishlistManagement                     $wishlistManagement
     * @param \SM\Customer\Model\ResourceModel\Grid\CollectionFactory          $customerGridCollection
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Customer\Model\Config\Share $customerConfigShare,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \SM\Customer\Helper\Data $customerHelper,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Sales\Model\ResourceModel\Sale\CollectionFactory $salesCollectionFactory,
        ProductFactory $productFactory,
        \SM\Integrate\Helper\Data $integrateHelperData,
        \SM\Wishlist\Repositories\WishlistManagement $wishlistManagement,
        \SM\Customer\Model\ResourceModel\Grid\CollectionFactory $customerGridCollection,
        SubscriberFactory $subscriberFactory
    ) {
        $this->customerConfigShare            = $customerConfigShare;
        $this->customerCollectionFactory      = $customerCollectionFactory;
        $this->countryCollection              = $countryCollectionFactory;
        $this->resource                       = $resource;
        $this->customerGroupCollectionFactory = $groupCollectionFactory;
        $this->customerFactory                = $customerFactory;
        $this->customerRepository             = $customerRepository;
        $this->customerHelper                 = $customerHelper;
        $this->addressRepository              = $addressRepository;
        $this->addressFactory                 = $addressFactory;
        $this->productFactory                 = $productFactory;
        $this->salesCollectionFactory         = $salesCollectionFactory;
        $this->integrateHelper                = $integrateHelperData;
        $this->wishlistManagement             = $wishlistManagement;
        $this->customerGridCollectionFactory  = $customerGridCollection;
        $this->subscriberFactory              = $subscriberFactory;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     */
    public function getCustomerData() {
        return $this->loadCustomers($this->getSearchCriteria())->getOutput();
    }

    /**
     * @param null $searchCriteria
     *
     * @return \SM\Core\Api\SearchResult
     */
    public function loadCustomers($searchCriteria = null) {
        if (is_null($searchCriteria) || !$searchCriteria)
            $searchCriteria = $this->getSearchCriteria();

        $this->getSearchResult()->setSearchCriteria($searchCriteria);
        $collection = $this->getCustomerCollection($searchCriteria);

        $customers = [];
        if ($collection->getLastPageNumber() < $searchCriteria->getData('currentPage')) {
        }
        else {
            foreach ($collection as $customerModel) {
                $customerModel->load($customerModel->getId());
                /** @var $customerModel \Magento\Customer\Model\Customer */
                $customer = new XCustomer();
                $customer->addData($customerModel->getData());
                $customer->setData('tax_class_id', $customerModel->getTaxClassId());

                $customer->setData('address', $this->getCustomerAddress($customerModel));

                $checkSubscriber = $this->subscriberFactory->create()->loadByCustomerId($customerModel->getId());
                if ($checkSubscriber->isSubscribed()) {
                    $customer->setData('subscription', true);
                } else {
                    $customer->setData('subscription', false);
                }

                $customers[] = $customer;
            }

        }
        return $this->getSearchResult()
                    ->setItems($customers)
                    ->setLastPageNumber($collection->getLastPageNumber())
                    ->setTotalCount($collection->getSize());
    }

    /**
     *
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return \Magento\Customer\Model\ResourceModel\Grid\Collection
     * @throws \Exception
     */
    protected function getCustomerCollection($searchCriteria) {
        $storeId = $searchCriteria->getData('storeId');
        if (is_null($storeId)) {
            throw new \Exception(__('Must have param storeId'));
        }
        else {
            $this->getStoreManager()->setCurrentStore($storeId);
        }
        /** @var \SM\Customer\Model\ResourceModel\Grid\Collection $collection */
        $collection = $this->customerGridCollectionFactory->create();
        $collection->setCurPage(is_nan($searchCriteria->getData('currentPage')) ? 1 : $searchCriteria->getData('currentPage'));
        if ($searchCriteria->getData('ids')) {
            $collection->addFieldToFilter('entity_id', ['in' => $searchCriteria->getData('ids')]);
        }
        if ($searchCriteria->getData('entity_id') || $searchCriteria->getData('entityId')) {
            $ids = is_null($searchCriteria->getData('entity_id')) ? $searchCriteria->getData('entityId') : $searchCriteria->getData('entity_id');
            $collection->addFieldToFilter('entity_id', ['in' => explode(",", $ids)]);
        }
        if ($searchCriteria->getData('searchOnline') == 1) {
            $searchValue = $searchCriteria->getData('searchValue');
            $searchField = $searchCriteria->getData('searchFields');

            $_fieldFilters = [];
            $_valueFilters = [];
            foreach (explode(",", $searchField) as $field) {
                if ($field === 'first_name' || $field === 'last_name') {
                    $_fieldFilters[] = "name";
                    $_valueFilters[] = ['like' => '%' . $searchValue . '%'];
                }
                else if ($field === 'telephone') {
                    $_fieldFilters[] = 'billing_telephone';
                    $_fieldFilters[] = 'shipping_full';
                    $_valueFilters[] = ['like' => '%' . $searchValue . '%'];
                    $_valueFilters[] = ['like' => '%' . $searchValue . '%'];
                }
                elseif ($field === 'id') {
                    $_fieldFilters  [] = 'entity_id';
                    $_valueFilters[]   = ['like' => '%' . $searchValue . '%'];
                }
                elseif ($field === 'postcode') {
                    $_fieldFilters[] = 'billing_postcode';
                    $_fieldFilters[] = 'shipping_full';
                    $_valueFilters[] = ['like' => '%' . $searchValue . '%'];
                    $_valueFilters[] = ['like' => '%' . $searchValue . '%'];
                }
                else if ($field === 'email') {
                    $_fieldFilters  [] = 'email';
                    $_valueFilters[]   = ['like' => '%' . $searchValue . '%'];
                }
            }
            $_fieldFilters = array_unique($_fieldFilters);
            $collection->addFieldToFilter($_fieldFilters, $_valueFilters);
        }
        $collection->setPageSize(
            is_nan($searchCriteria->getData('pageSize')) ? DataConfig::PAGE_SIZE_LOAD_CUSTOMER : $searchCriteria->getData('pageSize')
        );
        if ($this->customerConfigShare->isWebsiteScope()) {
            $collection->addFieldToFilter('website_id', $this->getStoreManager()->getStore($storeId)->getWebsiteId());
        }

        return $collection;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     *
     * @return array
     */
    protected function getCustomerAddress(\Magento\Customer\Model\Customer $customer) {
        $customerAdd = [];

        foreach ($customer->getAddresses() as $address) {
            /** @var \Magento\Customer\Model\Address $address */
            $customerAdd[] = $this->getAddressData($address);
        }

        return $customerAdd;
    }

    /**
     * Get customer address base on api
     *
     * @param \Magento\Customer\Model\Address $address
     *
     * @return array
     */
    protected function getAddressData(\Magento\Customer\Model\Address $address) {
        $addData           = $address->getData();
        $addData['street'] = $address->getStreet();
        $_customerAdd      = new CustomerAddress($addData);

        return $_customerAdd->getOutput();
    }

    /**
     * @return array
     */
    public function getCountryRegionData() {
        $items      = [];
        $collection = $this->getCountryCollection($this->getSearchCriteria());
        if ($collection->getLastPageNumber() < $this->getSearchCriteria()->getData('currentPage')) {
        }
        else {
            foreach ($collection as $country) {
                /** @var \Magento\Directory\Model\Country $country */
                $regionCollection = $country->getRegionCollection();
                $regions          = [];
                foreach ($regionCollection as $region) {
                    $regions[] = $region->getData();
                }
                $countryRegion = new CountryRegion();
                $countryRegion->addData(
                    [
                        'country_id' => $country->getCountryId(),
                        'name'       => $country->getName(),
                        'regions'    => $regions
                    ]);
                $items[] = $countryRegion;
            }
        }

        return $this->getSearchResult()
                    ->setItems($items)
                    ->setTotalCount($collection->getSize())
                    ->getOutput();
    }

    /**
     * @param $searchCriteria
     *
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected function getCountryCollection($searchCriteria) {
        /** @var   \Magento\Directory\Model\ResourceModel\Country\Collection $collection */
        $collection = $this->countryCollection->create();
        $collection->setCurPage(is_nan($searchCriteria->getData('currentPage')) ? 1 : $searchCriteria->getData('currentPage'));
        $collection->setPageSize(
            is_nan($searchCriteria->getData('pageSize')) ? DataConfig::PAGE_SIZE_LOAD_CUSTOMER : $searchCriteria->getData('pageSize')
        );

        return $collection;
    }

    /**
     * @return array
     */
    public function getCustomerGroupData() {
        $items      = [];
        $collection = $this->getCustomerGroupCollection($this->getSearchCriteria());
        if ($collection->getLastPageNumber() < $this->getSearchCriteria()->getData('currentPage')) {
        }
        else {
            foreach ($collection as $group) {
                $g = new CustomerGroup();
                /** @var \Magento\Customer\Model\Group $group */
                $g->addData(
                    [
                        'customer_group_id'   => $group->getId(),
                        'customer_group_code' => $group->getCode(),
                        'tax_class_id'        => $group->getData('tax_class_id'),
                        'tax_class_name'      => $group->getTaxClassName()
                    ]);
                $items[] = $g;
            }
        }

        return $this->getSearchResult()
                    ->setSearchCriteria($this->getSearchCriteria())
                    ->setItems($items)
                    ->setTotalCount($collection->getSize())
                    ->getOutput();
    }

    protected function getCustomerGroupCollection($searchCriteria) {
        /** @var   \Magento\Customer\Model\ResourceModel\Group\Collection $collection */
        $collection = $this->customerGroupCollectionFactory->create();
        $collection->setCurPage(is_nan($searchCriteria->getData('currentPage')) ? 1 : $searchCriteria->getData('currentPage'));
        $collection->setPageSize(
            is_nan($searchCriteria->getData('pageSize')) ? DataConfig::PAGE_SIZE_LOAD_CUSTOMER : $searchCriteria->getData('pageSize')
        );
        if ($searchCriteria->getData('entity_id')) {
            $collection->addFieldToFilter('customer_group_id', ['in' => explode(",", $searchCriteria->getData('entity_id'))]);
        }

        return $collection;
    }

    public function create($data) {
        $this->getRequest()->setParams($data);
        $customer = $this->save();
        if (isset($customer['items'][0]['id'])) {
            return $customer['items'][0]['id'];
        }
        else {
            throw new \Exception("Can't create customer");
        }
    }

    /**
     * Save customer and address
     *
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save() {
        $data = $this->getRequestData();

        $customerData = new DataObject($data->getData('customer'));
        $addressData  = $data->getData('address') ? new DataObject($data->getData('address')) : null;
        $addressType  = $data->getData('addressType');
        $storeId      = $data->getData('storeId');

        if (is_null($storeId)) {
            throw new \Exception("Please define customer store id");
        }
        $this->customerHelper->transformCustomerData($customerData);

        // Check email already exists in website
        if (!$customerData->getId()) {
            try {
                $checkCustomer = $this->customerRepository->get($customerData->getEmail());
                $websiteId     = $checkCustomer->getWebsiteId();

                if ($this->customerHelper->isCustomerInStore($websiteId, $storeId)) {
                    throw new \Exception(__('A customer with the same email already exists in an associated website.'));
                }
            }
            catch (\Exception $e) {
                // CustomerRepository will throw exception if can't not find customer with email
            }
        }

        // Associate website_id with customer
        if (!$customerData->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $customerData->setWebsiteId($websiteId);
        }

        // Update 'created_in' value with actual store name
        if ($customerData->getId() === null) {
            $storeName = $this->storeManager->getStore($storeId)->getName();
            $customerData->setCreatedIn($storeName);
        }

        try {
            $customer = $this->getCustomerModel();
            $customerData->setAddress(null);
            if ($customerData->getId() && $customerData->getId() < 1481282470403) {
                $customer = $customer->load($customerData->getId());

                if ($customer->getId()) {
                    if ($addressType === 'billing') {
                        $customer->addData($customerData->getData())
                                 ->save();
                    }
                }
                else {
                    throw new \Exception("Can't find customer with id: " . $customerData->getId());
                }
            }
            else if ($addressType === 'shipping') {
                throw new \Exception("Please define customer when save shipping address");
            }
            else {
                $customer = $customer->addData($customerData->getData())
                                     ->save();
                try {
                    $customer->sendNewAccountEmail('confirmed', '', $storeId);
                }
                catch (\Exception $e) {
                   
                }
            }

            if ($addressData && $customer->getId()) {
                $this->customerHelper->transformCustomerData($addressData);
                $addressModel = $this->getAddressModel();
                if ($addressData->getId() && $addressData->getId() < 1481282470403) {
                    $addressModel->load($addressData->getId());
                    if (!$addressModel->getId()) {
                        throw new \Exception(__("Can't get address id: " . $addressData->getId()));
                    }
                }
                else {
                    $addressData->setId(null);
                }
                $addressModel->addData($addressData->getData())
                             ->setData('parent_id', $customer->getId())
                             ->save();

                $customer = $this->getCustomerModel()->load($customer->getId());
                if ($addressType === 'billing') {
                    $customer->setDefaultBilling($addressModel->getId())->save();
                }
                else {
                    $customer->setDefaultShipping($addressModel->getId())->save();
                }
            }

            if (isset($customerData['subscription']) && $customer->getId()) {
                if ($customerData['subscription'] == 1) {
                    $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
                } else {
                    $this->subscriberFactory->create()->unsubscribeCustomerById($customer->getId());
                }
            }


        }
        catch (AlreadyExistsException $e) {
            throw new \Exception(
                __('A customer with the same email already exists in an associated website.')
            );
        }
        catch (LocalizedException $e) {
            throw $e;
        }

        $searchCriteria = new \Magento\Framework\DataObject(
            [
                'storeId'   => $storeId,
                'entity_id' => $customer->getId()
            ]);


        return $this->loadCustomers($searchCriteria)->getOutput();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function createAddress() {
        $address = $this->getRequestData();
        $this->customerHelper->transformCustomerData($address);

        $addressModel = $this->getAddressModel();
        if ($address->getId()) {
            $addressModel->load($address->getId());
            if (!$addressModel->getId()) {
                throw new \Exception(__("Can't get address id: " . $address->getId()));
            }
        }
        $addressModel->addData($address->getData());
        $addressModel->setData('parent_id', $address->getData('customer_id'));

        return $this->getAddressData($addressModel->save());
    }

    /**
     * @return \Magento\Customer\Model\Address
     */
    protected function getAddressModel() {
        return $this->addressFactory->create();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomerModel() {
        return $customerModel = $this->customerFactory->create();
    }

    private function dummy($isDummyAdd = false) {
        /*
         * Create customer: Post: mage212.dev/xrest/v1/xretail/customer
         * Edit customer: Put: mage212.dev/xrest/v1/xretail/customer
         * Add Customer address: Post: mage212.dev/xrest/v1/xretail/customer-address
         * Edit Customer address: Put: mage212.dev/xrest/v1/xretail/customer-address
         * */
        if (!$isDummyAdd) {
            $data = [
                "id"          => 22,
                "group_id"    => 2,
                "email"       => "mr.vjcspy" . rand(0, 100000) . "@gmail.com",
                "first_name"  => "Mr" . rand(0, 100000),
                "last_name"   => "Abc" . rand(0, 100000),
                "middle_name" => "xyz" . rand(0, 100000),
                "prefix"      => "a",
                "suffix"      => "b",
                "gender"      => 0,
                "storeId"     => 1,
            ];
        }
        else {
            $data = [
                "id"          => 7, // Nếu add thì id =0, edit thì có id
                "customer_id" => 22,
                "region_id"   => 0,
                "country_id"  => "string",
                "street"      => [
                    "string"
                ],
                "telephone"   => "string",
                "postcode"    => "string",
                "city"        => "string",
                "first_name"  => "string",
                "last_name"   => "string",
                "middle_name" => "string",
            ];
        }
        $this->getRequest()->setParams($data);
    }

    public function loadCustomerDetail($searchCriteria = null) {
        if (is_null($searchCriteria) || !$searchCriteria) {
            $searchCriteria = $this->getSearchCriteria();
        }
        $this->getSearchResult()->setSearchCriteria($searchCriteria);
        $customerId = $searchCriteria->getData('customerId');
        $storeId    = $searchCriteria->getData('storeId');
        if (is_null($customerId) || is_null($storeId)) {
            throw new \Exception(__("Something wrong! Missing require value"));
        }

        $data = [
            'life_time_sales' => $this->salesCollectionFactory->create()
                                                              ->setOrderStateFilter(Order::STATE_COMPLETE, false)
                                                              ->setCustomerIdFilter($customerId)
                                                              ->load()
                                                              ->getTotals()
                                                              ->getLifetime(),
            'wishlist'        => $this->wishlistManagement->getWishlistData($customerId, $storeId),
        ];

        if ($this->integrateHelper->isIntegrateRP()) {
            $data['rp_point_balance'] = $this->integrateHelper->getRpIntegrateManagement()
                                                              ->getCurrentIntegrateModel()
                                                              ->getCurrentPointBalance(
                                                                  $customerId,
                                                                  $this->storeManager->getStore($storeId)->getWebsiteId());
        }

        return $data;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductModel() {
        return $this->productFactory->create();
    }
}
