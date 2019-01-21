<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 09/12/2016
 * Time: 10:05
 */

namespace SM\Customer\Helper;

use Magento\Framework\DataObject;


/**
 * Class Data
 *
 * @package SM\Customer\Helper
 */
class Data {

    const DEFAULT_CUSTOMER_RETAIL_EMAIL = 'guest@sales.connectpos.com';
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    protected $configShare;

    /**
     * Data constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Config\Share       $configShare
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Config\Share $configShare
    ) {
        $this->storeManager = $storeManager;
        $this->configShare  = $configShare;
    }

    /**
     * @param $customerWebsiteId
     * @param $storeId
     *
     * @return bool
     */
    public function isCustomerInStore($customerWebsiteId, $storeId) {
        $ids = [];
        if ((bool)$this->configShare->isWebsiteScope()) {
            $ids = $this->storeManager->getWebsite($customerWebsiteId)->getStoreIds();
        }
        else {
            foreach ($this->storeManager->getStores() as $store) {
                $ids[] = $store->getId();
            }
        }

        return in_array($storeId, $ids);
    }

    public function transformCustomerData(&$customer) {
        if ($customer->getData('entity_id'))
            $customer->setData('id', $customer->getData('entity_id'));
        if ($customer->getData('first_name'))
            $customer->setData('firstname', $customer->getData('first_name'));
        if ($customer->getData('last_name'))
            $customer->setData('lastname', $customer->getData('last_name'));
        if ($customer->getData('middle_name'))
            $customer->setData('middlename', $customer->getData('middle_name'));
        if ($customer->getData('customer_group_id'))
            $customer->setData('group_id', $customer->getData('customer_group_id'));
        if ($customer->getData('telephone'))
            $customer->setData('retail_telephone', $customer->getData('telephone'));
        if ($customer->getData('retail_store_id'))
            $customer->setData('store_id', $customer->getData('retail_store_id'));
        if (is_bool($customer->getData('subscription')))
            $customer->setData('subscription', $customer->getData('subscription'));

        return $customer;
    }
}