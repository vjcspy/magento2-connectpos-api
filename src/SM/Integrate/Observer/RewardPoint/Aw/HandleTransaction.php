<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 3/5/18
 * Time: 16:20
 */

namespace SM\Integrate\Observer\RewardPoint\Aw;

use Magento\Framework\Event\Observer;

class HandleTransaction implements \Magento\Framework\Event\ObserverInterface {

    //public function __construct(
    //    /
    //) { }

    /**
     * @param Observer $observer
     *
     * Handle aw save transaction to push data to sm_transaction
     *
     * @return void
     */
    public function execute(Observer $observer) {
        $object = $observer->getData('object');

        if (!class_exists("\Aheadworks\RewardPoints\Model\Transaction")) {
            return;
        }

        //if ($object instanceof \Aheadworks\RewardPoints\Model\Transaction) {
        //    $transactionData = [
        //        "id"        => $object->getTransactionId(),
        //        "balance"   => $object->getBalance(),
        //        "entity_id" => $object->getEntityId()
        //    ];
        //}

        /*
         * Because aw_rp didn't save enough data to transaction table and getting data take a lot of time so we just save spent data in sale_order
         * TODO: if we could implement reward point extension later, we have to save pont_spent and point_earn to order table.
         * */
    }
}
