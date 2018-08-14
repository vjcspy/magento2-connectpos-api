<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 8/8/18
 * Time: 10:57
 */

namespace SM\Customization\Model;

use Magento\Framework\App\ResourceConnection as AppResource;

/**
 * Class Sequence
 *
 * @package SM\Customization\Model
 */
class Sequence extends \Magento\SalesSequence\Model\Sequence {

    /**
     * Default pattern for Sequence
     */
    const DEFAULT_PATTERN     = "%s%'.09d%s";

    /**
     *
     */
    const POS_SEQUENCE_PREFIx = "POS_";
    /**
     * @var string
     */
    protected $lastIncrementId;

    /**
     * @var \Magento\SalesSequence\Model\Meta
     */
    protected $meta;

    /**
     * @var false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var
     */
    protected $_sequenceTable;

    /**
     * Retrieve current value
     *
     * @return string
     */

    /**
     * @param Meta        $meta
     * @param AppResource $resource
     * @param string      $pattern
     */
    public function __construct(
        \Magento\SalesSequence\Model\Meta $meta,
        AppResource $resource,
        $pattern = self::DEFAULT_PATTERN
    ) {
        $this->meta       = $meta;
        $this->connection = $resource->getConnection('sales');
        $this->pattern    = $pattern;
    }

    /**
     * @return null|string
     */
    public function getCurrentValue() {
        if (!isset($this->lastIncrementId)) {
            return null;
        }

        return sprintf(
            $this->pattern,
            self::POS_SEQUENCE_PREFIx . $this->meta->getActiveProfile()->getPrefix(),
            $this->calculateCurrentValue(),
            $this->meta->getActiveProfile()->getSuffix()
        );
    }

    /**
     * Retrieve next value
     *
     * @return string
     */
    public function getNextValue() {
        $this->connection->insert($this->_sequenceTable, []);
        $this->lastIncrementId = $this->connection->lastInsertId($this->_sequenceTable);

        return $this->getCurrentValue();
    }

    /**
     * Calculate current value depends on start value
     *
     * @return string
     */
    private function calculateCurrentValue() {
        return ($this->lastIncrementId - $this->meta->getActiveProfile()->getStartValue())
               * $this->meta->getActiveProfile()->getStep() + $this->meta->getActiveProfile()->getStartValue();
    }

    /**
     * @param $table
     *
     * @return $this
     */
    public function setSequenceTable($table) {
        $this->_sequenceTable = $table;

        return $this;
    }
}
