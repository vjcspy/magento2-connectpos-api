<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 4/5/17
 * Time: 2:24 PM
 */

namespace SM\Performance\Observer\BatchData;


use Magento\Framework\Event\Observer;

class ProcessBatch implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \SM\Performance\Helper\RealtimeManager
     */
    private $realtimeManager;
    /**
     * @var \SM\XRetail\Model\Shell\Process
     */
    private $process;
    /**
     * @var \Magento\Config\Model\Config\Loader
     */
    private $configLoader;

    public function __construct(
        \SM\Performance\Helper\RealtimeManager $realtimeManager,
        \SM\XRetail\Model\Shell\Process $process,
        \Magento\Config\Model\Config\Loader $loader
    ) {
        $this->realtimeManager = $realtimeManager;
        $this->process         = $process;
        $this->configLoader  = $loader;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        if (count($this->realtimeManager->getBatchData()) > 0) {
            $config = $this->configLoader->getConfigByPath('xpos/advance', 'default', 0);
            if (isset($config['xpos/advance/sync_realtime']) && $config['xpos/advance/sync_realtime']['value'] == 'cronjob') {
                $this->realtimeManager->processBatchData();
            }
            else {
                $this->process
                    ->setCommand("bin/magento retail:sendrealtime " . "'" . json_encode($this->realtimeManager->getBatchData()) . "'")
                    ->start();
            }
        }
    }
}