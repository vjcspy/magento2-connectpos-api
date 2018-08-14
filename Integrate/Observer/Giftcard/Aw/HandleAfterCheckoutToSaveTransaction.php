<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 3/5/18
 * Time: 16:56
 */

namespace SM\Integrate\Observer\Giftcard\Aw;


use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;

class HandleAfterCheckoutToSaveTransaction implements \Magento\Framework\Event\ObserverInterface {


    /**
     * @var \SM\Shift\Model\RetailTransactionFactory
     */
    private $retailTransactionFactory;
    /**
     * @var \SM\Shift\Helper\Data
     */
    private $shiftHelperData;
    /**
     * @var \SM\Payment\Helper\PaymentHelper
     */
    private $paymentHelper;

    /**
     * HandleAfterCheckoutToSaveTransaction constructor.
     *
     * @param \SM\Shift\Model\RetailTransactionFactory $transactionFactory
     * @param \SM\Shift\Helper\Data                    $shiftHelperData
     * @param \SM\Payment\Helper\PaymentHelper         $paymentHelper
     */
    public function __construct(
        \SM\Shift\Model\RetailTransactionFactory $transactionFactory,
        \SM\Shift\Helper\Data $shiftHelperData,
        \SM\Payment\Helper\PaymentHelper $paymentHelper
    ) {
        $this->shiftHelperData          = $shiftHelperData;
        $this->retailTransactionFactory = $transactionFactory;
        $this->paymentHelper            = $paymentHelper;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer) {
        /** @var Order $order */
        $order = $observer->getData('order');

        if ($order->getData('retail_id') && $order->getData('aw_giftcard_amount')) {
            $outletId          = $order->getData('outlet_id');
            $registerId        = $order->getData('register_id');
            $currentShift      = $this->shiftHelperData->getShiftOpening($outletId, $registerId);
            $giftCardPaymentId = $this->paymentHelper->getPaymentIdByType(\SM\Payment\Model\RetailPayment::GIFT_CARD_PAYMENT_TYPE);
            if ($currentShift->getData('id') && $giftCardPaymentId) {
                $transaction = $this->getRetailTransactionModel();
                $transaction->setData('payment_id', $giftCardPaymentId)
                            ->setData('shift_id', $currentShift->getData('id'))
                            ->setData('outlet_id', $outletId)
                            ->setData('register_id', $registerId)
                            ->setData('payment_type', \SM\Payment\Model\RetailPayment::GIFT_CARD_PAYMENT_TYPE)
                            ->setData('amount', $order->getData('aw_giftcard_amount'))
                            ->setData('is_purchase', 1)
                            ->setData('order_id', $order->getEntityId())
                            ->save();
            }
        }
    }

    /**
     * @return \SM\Shift\Model\RetailTransaction
     */
    protected function getRetailTransactionModel() {
        return $this->retailTransactionFactory->create();
    }
}
