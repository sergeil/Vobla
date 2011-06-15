<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Definition\QualifiedReference,
    Vobla\Exception;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
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
        if (in_array($definition->getFactoryMethod(), array('__construct', ''))) { // standard stuff
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
        // TODO validate if number of constructor parametrs provided is right

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
        
        $assemblersManager->proceed($definition, $obj);

        return $obj;
    }
}
