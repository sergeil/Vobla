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
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\Exception;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
abstract class AbstractPropertiesProcessor extends AbstractDereferencingProcessor //extends AbstractProcessor
{
    /**
     * {@inheritdoc}
     */
    public function handle(AnnotationReader $annotationReader, \ReflectionClass $reflClass, ServiceDefinition $serviceDefinition)
    {
        $currentServiceArgs = $serviceDefinition->getArguments();
        $result = $serviceClasses = array();
        foreach ($reflClass->getProperties() as $reflProp) {
            $reflDeclaredClass = $reflProp->getDeclaringClass();
            if (!in_array($reflDeclaredClass->getName(), $serviceClasses)) {
                $serviceAnnotation = $annotationReader->getClassAnnotation($reflDeclaredClass, Service::clazz());
                if ($serviceAnnotation) {
                    $serviceClasses[] = $reflDeclaredClass->getName();
                }
            }

            // if a declared class doesn't have Service annotation skipping its properties
            if (!in_array($reflDeclaredClass->getName(), $serviceClasses)) {
                continue;
            }

            $refDef = null;
            try {
                $refDef = $this->handleProperty($annotationReader, $reflClass, $reflProp, $serviceDefinition);
            } catch (\Exception $e) {
                $msg = sprintf(
                    'Unable to handle some annotation of property %s::%s',
                    $reflClass->getName(), $reflProp->getName()
                );
                throw new Exception($msg);
            }

            if ($refDef !== null && !isset($currentServiceArgs[$reflProp->getName()])) {
                $result[$reflProp->getName()] = $refDef;
            }
        }

        $serviceDefinition->setArguments(array_merge($currentServiceArgs, $result));
    }

    abstract protected function handleProperty(AnnotationReader $annotationReader, \ReflectionClass $reflClass, \ReflectionProperty $reflProp, ServiceDefinition $serviceDefinition);
}
