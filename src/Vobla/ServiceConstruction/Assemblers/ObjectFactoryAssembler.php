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

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Definition\QualifiedReference,
    Vobla\Exception;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ObjectFactoryAssembler extends AbstractReferenceWeaverAssembler
{
    /**
     * @throws \Vobla\Exception
     * @param \ReflectionClass $reflClass
     * @param  string $methodName
     * @return \ReflectionMethod
     */
    protected function getReflectedFactoryMethod(\ReflectionClass $reflClass, $methodName)
    {
        if (!$reflClass->hasMethod($methodName)) {
            throw new Exception(
                sprintf(
                    "Class '%s' doesn't have a declared factory-method '%s'.",
                    get_class($reflClass->getName()), $methodName
                )
            );
        }

        return $reflClass->getMethod($methodName);
    }

    protected function createInstanceWithFactory(ServiceDefinition $definition, array $params)
    {
        $factoryMethod = $definition->getFactoryMethod();
        if ($definition->getFactoryService() !== null) { // other service's method will act as a factory
            $factoryObj = $this->getContainer()->getServiceById($definition->getFactoryService());

            $reflFactoryMethod = $this->getReflectedFactoryMethod(
                new \ReflectionClass($factoryObj),
                $factoryMethod
            );
            return $reflFactoryMethod->invokeArgs($factoryObj, $params);

        } else { // static factory-method
            $reflFactoryMethod = $this->getReflectedFactoryMethod(
                new \ReflectionClass($this->createTargetServiceClassName($definition)),
                $factoryMethod
            );
            return $reflFactoryMethod->invokeArgs(null, $params);
        }
    }

    protected function createInstance(ServiceDefinition $definition, array $params)
    {
        if ($definition->getFactoryMethod() == '') {
            $className = $this->createTargetServiceClassName($definition);
            return new $className;
        } else if ($definition->getFactoryMethod() == '__construct') { // standard stuff
            $reflClass = new \ReflectionClass($this->createTargetServiceClassName($definition));
            return $reflClass->newInstanceArgs($params);
        } else { // factory-method
            return $this->createInstanceWithFactory($definition, $params);
        }
    }

    protected function createTargetServiceClassName(ServiceDefinition $definition)
    {
        return $definition->getClassName();
    }

    protected function dereferenceConstructorParameters(ServiceDefinition $definition)
    {
        // TODO validate if number of constructor parameters provided is right

        $constructorParams = array();
        foreach ($definition->getConstructorArguments() as $arg) {
            $constructorParams[] = $this->derefenceParameter($arg);
        }

        return $constructorParams;
    }

    public function execute(AssemblersManager $assemblersManager, ServiceDefinition $definition, $obj = null)
    {
        $obj = $this->createInstance(
            $definition,
            $this->dereferenceConstructorParameters($definition)
        );
        
        return $assemblersManager->proceed($definition, $obj);
    }
}
