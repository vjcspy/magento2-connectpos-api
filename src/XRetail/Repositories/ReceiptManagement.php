<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 19/01/2017
 * Time: 14:47
 */

namespace SM\XRetail\Repositories;


use Magento\Framework\DataObject;
use SM\Core\Api\Data\XReceipt;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class ReceiptManagement
 *
 * @package SM\XRetail\Repositories
 */
class ReceiptManagement extends ServiceAbstract {

    /**
     * @var \SM\XRetail\Model\ResourceModel\Receipt\CollectionFactory
     */
    protected $receiptCollectionFactory;
    /**
     * @var \SM\XRetail\Model\ReceiptFactory
     */
    protected $receiptFactory;

    /**
     * ReceiptManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                   $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                             $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager
     * @param \SM\XRetail\Model\ResourceModel\Receipt\CollectionFactory $receiptCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SM\XRetail\Model\ResourceModel\Receipt\CollectionFactory $receiptCollectionFactory,
        \SM\XRetail\Model\ReceiptFactory $receiptFactory
    ) {
        $this->receiptFactory           = $receiptFactory;
        $this->receiptCollectionFactory = $receiptCollectionFactory;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     */
    public function getReceiptData() {
        return $this->load($this->getSearchCriteria())->getOutput();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function save() {
        $data = $this->getRequestData();

        /** @var \SM\XRetail\Model\Receipt $receipt */
        $receipt = $this->receiptFactory->create();
        $id      = $data->getId();
        if ($id && $id < 1484901028405) {
            $receipt->load($id);
            if (!$receipt->getId())
                throw new \Exception("Can't find receipt");
        }
        $data->unsetData('id');

        if ($data->getData('is_default') == true) {
            $is_default = 1;
        }
        else {
            $is_default = 0;
        }
        $orderInfo = $data->getData('order_info');
        $data->setData('order_info', json_encode($orderInfo));

        $data->setData('is_default', $is_default);
        $receipt->addData($data->getData())->save();

        $searchCriteria = new DataObject(
            [
                'ids' => $receipt->getId()
            ]);

        return $this->load($searchCriteria)->getOutput();
    }

    public function delete() {
        $data = $this->getRequestData();
        if ($id = $data->getData('id')) {
            /** @var \SM\XRetail\Model\Receipt $receipt */
            $receipt = $this->receiptFactory->create();
            $receipt->load($id)->delete();
        }
        else {
            throw new \Exception("Please define id");
        }
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return \SM\Core\Api\SearchResult
     */
    public function load(DataObject $searchCriteria) {
        if (is_null($searchCriteria) || !$searchCriteria)
            $searchCriteria = $this->getSearchCriteria();

        $collection = $this->getReceiptCollection($searchCriteria);

        $items = [];
        if ($collection->getLastPageNumber() < $searchCriteria->getData('currentPage')) {
        }
        else
            foreach ($collection as $item) {
                $i = new XReceipt();
                $i->setData('custom_date', $this->initCustomDateTimeFormat($item));
                $items[] = $i->addData($item->getData());
            }

        return $this->getSearchResult()
                    ->setSearchCriteria($searchCriteria)
                    ->setItems($items)
                    ->setTotalCount($collection->getSize());
    }

    /**
     * @param \Magento\Framework\DataObject $searchCriteria
     *
     * @return \SM\XRetail\Model\ResourceModel\Receipt\Collection
     */
    public function getReceiptCollection(DataObject $searchCriteria) {
        /** @var \SM\XRetail\Model\ResourceModel\Receipt\Collection $collection */
        $collection = $this->receiptCollectionFactory->create();

        if ($searchCriteria->getData('ids')) {
            $collection->addFieldToFilter('id', ['in' => explode(",", $searchCriteria->getData('ids'))]);
        }

        return $collection;
    }

    private function initCustomDateTimeFormat($item) {
        return ((!empty($item['day_of_week']) && $item['day_of_week'] !== 0) ? ($item['day_of_week'].', ') : '')
               . ((!empty($item['day_of_month']) && $item['day_of_month'] !== 0) ? $item['day_of_month'] : '') . ' '
               . ((!empty($item['month']) && $item['month'] !== 0) ? $item['month'] : '') . ' '
               . ((!empty($item['year']) && $item['year'] !== 0) ? $item['year'] : '');
    }
}
