<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 7/12/17
 * Time: 11:08 AM
 */

namespace SM\XRetail\Repositories;

class DebugManagement {

    private $orderFactory;
    private $orderManagement;
    private $invoiceManagement;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \SM\Sales\Repositories\InvoiceManagement $invoiceManagement
    ) {
        $this->orderFactory      = $orderFactory;
        $this->invoiceManagement = $invoiceManagement;
    }

    public function debug() {
        echo shell_exec('php bin/magento');die;
    }
}