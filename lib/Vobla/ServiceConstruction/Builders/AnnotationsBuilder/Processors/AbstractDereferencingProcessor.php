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

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\AutowiredSet,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\AutowiredMap,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Definition\References\TagsCollectionReference,
    Vobla\ServiceConstruction\Definition\References\TypeCollectionReference,
    Vobla\Exception,
    Vobla\ServiceConstruction\Definition\References\IdReference,
    Vobla\ServiceConstruction\Definition\References\QualifiedReference,
    Vobla\ServiceConstruction\Definition\References\TagReference,
    Vobla\ServiceConstruction\Definition\References\TypeReference;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
abstract class AbstractDereferencingProcessor extends AbstractProcessor
{
    /**
     * @throws \Vobla\Exception
     * @param \Vobla\ServiceConstruction\Definition\ServiceDefinition $serviceDefinition
     * @param \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired $awAnn
     * @param string $defaultServiceId  If annotation is placed on a property we may omit defining
     *                                  referenced-service id, property-name will be treated as id.
     * @return mixed
     */
    protected function dereferenceAutowiredAnnotation(ServiceDefinition $serviceDefinition, Autowired $awAnn, $defaultServiceId = null)
    {
        /* @var \Vobla\ServiceConstruction\Builders\InjectorsOrderResolver $ior */
        $ior = clone $this->getInjectorsOrderResolver();
        $ior->setByIdCallback(function() use($defaultServiceId, $awAnn) {
            if (null === $defaultServiceId && null === $awAnn->id) {
                $msg = sprintf(
                    'No default service-id provided nor ID explicitely defined in %s annotation.',
                    Autowired::clazz()
                );
                throw new Exception($msg);
            }

            $refServiceId = $awAnn->id === null ? $defaultServiceId : $awAnn->id;
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

    protected function dereferenceAutowiredSetAnnotation(ServiceDefinition $serviceDefinition, AutowiredSet $awSetAnn)
    {
        return $this->createInjectorsOrderResolverForCollection($serviceDefinition, $awSetAnn, 'set')->resolve();
    }

    protected function dereferenceAutowiredMapAnnotation(ServiceDefinition $serviceDefinition, AutowiredMap $awMapAnn)
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
