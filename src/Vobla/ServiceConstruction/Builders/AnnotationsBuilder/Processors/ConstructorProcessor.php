<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor,
    Vobla\ServiceConstruction\Definition\QualifiedReference,
    Vobla\Exception;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ConstructorProcessor implements Processor
{
    public function handle(AnnotationReader $annotationReader, \ReflectionClass $reflClass, ServiceDefinition $serviceDefinition)
    {
        $isConstructorFound = false;
        foreach ($reflClass->getMethods() as $reflMethod) {
            /* @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor $constructorAnnotation */
            $constructorAnnotation = $annotationReader->getMethodAnnotation($reflMethod, Constructor::clazz());
            if (!$constructorAnnotation) {
                continue;
            } else if ($isConstructorFound) {
                // TODO throw a proper exception
                throw new Exception(sprintf('Multiple constructors defined in class %s', $reflClass->getName()));
            }

            $isConstructorFound = true;
            $serviceDefinition->setFactoryMethod($reflMethod->getName());
            $serviceDefinition->setConstructorArguments(
                $this->dereferenceConstructorParams($reflMethod, $constructorAnnotation->params)
            );
        }
    }

    protected function dereferenceConstructorParams(\ReflectionMethod $reflMethod, array $constructorParams)
    {
        try {
            return $this->doDereferenceConstructorParams($reflMethod, $constructorParams);
        } catch (Exception $e) {
            throw new Exception(
                sprintf(
                    'Failed to process annotations for constructor method %s::%s".',
                    $reflMethod->getDeclaringClass()->getName(),
                    $reflMethod->getName()
                )
            );
        }
    }

    /**
     * Override this method if you want to introduce some more annotations
     *
     * @param array $constructorParams
     * @return array
     */
    protected function doDereferenceConstructorParams(\ReflectionMethod $reflMethod, array $constructorParams)
    {
        $dereferencedParams = array();
        foreach ($reflMethod->getParameters() as $reflParam) {
            $dereferencedParams[$reflParam->getName()] = null;
        }

        /* @var Annotations\Parameter $param */
        foreach ($constructorParams as $param) {
            if ($param->name == null) {
                throw new Exception(
                    "Parameter 'name' is required."
                );
            }
            $dereferencedParams[$param->name] = $this->dereferenceConstructorParam($param);
        }

        foreach ($dereferencedParams as $paramName=>$value) {
            if ($value === null) {
                $dereferencedParams[$paramName] = new ServiceReference($paramName);
            }
        }

        return array_values($dereferencedParams);
    }

    protected function dereferenceConstructorParam($param) // TODO make this method as final
    {
        if ($param->qualifier != null) {
            return new QualifiedReference($param->qualifier);
        } else if ($param->id != null) {
            return new ServiceReference($param->id);
        } else {
            return $this->dereferenceConstructorParam($param);
        }
    }
}
