<?php
namespace SM\Shipping\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SaveOutletInformationToOrder implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectmanager)
    {
        $this->_objectManager = $objectmanager;
    }

    public function execute(EventObserver $observer) {
        $order           = $observer->getOrder();
        $quoteRepository = $this->_objectManager->create('Magento\Quote\Model\QuoteRepository');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $quoteRepository->get($order->getQuoteId());
        if ($quote->getOutletId() != null) {
            $order->setOutletId($quote->getOutletId());
        }

        return $this;
    }
}