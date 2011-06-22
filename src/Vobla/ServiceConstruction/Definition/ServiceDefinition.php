<?php

namespace Vobla\ServiceConstruction\Definition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ServiceDefinition
{
    /**
     * @var bool
     */
    private $isAbstract = false;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $factoryMethod;

    /**
     * @var mixed
     */
    private $scope;

    /**
     * @var string
     */
    private $factoryService;

    /**
     * @var array
     */
    private $arguments = array();

    /**
     * @var array
     */
    private $constructorArguments = array();

    /**
     * @var string
     */
    private $initMethod;

    /**
     * @var string
     */
    private $qualifier;
    
    /**
     * @param array $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param array $constructorArguments
     */
    public function setConstructorArguments($constructorArguments)
    {
        $this->constructorArguments = $constructorArguments;
    }

    /**
     * @return array
     */
    public function getConstructorArguments()
    {
        return $this->constructorArguments;
    }

    /**
     * @param string $factoryMethod
     */
    public function setFactoryMethod($factoryMethod)
    {
        $this->factoryMethod = $factoryMethod;
    }

    /**
     * @return string
     */
    public function getFactoryMethod()
    {
        return $this->factoryMethod;
    }

    /**
     * @param string $factoryService
     */
    public function setFactoryService($factoryService)
    {
        $this->factoryService = $factoryService;
    }

    /**
     * @return string
     */
    public function getFactoryService()
    {
        return $this->factoryService;
    }

    /**
     * @param string $initMethod
     */
    public function setInitMethod($initMethod)
    {
        $this->initMethod = $initMethod;
    }

    /**
     * @return string
     */
    public function getInitMethod()
    {
        return $this->initMethod;
    }

    /**
     * @param boolean $isAbstract
     */
    public function setAbstract($isAbstract)
    {
        $this->isAbstract = $isAbstract;
    }

    /**
     * @return boolean
     */
    public function isAbstract()
    {
        return $this->isAbstract;
    }

    /**
     * @param string $qualifier
     */
    public function setQualifier($qualifier)
    {
        $this->qualifier = $qualifier;
    }

    /**
     * @return string
     */
    public function getQualifier()
    {
        return $this->qualifier;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    static public function clazz()
    {
        return get_called_class();
    }
}
