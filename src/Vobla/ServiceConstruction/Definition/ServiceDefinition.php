<?php
/*
 * Copyright (c) 2011 Sergei Lissovski, http://sergei.lissovski.org
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:

 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Vobla\ServiceConstruction\Definition;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
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
        $this->isAbstract = is_bool($isAbstract) ? $isAbstract : $isAbstract == 'true';
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
