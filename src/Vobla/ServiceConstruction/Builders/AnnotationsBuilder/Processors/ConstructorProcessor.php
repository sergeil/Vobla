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

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\ServiceConstruction\Definition\References\IdReference,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor,
    Vobla\ServiceConstruction\Definition\References\QualifiedReference,
    Vobla\Exception;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
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
        } catch (\Exception $e) {
            throw new Exception(
                sprintf(
                    'Failed to process annotations for constructor method %s::%s".',
                    $reflMethod->getDeclaringClass()->getName(),
                    $reflMethod->getName()
                ),
                null,
                $e
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
                $dereferencedParams[$paramName] = new IdReference($paramName);
            }
        }

        return array_values($dereferencedParams);
    }

    protected function dereferenceConstructorParam($param) // TODO make this method as final
    {
        if ($param->qualifier != null) {
            return new QualifiedReference($param->qualifier);
        } else if ($param->id != null) {
            return new IdReference($param->id);
        } else {
            return $this->dereferenceConstructorParam($param);
        }
    }
}
