<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 24/03/2017
 * Time: 14:13
 */

namespace SM\Performance\Plugin;

use SM\Performance\Helper\RealtimeManager;


/**
 * Class RealTimeTax
 *
 * @package SM\Performance\Plugin
 */
class RealTimeTax {

    /**
     * @var \SM\Performance\Helper\RealtimeManager
     */
    protected $realtimeManager;

    /**
     * RealTimeTax constructor.
     *
     * @param \SM\Performance\Helper\RealtimeManager $realtimeManager
     */
    public function __construct(
        \SM\Performance\Helper\RealtimeManager $realtimeManager
    ) {
        $this->realtimeManager = $realtimeManager;
    }

    /**
     * @param $subject
     * @param $result
     *
     * @return string
     */
    public function afterSave($subject, $result) {
        $this->realtimeManager->trigger(RealtimeManager::TAX_ENTITY, 'all', RealtimeManager::TYPE_CHANGE_UPDATE);

        return $result;
    }

}