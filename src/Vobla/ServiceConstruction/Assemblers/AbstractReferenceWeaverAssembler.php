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

use Vobla\Exception,
    Vobla\Container,
    Vobla\ServiceConstruction\Definition\References\IdReference,
    Vobla\ServiceConstruction\Definition\References\QualifiedReference,
    Vobla\ServiceConstruction\Definition\References\TagReference,
    Vobla\ServiceConstruction\Definition\References\TypeReference,
    Vobla\ServiceLocating\DefaultImpls\TagServiceLocator,
    Vobla\ServiceLocating\DefaultImpls\TypeServiceLocator,
    Vobla\ServiceConstruction\Definition\References\OptionalReference,
    Vobla\ServiceNotFoundException,
    Vobla\ServiceConstruction\Definition\References\TagsCollectionReference,
    Vobla\ServiceConstruction\Definition\References\TypeCollectionReference;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
abstract class AbstractReferenceWeaverAssembler implements Assembler
{
    /**
     * @var \Vobla\Container
     */
    protected $container;

    public function getContainer()
    {
        return $this->container;
    }

    public function init(Container $container)
    {
        $this->container = $container;
    }

    protected function derefenceParameter($param)
    {
        try {
            $shortClassName = explode('\\', get_class($param));
            $shortClassName = end($shortClassName);
            
            $methodName = 'dereference'.ucfirst($shortClassName).'Parameter';
            
            if (in_array($methodName, get_class_methods($this))) {
                return $this->{$methodName}($param);
            } else {
                throw new \RuntimeException();
            }
        } catch (ServiceNotFoundException $e) {
            if ($param instanceof OptionalReference && !$param->isOptional()) {
                throw $e;
            } else {
                throw $e;
            }
        }
    }

    protected function dereferenceIdReferenceParameter(IdReference $param)
    {
        return $this->getContainer()->getServiceById($param->getServiceId());
    }

    protected function dereferenceQualifiedReferenceParameter(QualifiedReference $param)
    {
        return $this->getContainer()->getServiceByQualifier($param->getQualifier());
    }

    protected function dereferenceTagReferenceParameter(TagReference $param)
    {
        $ids = $this->getContainer()->getServiceLocator()->locate(TagServiceLocator::createCriteria($param->getTag()));
        
        if (!$ids) {
            throw new Exception(
                sprintf('Unable to find any services with tag "%s".', $param->getTag())
            );
        }

        if (sizeof($ids) > 1) {
            throw new Exception(
                sprintf(
                    "It was expected that there's only one service with tag '%s' but instead there are %s of them.",
                    $param->getTag(), sizeof($ids)
                )
            );
        }
        
        return $this->getContainer()->getServiceById($ids[0]);
    }

    protected function dereferenceTypeReferenceParameter(TypeReference $param)
    {
        $c = $this->getContainer();

        $ids = $c->getServiceLocator()->locate(TypeServiceLocator::createCriteria($param->getType()));

        if (sizeof($ids) > 1) {
            $msg = 'It was expected that dereferencing by type "%s" would provide one matching ID';
            $msg .= ' but instead there was many - "%s". Eliminate ambiguity or use Set/Map counterparts.';
            $msg = sprintf($msg, $param->getType(), implode($ids));
            throw new Exception($msg);
        }

        return $c->getServiceById($ids[0]);
    }

    protected function dereferenceTagsCollectionReferenceParameter(TagsCollectionReference $param)
    {
        $serviceLocator = $this->getContainer()->getServiceLocator();

        return $this->dereferenceCollection(
            $param->getTags(),
            $param->getStereotype(),
            $param->isOptional(),
            function($tagName) use($serviceLocator){ return TagServiceLocator::createCriteria($tagName); }
        );
    }

    protected function dereferenceCollection(array $lookupTokens, $stereotype, $isOptional, \Closure $criteriaBuilder)
    {
        $ids = array();
        foreach ($lookupTokens as $token) {
            $criteria = $criteriaBuilder($token);
            $ids = array_merge(
                $ids,
                $this->getContainer()->getServiceLocator()->locate($criteria)
            );
        }

        $stereotype = $this->getCollectionStereotype($stereotype);
        $services = array();
        foreach ($ids as $id) {
            $service = null;
            try {
                $service = $this->getContainer()->getServiceById($id);
            } catch (ServiceNotFoundException $e) {
                if (!$isOptional) {
                    throw $e;
                }
            }

            if ($stereotype == 'set') {
                $services[] = $service;
            } else if ($stereotype == 'map') {
                $services[$id] = $service;
            } else {
                // TODO
            }
        }

        return $services;
    }

    protected function dereferenceTypeCollectionReferenceParameter(TypeCollectionReference $param)
    {
        $serviceLocator = $this->getContainer()->getServiceLocator();

        return $this->dereferenceCollection(
            array($param->getType()),
            $param->getStereotype(),
            $param->isOptional(),
            function($typeName) use($serviceLocator){ return TypeServiceLocator::createCriteria($typeName); }
        );
    }

    protected function getCollectionStereotype($providedStereotype)
    {
        return in_array($providedStereotype, array('set', 'map')) ? $providedStereotype : 'set';
    }
}