<?php
/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 20/06/2016
 * Time: 10:56
 */

namespace SM\XRetail\Model\Api;


use Magento\Framework\DataObject;

class Configuration extends DataObject {

    /**
     * @var
     */
    protected $_apiRouters;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_mageConfig;
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;
    /**
     * @var \Magento\Framework\Xml\Parser
     */
    protected $parser;

    /**
     * Configuration constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Module\Dir\Reader               $reader
     * @param \Magento\Framework\Xml\Parser                      $parser
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Framework\Xml\Parser $parser,
        array $data = []
    ) {
        $this->reader      = $reader;
        $this->parser      = $parser;
        $this->_mageConfig = $scopeConfig;
        parent::__construct($data);
    }

    /**
     * @return array
     */
    public function getApiRouters() {

        if (is_null($this->_apiRouters)) {
            $this->_apiRouters = $this->getRouterData();
        }

        return $this->_apiRouters;
    }

    private function getRouterData() {
        $filePath    = $this->reader->getModuleDir('etc', 'SM_XRetail') . '/config.xml';
        $parsedArray = $this->parser->load($filePath)->xmlToArray();

        if (isset($parsedArray['config']['_value']['default']['apirouters']['router'])) {
            return $parsedArray['config']['_value']['default']['apirouters']['router'];
        }

        throw new \Exception("Can't get routers data");
    }
}