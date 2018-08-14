<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 20/06/2016
 * Time: 10:44
 */

namespace SM\XRetail\Observer;


use Magento\Framework\Event\ObserverInterface;

class ApiControllerBefore implements ObserverInterface {

    public function __construct() {
    }

    /**
     * Customer login bind process
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var \SM\XRetail\Controller\V1\Xretail $apiController */
        $apiController = $observer->getData('apiController');

        // get data as json
        if (!is_null($data = json_decode(file_get_contents('php://input'), true)))
            $apiController->getRequest()->setParams($data);

        $apiController->checkPath();

        return $this;
    }
}