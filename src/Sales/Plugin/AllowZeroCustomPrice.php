<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 24/03/2017
 * Time: 16:45
 */

namespace SM\Sales\Plugin;

use \Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\DataObject;

/**
 * Class AllowZeroCustomPrice
 *
 * @package SM\Sales\Plugin
 */
class AllowZeroCustomPrice {

    /**
     * Magento có bug là check empty của custom price khi mới add vào quote nên giá = 0  thì không thể set được.
     * Còn ngược lại khi update item thì lại kiêm tra số nên vẫn add được 0 vào.
     * Sửa lại cách lấy custom price của magento
     *
     * @param \Magento\Quote\Model\Quote\Item\Processor $subject
     * @param callable                                  $proceed
     * @param \Magento\Quote\Model\Quote\Item           $item
     * @param \Magento\Framework\DataObject             $request
     * @param \Magento\Catalog\Model\Product            $candidate
     */
    public function aroundPrepare(
        \Magento\Quote\Model\Quote\Item\Processor $subject,
        callable $proceed,
        Item $item,
        DataObject $request,
        Product $candidate
    ) {
        $proceed($item, $request, $candidate);

        $customPrice = $request->getCustomPrice();
        if (!is_null($customPrice)) {
            $customPrice = $customPrice > 0 ? $customPrice : 0;
            $item->setCustomPrice($customPrice);
            $item->setOriginalCustomPrice($customPrice);
        }
    }
}