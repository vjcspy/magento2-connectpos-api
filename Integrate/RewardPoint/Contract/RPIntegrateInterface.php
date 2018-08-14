<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 20/03/2017
 * Time: 17:24
 */

namespace SM\Integrate\RewardPoint\Contract;


use SM\Integrate\Data\RewardPointQuoteData;

interface RPIntegrateInterface {

    /**
     * @param $data
     *
     * @return void
     */
    function saveRPDataBeforeQuoteCollect($data);

    /**
     * @return RewardPointQuoteData
     */
    function getQuoteRPData();

    /**
     *
     * @param      $customerId
     * @param null $scope
     *
     * @return int
     */
    function getCurrentPointBalance($customerId, $scope = null);
}