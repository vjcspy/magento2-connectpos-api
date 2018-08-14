<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 08/11/2016
 * Time: 16:08
 */

namespace SM\Setting\Repositories\SettingManagement;

use Magento\Framework\DataObject;


/**
 * Class Customer
 *
 * @package SM\Setting\Repositories\SettingManagement
 */
class Customer extends AbstractSetting implements SettingInterface {

    /**
     * @var string
     */
    protected $CODE = 'customer';
    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    protected $customerGroupManagement;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \SM\Customer\Repositories\CustomerManagement
     */
    protected $customerManagement;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Customer\Helper\Address
     */
    private $customerAddressHelper;

    /**
     * Customer constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Api\GroupManagementInterface     $customerGroupManagement
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Api\GroupManagementInterface $customerGroupManagement,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \SM\Customer\Repositories\CustomerManagement $customerManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Helper\Address $customerAddressHelper
    ) {
        $this->storeManager            = $storeManager;
        $this->customerManagement      = $customerManagement;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->customerRepository      = $customerRepository;
        $this->customerAddressHelper   = $customerAddressHelper;
        parent::__construct($scopeConfig);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function build() {
        // TODO: Implement build() method.
        $defaultCustomerId   = $this->getDefaultCustomerId();
        $loadDefaultCustomer = $this->customerManagement->loadCustomers(
            new DataObject(['entity_id' => $defaultCustomerId, 'storeId' => $this->getStore()]));
        if ($loadDefaultCustomer && count($loadDefaultCustomer->getItems()) > 0) {
            $defaultCustomer = $loadDefaultCustomer->getItems()[0]->getOutput();
        }
        else {
            /*
             * NOTE: if customer has been creaated but still throw this error, Pls run reindex.
             */
            throw new \Exception("Can't get default customer");
        }

        return [
            'default_customer_tax_class'       => $this->customerGroupManagement->getDefaultGroup($this->getStore())->getTaxClassId(),
            'not_logged_In_customer_tax_class' => $this->customerGroupManagement->getNotLoggedInGroup()->getTaxClassId(),
            'default_customer_id'              => $defaultCustomerId,
            'default_customer'                 => $defaultCustomer,
            'street_lines'                     => $this->customerAddressHelper->getStreetLines($this->getStore())
        ];
    }

    /**
     * @return int|null
     */
    private function getDefaultCustomerId() {
        try {
            $customer = $this->customerRepository->get(\SM\Customer\Helper\Data::DEFAULT_CUSTOMER_RETAIL_EMAIL, $this->getWebsiteId());
        }
        catch (\Exception $e) {
            $customer = null;
        }
        if (!is_null($customer) && $customer->getId()) {
            return $customer->getId();
        }
        else {
            $data = [
                "group_id"    => $this->customerGroupManagement->getDefaultGroup($this->getStore())->getId(),
                "email"       => \SM\Customer\Helper\Data::DEFAULT_CUSTOMER_RETAIL_EMAIL,
                "first_name"  => "Guest",
                "last_name"   => "Customer",
                "middle_name" => "",
                "prefix"      => "",
                "suffix"      => "",
                "gender"      => 0,
                "storeId"     => $this->getStore(),
            ];

            return $this->customerManagement->create(['customer' => $data, 'storeId' => $this->getStore()]);
        }
    }

    /**
     * @return int
     */
    private function getWebsiteId() {
        return $this->storeManager->getStore($this->getStore())->getWebsiteId();
    }
}