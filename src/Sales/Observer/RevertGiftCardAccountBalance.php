<?php
/**
 * Created by Hung Nguyen - hungnh@smartosc.com
 * Date: 27/08/2018
 * Time: 16:10
 */

namespace SM\Sales\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class RevertGiftCardAccountBalance
 *
 * @package SM\Sales\Observer
 */
class RevertGiftCardAccountBalance implements ObserverInterface {

    const STATUS_DISABLED = 0;
    const STATE_EXPIRED   = 3;

    /**
     * Gift card account giftcardaccount
     *
     * @var \Magento\GiftCardAccount\Model\GiftcardaccountFactory
     */
    protected $giftCAFactory;
    /**
     * @var \SM\Integrate\Helper\Data
     */
    protected $integrateHelperData;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \SM\Integrate\Helper\Data $integrateHelperData,
        ObjectManagerInterface $objectManager
    ) {
        $this->integrateHelperData = $integrateHelperData;
        $this->objectManager       = $objectManager;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        if (!$this->integrateHelperData->isGiftCardMagento2EE())
            return;

        $order = $observer->getData('order');
        if ($order) {
            $this->_revertGiftCardsForOrder($order);
        }

        return $this;
    }

    /**
     * Revert authorized amounts for all order's gift cards
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return $this
     */
    protected function _revertGiftCardsForOrder(\Magento\Sales\Model\Order\Creditmemo $order) {
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $item) {
            if ($item->getOrderItem()->getData('product_type') == \Magento\GiftCard\Model\Catalog\Product\Type\Giftcard::TYPE_GIFTCARD) {
                $productOptions = $item->getOrderItem()->getProductOptions();
                if (isset($productOptions['giftcard_created_codes'])) {
                    $giftCardCode = $productOptions['giftcard_created_codes'][0];
                    $this->_revertByCode($giftCardCode);
                }
            }
        }

        return $this;
    }

    /**
     * Revert amount to gift card
     *
     * @param int   $id
     * @param float $amount
     *
     * @return $this
     */
    protected function _revertByCode($code = null) {
        /** @var \Magento\GiftCardAccount\Model\Giftcardaccount $giftCard */
        $giftCard = $this->getGiftCardRepository()->loadByCode($code);

        if ($giftCard) {
            $giftCard->setData('status', self::STATUS_DISABLED)
                     ->setData('state', self::STATE_EXPIRED)
                     ->save();
        }

        return $this;
    }

    /*
     * Gift card account giftcardaccount
     *
     * @var \Magento\GiftCardAccount\Model\GiftcardaccountFactory
     */
    protected function getGiftCardRepository() {
        if ($this->integrateHelperData->isGiftCardMagento2EE() && is_null($this->giftCAFactory)) {
            $this->giftCAFactory = $this->objectManager->create('\Magento\GiftCardAccount\Model\Giftcardaccount');
        }

        return $this->giftCAFactory;
    }
}
