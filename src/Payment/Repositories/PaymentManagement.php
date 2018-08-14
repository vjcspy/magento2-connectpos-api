<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 15/01/2017
 * Time: 20:31
 */

namespace SM\Payment\Repositories;


use Magento\Framework\DataObject;
use SM\Core\Api\SearchResult;
use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;
use SM\Core\Api\Data\XPayment;

class PaymentManagement extends ServiceAbstract {

    /**
     * @var \SM\Payment\Model\RetailPaymentFactory
     */
    protected $retailPaymentFactory;
    /**
     * @var \SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory
     */
    protected $paymentCollectionFactory;

    /**
     * PaymentManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                         $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                                   $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \SM\Payment\Model\RetailPaymentFactory                          $retailPaymentFactory
     * @param \SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory $paymentCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SM\Payment\Model\RetailPaymentFactory $retailPaymentFactory,
        \SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory $paymentCollectionFactory
    ) {
        $this->retailPaymentFactory     = $retailPaymentFactory;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     */
    public function getPaymentData() {
        return $this->load($this->getSearchCriteria())->getOutput();
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return SearchResult
     */
    public function load(DataObject $searchCriteria) {
        if (is_null($searchCriteria) || !$searchCriteria)
            $searchCriteria = $this->getSearchCriteria();

        $this->getSearchResult()->setSearchCriteria($searchCriteria);
        $collection = $this->getPaymentCollection($searchCriteria);
        $items      = [];
        if ($collection->getLastPageNumber() < $searchCriteria->getData('currentPage')) {
        }
        else {
            foreach ($collection as $payment) {
                $paymentData = new XPayment();
                $paymentData->addData($payment->getData());
                $items[] = $paymentData;
            }
        }

        return $this->getSearchResult()
                    ->setItems($items)
                    ->setTotalCount($collection->getSize())
                    ->setLastPageNumber($collection->getLastPageNumber());
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return \SM\Payment\Model\ResourceModel\RetailPayment\Collection
     */
    public function getPaymentCollection(DataObject $searchCriteria) {
        /** @var \SM\Payment\Model\ResourceModel\RetailPayment\Collection $collection */
        $collection = $this->paymentCollectionFactory->create();

        $collection->setCurPage(is_nan($searchCriteria->getData('currentPage')) ? 1 : $searchCriteria->getData('currentPage'));
        $collection->setPageSize(
            is_nan($searchCriteria->getData('pageSize')) ? DataConfig::PAGE_SIZE_LOAD_DATA : $searchCriteria->getData('pageSize')
        );

        return $collection;
    }

    protected function dummyPayment() {
        $payments = [
            [
                'type'     => "cash",
                'title'    => "Cash",
                'is_dummy' => 1
            ],
            [
                'type'     => "credit_card",
                'title'    => "Credit Card",
                'is_dummy' => 1
            ],
            [
                'type'     => "credit_card",
                'title'    => "Debit Card",
                'is_dummy' => 1
            ],
            [
                'type'     => "credit_card",
                'title'    => "Visa Card",
                'is_dummy' => 1
            ]
        ];
        foreach ($payments as $pData) {
            $payment = $this->retailPaymentFactory->create();
            $payment->addData($pData)->save();
        }
    }

    public function savePayment() {
        $listPayment = $this->getRequestData();
        $items       = [];
        foreach ($listPayment['payment_data'] as $pData) {
            $paymentData = new XPayment();
            if (isset($pData['payment_data'])) {
                $pData['payment_data'] = json_encode($pData['payment_data']);
            }
            if (isset($pData['id']) && $pData['id'] && $pData['id'] < 1481282470403) {
                $payment = $this->retailPaymentFactory->create();
                $payment->addData($pData)->save();
                $items[] = $paymentData->addData($payment->getData());
            }
            else {
                $pData['id']   = null;
                $pData['type'] = "credit_card";
                $payment       = $this->retailPaymentFactory->create();
                $payment->setData($pData)->save();
                $items[] = $paymentData->addData($payment->getData());
            }
        }

        return $this->getSearchResult()
                    ->setItems($items)
                    ->setTotalCount(1)
                    ->setLastPageNumber(1)->getOutput();
    }
}