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
    Vobla\ServiceConstruction\Definition\References\QualifiedReference,
    Vobla\ServiceConstruction\Definition\References\IdReference,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\AutowiredSet,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\AutowiredMap,
    Vobla\ServiceConstruction\Definition\References\TagReference,
    Vobla\ServiceConstruction\Definition\References\TypeReference,
    Vobla\ServiceConstruction\Builders\InjectorsOrderResolver,
    Vobla\ServiceConstruction\Definition\References\TagsCollectionReference,
    Vobla\ServiceConstruction\Definition\References\TypeCollectionReference,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\ConfigProperty,
    Vobla\Exception,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class PropertiesProcessor extends AbstractPropertiesProcessor
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\InjectorsOrderResolver
     */
    protected $injectorsOrderResolver;

    /**
     * @param \Vobla\ServiceConstruction\Builders\InjectorsOrderResolver $injectorsOrderResolver
     */
    public function setInjectorsOrderResolver(InjectorsOrderResolver $injectorsOrderResolver)
    {
        $this->injectorsOrderResolver = $injectorsOrderResolver;
    }

    /**
     * @return \Vobla\ServiceConstruction\Builders\InjectorsOrderResolver
     */
    public function getInjectorsOrderResolver()
    {
        if (null === $this->injectorsOrderResolver) {
            $this->injectorsOrderResolver = new InjectorsOrderResolver();
        }

        return $this->injectorsOrderResolver;
    }

    /**
     * @throws \Vobla\Exception
     * @param \Doctrine\Common\Annotations\AnnotationReader $annotationReader
     * @param \ReflectionClass $reflClass
     * @param \ReflectionProperty $reflProp
     * @param \Vobla\ServiceConstruction\Definition\ServiceDefinition $serviceDefinition
     * @return mixed
     */
    protected function handleProperty(\ReflectionClass $reflClass, \ReflectionProperty $reflProp, ServiceDefinition $serviceDefinition, AnnotationsBuilder $annotationsBuilder)
    {
        $ar = $annotationsBuilder->getAnnotationReader();

        /* @var Annotations\Autowired $autowiredAnnotation */
        $awAnn = $ar->getPropertyAnnotation($reflProp, Autowired::clazz());
        $awSetAnn = $ar->getPropertyAnnotation($reflProp, AutowiredSet::clazz());
        $awMapAnn = $ar->getPropertyAnnotation($reflProp, AutowiredMap::clazz());
        $cpAnn = $ar->getPropertyAnnotation($reflProp, ConfigProperty::clazz());

        if ($cpAnn && ($awAnn || $awSetAnn || $awMapAnn)) {
            $msg = sprintf(
                '%s annotation should not be mixed with some of AutowiredXXX annotation on the same property!',
                ConfigProperty::clazz()
            );
            throw new Exception($msg);
        }

        if ($awAnn) {
            return $this->dereferenceAutowiredAnnotation($serviceDefinition, $awAnn, $reflProp->getName());
        } else if ($awSetAnn) {
            return $this->dereferenceAutowiredSetAnnotation($serviceDefinition, $awSetAnn);
        } else if ($awMapAnn) {
            return $this->dereferenceAutowiredMapAnnotation($serviceDefinition, $awMapAnn);
        } else if ($cpAnn) {
            return $this->dereferenceConfigPropertyAnnotation($serviceDefinition, $cpAnn);
        }
    }
}
