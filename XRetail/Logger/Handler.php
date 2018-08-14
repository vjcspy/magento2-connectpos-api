<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 13/03/2017
 * Time: 10:53
 */

namespace SM\XRetail\Logger;


use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base {

    protected $fileName   = '/var/log/xretail/retail.log';
    protected $loggerType = Logger::DEBUG;
}