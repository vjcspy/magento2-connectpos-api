<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 10/01/2017
 * Time: 14:06
 */

namespace SM\Sales\Repositories;


use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

/**
 * Class InvoiceManagement
 *
 * @package SM\Sales\Repositories
 */
class InvoiceManagement extends ServiceAbstract {

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * @var \SM\Sales\Repositories\OrderHistoryManagement
     */
    protected $orderHistoryManagement;

    /**
     * @var \SM\Shift\Model\RetailTransactionFactory
     */
    protected $retailTransactionFactory;
    private   $shiftHelper;

    /**
     * @var \SM\XRetail\Helper\Data
     */
    private $retailHelper;


    /**
     * InvoiceManagement constructor.
     *
     * @param \Magento\Framework\App\RequestInterface    $requestInterface
     * @param \SM\XRetail\Helper\DataConfig              $dataConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \SM\XRetail\Helper\DataConfig $dataConfig,
        \SM\XRetail\Helper\Data $retailHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \SM\Sales\Repositories\OrderHistoryManagement $orderHistoryManagement,
        \SM\Shift\Model\RetailTransactionFactory $retailTransactionFactory,
        \SM\Shift\Helper\Data $shiftHelper
    ) {
        $this->orderHistoryManagement   = $orderHistoryManagement;
        $this->orderFactory             = $orderFactory;
        $this->shipmentSender           = $shipmentSender;
        $this->invoiceSender            = $invoiceSender;
        $this->registry                 = $registry;
        $this->invoiceService           = $invoiceService;
        $this->objectManager            = $objectManager;
        $this->retailTransactionFactory = $retailTransactionFactory;
        $this->shiftHelper              = $shiftHelper;
        $this->retailHelper             = $retailHelper;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    public function invoice($orderId) {
        try {
            $invoiceData  = $this->getRequest()->getParam('invoice', []);
            $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
            }

            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }

            $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);

            if (!$invoice) {
                throw new LocalizedException(__('We can\'t save the invoice right now.'));
            }

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            $this->registry->unregister('current_invoice');
            $this->registry->register('current_invoice', $invoice);
            if (!empty($data['capture_case'])) {
                $invoice->setRequestedCaptureCase($data['capture_case']);
            }

            if (!empty($data['comment_text'])) {
                $invoice->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );

                $invoice->setCustomerNote($data['comment_text']);
                $invoice->setCustomerNoteNotify(isset($data['comment_customer_notify']));
            }

            $invoice->register();

            $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $invoice->getOrder()->setIsInProcess(true);

            $order           = $invoice->getOrder();
            $transactionSave = $this->objectManager->create(
                'Magento\Framework\DB\Transaction'
            )->addObject(
                $invoice
            )->addObject(
                $order
            );
            $shipment        = false;
            if (!empty($data['do_shipment']) || (int)$invoice->getOrder()->getForcedShipmentWithInvoice()) {
                $shipment = $this->_prepareShipment($invoice);
                if ($shipment) {
                    $transactionSave->addObject($shipment);
                }
            }
            $transactionSave->save();

            //if (isset($shippingResponse) && $shippingResponse->hasErrors()) {
            //    $order->setData(
            //        'retail_note',
            //        $order->getData('retail_note') .
            //        __(
            //            'The invoice and the shipment  have been created. ' .
            //            'The shipping label cannot be created now.'
            //        )
            //    );
            //}
            //elseif (!empty($data['do_shipment'])) {
            //    $order->setData('retail_note', $order->getData('retail_note') . __('You created the invoice and shipment.'));
            //}
            //else {
            //    $order->setData('retail_note', $order->getData('retail_note') . __('The invoice has been created.'));
            //}

            // send invoice/shipment emails
            try {
                if (!empty($data['send_email'])) {
                    $this->invoiceSender->send($invoice);
                }
            }
            catch (\Exception $e) {
                $this->objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }
            if ($shipment) {
                try {
                    if (!empty($data['send_email'])) {
                        $this->shipmentSender->send($shipment);
                    }
                }
                catch (\Exception $e) {
                    $this->objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                }
            }
            $this->objectManager->get('Magento\Backend\Model\Session')->getCommentText(true);

        }
        catch (LocalizedException $e) {
            throw new \Exception($e->getMessage());
        }
        catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $order;
    }

    /**
     * @param $order
     *
     * @throws \Exception
     */
    public function checkPayment($order) {
        if ($order instanceof \Magento\Sales\Model\Order) {
        }
        else {
            $orderModel = $this->orderFactory->create();
            $order      = $orderModel->load($order);
        }
        if ($order->getPayment()->getMethod() == \SM\Payment\Model\RetailMultiple::PAYMENT_METHOD_RETAILMULTIPLE_CODE) {
            $paymentData = json_decode($order->getPayment()->getAdditionalInformation('split_data'), true);
            if (is_array($paymentData)) {
                $payments  = array_filter(
                    $paymentData,
                    function ($val) {
                        return is_array($val);
                    });
                $totalPaid = 0;
                foreach ($payments as $payment) {
                    $totalPaid += floatval($payment['amount']);
                }
                if ($totalPaid - floatval($order->getGrandTotal()) > 0.01) {
                    // in production we will not check this.
                    //throw new \Exception("Sorry, Not allow paid lager than grand total");
                }

                if ((abs($totalPaid - floatval($order->getGrandTotal())) < 0.07) || !!$order->getData('is_exchange')) {
                    // FULL PAID
                    if ($order->canInvoice())
                        $order = $this->invoice($order->getId());

                    if (!$order->hasCreditmemos()) {
                        if (($order->getData('retail_has_shipment') && $order->getShippingMethod() == "retailshipping_retailshipping") ||
                            (in_array('can_not_create_shipment_with_negative_qty', \SM\Sales\Repositories\OrderManagement::$MESSAGE_ERROR))) {
                            if ($order->canShip()) {
                                if (!$order->getData('is_exchange'))
                                    $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_COMPLETE_NOT_SHIPPED);
                                else
                                    $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_EXCHANGE_NOT_SHIPPED);
                            }
                            else {
                                if (!$order->getData('is_exchange'))
                                    $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_COMPLETE_SHIPPED);
                                else
                                    $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_EXCHANGE_SHIPPED);
                            }
                        }
                        else {
                            if (!$order->getData('is_exchange'))
                                $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_COMPLETE);
                            else
                                $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_EXCHANGE);
                        }
                    }
                    else {
                        if ($order->getState() == \Magento\Sales\Model\Order::STATE_CLOSED) {
                            $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_FULLY_REFUND);
                        }
                        else {
                            if ($order->getData('retail_has_shipment') && $order->getShippingMethod() == "retailshipping_retailshipping") {
                                if ($order->canShip()) {
                                    $order->setData(
                                        'retail_status',
                                        \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_PARTIALLY_REFUND_NOT_SHIPPED);
                                }
                                else {
                                    $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_PARTIALLY_REFUND_SHIPPED);
                                }
                            }
                            else {
                                $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_PARTIALLY_REFUND);
                            }
                        }
                    }
                }
                else {
                    // PARTIALLY
                    if (!$order->hasCreditmemos()) {
                        if ($order->getData('retail_has_shipment') && $order->getShippingMethod() == "retailshipping_retailshipping") {
                            if ($order->canShip()) {
                                $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_PARTIALLY_PAID_NOT_SHIPPED);
                            }
                            else {
                                $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_PARTIALLY_PAID_SHIPPED);
                            }
                        }
                        else {
                            $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_PARTIALLY_PAID);
                        }
                    }
                    else {
                        if ($order->canCreditmemo()) {
                            if (($order->getData('retail_has_shipment') && $order->getShippingMethod() == "retailshipping_retailshipping") ||
                                (in_array('can_not_create_shipment_with_negative_qty', \SM\Sales\Repositories\OrderManagement::$MESSAGE_ERROR))) {
                                if ($order->canShip()) {
                                    $order->setData(
                                        'retail_status',
                                        \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_PARTIALLY_REFUND_NOT_SHIPPED);
                                }
                                else {
                                    $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_PARTIALLY_REFUND_SHIPPED);
                                }
                            }
                            else {
                                $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_PARTIALLY_REFUND);
                            }
                        }
                        else {
                            // full refund
                            $order->setData('retail_status', \SM\Sales\Repositories\OrderManagement::RETAIL_ORDER_FULLY_REFUND);
                        }
                    }
                }
                $order->save();
            }
            else {

            }
        }
        else {
        }
    }


    /**
     * Add payment to order created by X-Retail, this means adding a transaction
     * Function will add data in order payment and transaction
     *
     * @param null $data
     *
     * @param bool $isRefunding
     *
     * @return array
     * @throws \Exception
     */
    public function addPayment($data = null, $isRefunding = false) {
        if (is_null($data))
            $data = $this->getRequest()->getParams();
        if (isset($data['order_id']) && isset($data['payment_data']) && is_array($data['payment_data'])) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderFactory->create();
            $order->load($data['order_id']);

            if (!$order->getId()) {
                throw new \Exception("Can not find order");
            }

            if ($order->getPayment()->getMethod() != \SM\Payment\Model\RetailMultiple::PAYMENT_METHOD_RETAILMULTIPLE_CODE)
                throw new \Exception("Can't add payment for order which haven't created from XRetail");

            $splitData = json_decode($order->getPayment()->getAdditionalInformation('split_data'), true);
            foreach ($data['payment_data'] as $payment) {
                $splitData[] = $payment;
            }
            $currentShift = $this->shiftHelper->getShiftOpening($data['outlet_id'], $data['register_id']);
            $shiftId      = $currentShift->getId();
            if (!$shiftId) {
                throw new \Exception("No shift are opening");
            }

            if ($isRefunding) {
                if (count($data['payment_data']) > 2) {
                    throw new \Exception("Refund only accept one payment method");
                }
                $created_at =  $this->retailHelper->getCurrentTime();
                // within cash rounding payment
                foreach ($data['payment_data'] as $payment_datum) {
                    $created_at =$this->retailHelper->getCurrentTime();
                    $transactionData  = [
                        "payment_id"    => isset($payment_datum['id']) ? $payment_datum['id'] : null,
                        "shift_id"      => $shiftId,
                        "outlet_id"     => $data['outlet_id'],
                        "register_id"   => $data['register_id'],
                        "payment_title" => $payment_datum['title'],
                        "payment_type"  => $payment_datum['type'],
                        "amount"        => floatval($payment_datum['amount']),
                        "is_purchase"   => 0,
                        "created_at" => $created_at,
                        "order_id" => $data['order_id']
                    ];
                    $transactionModel = $this->getRetailTransactionModel();
                    $transactionModel->addData($transactionData)->save();
                }
            }
            else {
                foreach ($data['payment_data'] as $payment_datum) {
                    $created_at       = $this->retailHelper->getCurrentTime();
                    $transactionData  = [
                        "payment_id"    => isset($payment_datum['id']) ? $payment_datum['id'] : null,
                        "shift_id"      => $shiftId,
                        "outlet_id"     => $data['outlet_id'],
                        "register_id"   => $data['register_id'],
                        "payment_title" => $payment_datum['title'],
                        "payment_type"  => $payment_datum['type'],
                        "amount"        => floatval($payment_datum['amount']),
                        "is_purchase"   => 1,
                        "created_at"    => $created_at,
                        "order_id"      => $data['order_id']
                    ];
                    $transactionModel = $this->getRetailTransactionModel();
                    $transactionModel->addData($transactionData)->save();
                }
            }

            $order->getPayment()->setAdditionalInformation('split_data', json_encode($splitData))->save();
            $this->checkPayment($order->getEntityId());

            $criteria = new DataObject(['entity_id' => $order->getEntityId(), 'storeId' => $data['store_id'], 'outletId' => $data['outlet_id']]);

            return $this->orderHistoryManagement->loadOrders($criteria);
        }
        else
            throw new \Exception("Must required data");
    }

    public function takePayment() {
        return $this->addPayment($this->getRequest()->getParams(), false);
    }

    /**
     * @return \SM\Shift\Model\RetailTransaction
     */
    protected function getRetailTransactionModel() {
        return $this->retailTransactionFactory->create();
    }
}
