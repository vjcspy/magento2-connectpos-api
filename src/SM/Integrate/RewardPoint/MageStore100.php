<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 20/03/2017
 * Time: 18:00
 */

namespace SM\Integrate\RewardPoint;

use SM\Integrate\RewardPoint\Contract\RPIntegrateInterface;

/**
 * Class MageStore100
 *
 * @package SM\Integrate\RewardPoint
 */
class MageStore100 implements RPIntegrateInterface {

    /**
     * @inheritdoc
     */
    function saveRPDataBeforeQuoteCollect($data) {
        // TODO: Implement saveRPDataBeforeQuoteCollect() method.
    }

    /**
     * @inheritdoc
     */
    function getQuoteRPData() {
        // TODO: Implement getRPDataAfterQuoteCollect() method.
    }

    /**
     * @inheritdoc
     */
    function getCurrentPointBalance($customerId, $scope = null) {
        // TODO: Implement getCurrentPointBalance() method.
    }

    function getTransactionByOrder(){
        // TODO: Implement getCurrentPointBalance() method.
    }
}