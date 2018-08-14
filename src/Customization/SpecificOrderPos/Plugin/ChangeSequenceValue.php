<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 8/8/18
 * Time: 10:37
 */

namespace SM\Customization\SpecificOrderPos\Plugin;

use Magento\Framework\App\ResourceConnection as AppResource;
use SM\Sales\Repositories\OrderManagement;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceSequenceMeta;

/**
 * Class ChangeSequenceValue
 *
 * @package SM\Customization\SpecificOrderPos\Plugin
 */
class ChangeSequenceValue {

    /**
     *
     */
    const SUPPORT_CHANGE_ORDER_INCREMENT = false;

    /**
     *
     */
    const SEQUENCE_TABLE_PREFIX = "pos_sequence_order_";

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $appResource;
    /**
     * @var \SM\Customization\Model\SequenceFactory
     */
    private $sequenceFactory;
    /**
     * @var \Magento\SalesSequence\Model\ResourceModel\Meta
     */
    private $resourceSequenceMeta;

    /**
     * ChangeSequenceValue constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection       $appResource
     * @param \SM\Customization\Model\SequenceFactory         $sequenceFactory
     * @param \Magento\SalesSequence\Model\ResourceModel\Meta $resourceSequenceMeta
     */
    public function __construct(
        AppResource $appResource,
        \SM\Customization\Model\SequenceFactory $sequenceFactory,
        ResourceSequenceMeta $resourceSequenceMeta
    ) {
        $this->appResource          = $appResource;
        $this->sequenceFactory      = $sequenceFactory;
        $this->resourceSequenceMeta = $resourceSequenceMeta;
    }

    /**
     * @param \Magento\SalesSequence\Model\Manager $subject
     * @param                                      $proceed
     * @param                                      $entityType
     * @param                                      $storeId
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetSequence(
        \Magento\SalesSequence\Model\Manager $subject,
        $proceed,
        $entityType,
        $storeId
    ) {
        $connection = $this->appResource->getConnection('sales');
        if ($entityType == "order" && OrderManagement::$FROM_API && self::SUPPORT_CHANGE_ORDER_INCREMENT) {
            if (!$this->isTableExisted($storeId)) {
                $connection->query($this->getCreateSequenceDdl($this->getSequenceTable($storeId)));
            }

            $sequence = $this->sequenceFactory->create(
                [
                    'meta' => $this->resourceSequenceMeta->loadByEntityTypeAndStore(
                        $entityType,
                        $storeId
                    )
                ]
            )->setSequenceTable($this->getSequenceTable($storeId));

            return $sequence;
        }
        else {
            return $proceed($entityType, $storeId);
        }
    }

    /**
     * @param $storeId
     *
     * @return bool
     */
    protected function isTableExisted($storeId) {
        $connection = $this->appResource->getConnection('sales');

        return $connection->isTableExists($this->getSequenceTable($storeId));
    }

    /**
     * @param $storeId
     *
     * @return string
     */
    protected function getSequenceTable($storeId) {
        $connection = $this->appResource->getConnection('sales');

        return $connection->getTableName(ChangeSequenceValue::SEQUENCE_TABLE_PREFIX . $storeId);
    }


    /**
     * @param        $name
     * @param int    $startNumber
     * @param string $columnType
     * @param bool   $unsigned
     *
     * @return string
     */
    protected function getCreateSequenceDdl(
        $name,
        $startNumber = 1,
        $columnType = \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        $unsigned = true
    ) {
        $format
            = "CREATE TABLE %s (
                     sequence_value %s %s NOT NULL AUTO_INCREMENT,
                     PRIMARY KEY (sequence_value)
            ) AUTO_INCREMENT = %d ENGINE = INNODB";

        return sprintf($format, $name, $columnType, $unsigned ? 'UNSIGNED' : '', $startNumber);
    }
}
