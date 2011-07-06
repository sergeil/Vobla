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
    Vobla\ServiceConstruction\Definition\References\QualifiedReference,
    Vobla\ServiceConstruction\Definition\References\IdReference,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\AutowiredSet,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\AutowiredMap,
    Vobla\ServiceConstruction\Definition\References\TagReference,
    Vobla\ServiceConstruction\Definition\References\TypeReference,
    Vobla\ServiceConstruction\Builders\InjectorsOrderResolver,
    Vobla\ServiceConstruction\Definition\References\TagsCollectionReference,
    Vobla\ServiceConstruction\Definition\References\TypeCollectionReference;

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

    protected function handleProperty(AnnotationReader $annotationReader, \ReflectionClass $reflClass, \ReflectionProperty $reflProp, ServiceDefinition $serviceDefinition)
    {
        /* @var Annotations\Autowired $autowiredAnnotation */
        $awAnn = $annotationReader->getPropertyAnnotation($reflProp, Autowired::clazz());
        $awSetAnn = $annotationReader->getPropertyAnnotation($reflProp, AutowiredSet::clazz());
        $awMapAnn = $annotationReader->getPropertyAnnotation($reflProp, AutowiredMap::clazz());

        if ($awAnn) {
            return $this->handleAutowired($serviceDefinition, $reflProp, $awAnn);
        } else if ($awSetAnn) {
            return $this->handleAutowiredSet($serviceDefinition, $reflProp, $awSetAnn);
        } else if ($awMapAnn) {
            return $this->handleAutowiredMap($serviceDefinition, $reflProp, $awMapAnn);
        }
    }
    
    protected function handleAutowired(ServiceDefinition $serviceDefinition, \ReflectionProperty $reflProp, Autowired $awAnn)
    {
        /* @var \Vobla\ServiceConstruction\Builders\InjectorsOrderResolver $ior */
        $ior = clone $this->getInjectorsOrderResolver();
        $ior->setByIdCallback(function() use($reflProp, $awAnn) {
            $refServiceId = $awAnn->id === null ? $reflProp->getName() : $awAnn->id;
            return new IdReference($refServiceId, $awAnn->isOptional);
        });
        $ior->setByQualifierCallback(function() use($awAnn) {
            if ($awAnn->qualifier) {
                return new QualifiedReference($awAnn->qualifier, $awAnn->isOptional);
            }
        });
        $ior->setByTagCallback(function() use($awAnn) {
            if ($awAnn->tag) {
                return new TagReference($awAnn->tag, $awAnn->isOptional);
            }
        });
        $ior->setByTypeCallback(function() use($awAnn, $serviceDefinition) {
            if ($awAnn->type) {
                /* @var \Vobla\ServiceConstruction\Definition\ServiceDefinition $serviceDefinition */
                $notByTypeWiringCandidate = $serviceDefinition->getMetaEntry('notByTypeWiringCandidate');
                // proceeding only if this class was not marked as a non-candidate
                // for by-type autowiring
                if ($notByTypeWiringCandidate === null) {
                    return new TypeReference($awAnn->type, $awAnn->isOptional);
                }
            }
        });

        return $ior->resolve();
    }

    protected function handleAutowiredSet(ServiceDefinition $serviceDefinition, \ReflectionProperty $reflProp, AutowiredSet $awSetAnn)
    {
        return $this->createInjectorsOrderResolverForCollection($serviceDefinition, $awSetAnn, 'set')->resolve();
    }

    protected function handleAutowiredMap(ServiceDefinition $serviceDefinition, \ReflectionProperty $reflProp, AutowiredMap $awMapAnn)
    {
        return $this->createInjectorsOrderResolverForCollection($serviceDefinition, $awMapAnn, 'map')->resolve();
    }

    /**
     * @param mixed $annotation
     * @param string $type  set or map
     * @return \Vobla\ServiceConstruction\Builders\InjectorsOrderResolver
     */
    private function createInjectorsOrderResolverForCollection(ServiceDefinition $serviceDefinition, $annotation, $type)
    {
        /* @var \Vobla\ServiceConstruction\Builders\InjectorsOrderResolver $ior */
        $ior = clone $this->getInjectorsOrderResolver();
        $ior->setByTagCallback(function() use($annotation, $type) {
            if ($annotation->tags) {
                return new TagsCollectionReference($annotation->tags, $type, $annotation->isOptional);
            }
        });
        $ior->setByTypeCallback(function() use($annotation, $type, $serviceDefinition) {
            if ($annotation->type) {
                /* @var \Vobla\ServiceConstruction\Definition\ServiceDefinition $serviceDefinition */
                $notByTypeWiringCandidate = $serviceDefinition->getMetaEntry('notByTypeWiringCandidate');
                // proceeding only if this class was not marked as a non-candidate
                // for by-type autowiring
                if ($notByTypeWiringCandidate === null) {
                    return new TypeCollectionReference($annotation->type, $type, $annotation->isOptional);
                }
            }
        });

        return $ior;
    }
}
