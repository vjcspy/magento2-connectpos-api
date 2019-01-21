<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 10/01/2017
 * Time: 15:30
 */

namespace SM\Sales\Repositories;


use Magento\Framework\DataObject;
use SM\XRetail\Repositories\Contract\ServiceAbstract;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;

/**
 * Class ShipmentManagement
 *
 * @package SM\Sales\Repositories
 */
class ShipmentManagement extends ServiceAbstract {

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;
    /**
     * @var
     */
    protected $shipmentValidator;
    /**
     * @var \SM\Sales\Repositories\InvoiceManagement
     */
    protected $invoiceManagement;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    private   $orderHistoryManagement;

    static $FROM_API = false;

    // static $CREATE_SHIPMENT = false;

    /**
     * ShipmentManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface                     $requestInterface
     * @param \SM\XRetail\Helper\DataConfig                               $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface                  $storeManager
     * @param \Magento\Framework\ObjectManagerInterface                   $objectManager
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender      $shipmentSender
     * @param \SM\Sales\Repositories\InvoiceManagement                    $invoiceManagement
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        InvoiceManagement $invoiceManagement,
        \SM\Sales\Repositories\OrderHistoryManagement $orderHistoryManagement
    ) {
        $this->orderFactory           = $orderFactory;
        $this->invoiceManagement      = $invoiceManagement;
        $this->shipmentSender         = $shipmentSender;
        $this->shipmentLoader         = $shipmentLoader;
        $this->objectManager          = $objectManager;
        $this->orderHistoryManagement = $orderHistoryManagement;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    public function createClickAndCollectInvoice() {
        if (!($orderId = $this->getRequest()->getParam('order_id')))
            throw new \Exception("Must have param Order Id");

        if (!($storeId = $this->getRequest()->getParam('store_id')))
            throw new \Exception("Must have param Store Id");

        if (!($outletId = $this->getRequest()->getParam('outlet_id')))
            throw new \Exception("Must have param Outlet Id");

        $order = $this->objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $this->pick($orderId);
        $this->invoiceManagement->invoice($orderId);
        //$this->invoiceManagement->checkPayment($order);
        //$this->invoice($orderId);
        $criteria = new DataObject(['entity_id' => $orderId, 'storeId' => $storeId, 'outletId' => $outletId, 'isSearchOnline' => true]);

        return $this->orderHistoryManagement->loadOrders($criteria);
    }

    /**
     * @throws \Exception
     */
    public function createShipment() {
        self:: $FROM_API = true;
        if (!($orderId = $this->getRequest()->getParam('order_id')))
            throw new \Exception("Must have param Order Id");

        if (!($storeId = $this->getRequest()->getParam('store_id')))
            throw new \Exception("Must have param Store Id");

        $outletId = $this->getRequest()->getParam('outlet_id');

        $order = $this->ship($orderId);
        $this->invoiceManagement->checkPayment($order);

        $criteria = new DataObject(['entity_id' => $order->getEntityId(), 'storeId' => $storeId, 'outletId' => $outletId]);

        return $this->orderHistoryManagement->loadOrders($criteria);
    }

    /**
     * @param $orderId
     *
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    public function pick($orderId) {
        $retail_status = $this->getRequest()->getParam('retail_status');
        $orderModel    = $this->orderFactory->create();
        $order         = $orderModel->load($orderId);

        if (!$order->getId()) {
            throw new \Exception("Can not find order");
        }

        $order->setData('retail_status', $retail_status);
        $order->save();

        return $order;
    }

    /**
     * @param $orderId
     *
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    public function ship($orderId) {
        if (!empty($data['comment_text'])) {
            $this->objectManager->get('Magento\Backend\Model\Session')->setCommentText($data['comment_text']);
        }
        try {
            $this->shipmentLoader->setOrderId($orderId);
            $shipment = $this->shipmentLoader->load();
            if (!$shipment) {
                throw new \Exception("Can't create shipment");
            }

            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
            }
            $validationResult = $this->getShipmentValidator()
                                     ->validate($shipment, [QuantityValidator::class]);

            if ($validationResult->hasMessages()) {
                throw new \Exception(
                    __("Shipment Document Validation Error(s):\n" . implode("\n", $validationResult->getMessages()))
                );
            }
            $shipment->register();

            $this->_saveShipment($shipment);

            if (!empty($data['send_email'])) {
                $this->shipmentSender->send($shipment);
            }
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new \Exception($e->getMessage());
        }
        catch (\Exception $e) {
            $this->objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            throw new \Exception($e->getMessage());
        }

        return $shipment->getOrder();
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     *
     * @return $this
     */
    protected function _saveShipment($shipment) {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->objectManager->create(
            'Magento\Framework\DB\Transaction'
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface
     */
    private function getShipmentValidator() {
        if ($this->shipmentValidator === null) {
            $this->shipmentValidator = $this->objectManager->get(
                \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface::class
            );
        }

        return $this->shipmentValidator;
    }
}
