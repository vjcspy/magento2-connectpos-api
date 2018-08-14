<?php

namespace SM\Core\Api\Data\Contract;

use Magento\Framework\App\ObjectManager;
use SM\Core\Model\DataObject;

abstract class ApiDataAbstract extends DataObject {

    /**
     * get Method
     */
    const GET_METHOD = 'get';
    /**
     * @var []
     */
    protected $_dataOutput;

    /**
     * @var []
     */
    protected $_allGetApiMethod;

    protected $_serializer;

    /**
     * Data as array
     *
     * @return array
     */
    public function getOutput() {
        if (is_null($this->_dataOutput)) {
            $methods = $this->getAllGetApiMethod();
            foreach ($methods as $method) {
                if (substr($method, 0, 3) === self::GET_METHOD) {
                    $key                     = $this->_underscore(substr($method, 3));
                    $this->_dataOutput[$key] = call_user_func_array([$this, $method], []);
                    if ($this->_dataOutput[$key] instanceof ApiDataAbstract)
                        $this->_dataOutput[$key] = $this->_dataOutput[$key]->getOutput();
                }
            }
        }

        return $this->_dataOutput;
    }

    /**
     * @return array get method
     */
    public function getAllGetApiMethod() {
        if (is_null($this->_allGetApiMethod)) {
            $class   = new \ReflectionClass(get_class($this));
            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if ($method->getDeclaringClass()->getName() == get_class($this))
                    $this->_allGetApiMethod[] = $method->getName();

            }
        }

        return $this->_allGetApiMethod;
    }

    protected function unserialize($value) {
        if (class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            return $this->getSerialize()->unserialize($value);
        }
        else {
            return unserialize($value);
        }
    }

    protected function getSerialize() {
        if (is_null($this->_serializer)) {
            $this->_serializer = ObjectManager::getInstance()->create('\Magento\Framework\Serialize\Serializer\Json');
        }

        return $this->_serializer;
    }
}