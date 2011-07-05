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
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Tag,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class TagsProcessor implements Processor
{
    public function handle(AnnotationReader $annotationReader, \ReflectionClass $reflClass, ServiceDefinition $serviceDefinition)
    {
        $this->scanTags($annotationReader, $reflClass, $serviceDefinition);
    }

    protected function scanTags(AnnotationReader $annotationReader, \ReflectionClass $reflClass, ServiceDefinition $serviceDefinition)
    {
        $serviceAnn = $annotationReader->getClassAnnotation($reflClass, Service::clazz());
        if (!$serviceAnn) {
            return;
        }

        $tags = array();
        foreach ($annotationReader->getClassAnnotations($reflClass, Tag::clazz()) as $annotation) {
            /* @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Tag $annotation */
            if ($annotation instanceof Tag) {
                $tags[] = $annotation->value;
            }
        }

        $existingTags = $serviceDefinition->getMetaEntry('tags');
        if (!$existingTags) {
            $existingTags = array();
        }
        $serviceDefinition->setMetaEntry('tags', array_merge($tags, $existingTags));

        $reflParent = $reflClass->getParentClass();
        if ($reflParent) {
            $this->scanTags($annotationReader, $reflParent, $serviceDefinition);
        }
    }


}
