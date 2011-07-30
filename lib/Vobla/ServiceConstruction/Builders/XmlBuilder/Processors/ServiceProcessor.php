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

namespace Vobla\ServiceConstruction\Builders\XmlBuilder\Processors;

use Vobla\Container,
    Vobla\ServiceConstruction\Builders\XmlBuilder\XmlBuilder,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\Exception,
    Vobla\ServiceConstruction\Definition\References\IdReference,
    Vobla\ServiceConstruction\Definition\References\QualifiedReference,
    Vobla\ServiceConstruction\Builders\InjectorsOrderResolver,
    Vobla\ServiceConstruction\Definition\References\TagReference,
    Vobla\ServiceConstruction\Definition\References\TagsCollectionReference,
    Vobla\ServiceConstruction\Definition\References\TypeReference,
    Vobla\ServiceConstruction\Definition\References\TypeCollectionReference,
    Vobla\ServiceConstruction\Definition\References\ConfigPropertyReference;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ServiceProcessor implements Processor
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

    public function processXml($xmlBody, XmlBuilder $xmlBuilder)
    {
        $xmlEl = new \SimpleXMLElement($xmlBody, 0, false, XsdNamespaces::CONTEXT);

        $container = $xmlBuilder->getContainer();
        foreach ($xmlEl->children() as $childXml) {
            $elName = $childXml->getName();
            if ($elName == 'service') {
                /* @var \Vobla\ServiceConstruction\Definition\ServiceDefinition $def */
                list($id, $def) = $this->parseServiceTag($childXml);
                if (!$id) {
                    $reflClass = new \ReflectionClass($def->getClassName());
                    $id = $xmlBuilder->getServiceIdGenerator()->generate($reflClass, $id, $def);
                }
                $container->addServiceDefinition($id, $def);
            } else if ($elName == 'config') {
                $cf = $container->getConfigHolder();
                
                $configs = $this->parseConfigTag($childXml);
                foreach ($configs as $name=>$value) {
                    $cf->set($name, $value);
                }
            }
        }
    }

    protected function createMethodNameFromAttributeName($attributeName)
    {
        $ccAttributeName = array();
        foreach (explode('-', $attributeName) as $segment) {
            $ccAttributeName[] = ucfirst($segment);
        }
        $ccAttributeName = implode('', $ccAttributeName);

        $matches = array();
        if (preg_match('/^is(.*)$/i', $ccAttributeName, $matches)) {
            return 'set'.ucfirst($matches[1]);
        }
        return 'set'.ucfirst($ccAttributeName);
    }

    public function parseServiceTag(\SimpleXMLElement $serviceXml)
    {
        $xmlAttrs = $serviceXml->attributes();
        $def = new ServiceDefinition();

        $defMethods = get_class_methods($def);
        foreach ($xmlAttrs as $name=>$attribute) {
            if ('tags' == $name) {
                $rawTags = explode(',', $attribute);
                $tags = array();
                foreach ($rawTags as $rawTag) {
                    $tags[] = trim($rawTag);
                }

                $def->setMetaEntry('tags', $tags);
                continue;
            } else if ('not-by-type-wiring-candidate' == $name) {
                $def->setMetaEntry(
                    'notByTypeWiringCandidate',
                    (string)$attribute == 'true'
                );
                continue;
            }

            $setterName = $this->createMethodNameFromAttributeName($name);
            if (in_array($setterName, $defMethods)) {
                $def->{$setterName}((string)$attribute);
            }
        }

        $constructorXmlEl = $propertiesXmlEl = null;
        foreach ($serviceXml->children() as $childXml) {
            if ('constructor' == $childXml->getName()) {
                $constructorXmlEl = $childXml;
            } else if ('properties' == $childXml->getName()) {
                $propertiesXmlEl = $childXml;
            }
        }

        if (null !== $constructorXmlEl) {
            $def->setConstructorArguments($this->parseServiceConstructorTag($constructorXmlEl));
        }
        if (null !== $propertiesXmlEl) {
            $def->setArguments($this->parseServicePropertiesTag($propertiesXmlEl));
        }

        $serviceId = isset($xmlAttrs['id']) ? (string)$xmlAttrs['id'] : null;
        return array(
            $serviceId,
            $def
        );
    }

    public function parseRef(\SimpleXMLElement $refXml)
    {
        if ($refXml->getName() != 'ref') {
            throw new \InvalidArgumentException('Reference tag-name must be "ref"');
        }
        $refAttrsXml = $refXml->attributes();

        if (isset($refAttrsXml['id'])) {
            return new IdReference((string)$refAttrsXml['id']);
        } else if (isset($refAttrsXml['qualifier'])) {
            return new QualifiedReference((string)$refAttrsXml['qualifier']);
        }

        $sp = $this;
        $ior = clone $this->getInjectorsOrderResolver();
        $ior->setByIdCallback(function() use($refAttrsXml) {
            if (isset($refAttrsXml['id'])) {
                return new IdReference((string)$refAttrsXml['id']);
            }
        });
        $ior->setByQualifierCallback(function() use($refAttrsXml) {
            if (isset($refAttrsXml['qualifier'])) {
                return new QualifiedReference((string)$refAttrsXml['qualifier']);
            }
        });
        $ior->setByTagCallback(function() use($refAttrsXml, $sp) {
            $isOptional = true;
            if (isset($refAttrsXml['is-optional']) && (string)$refAttrsXml['is-optional'] == 'false') {
                $isOptional = false;
            }

            if (isset($refAttrsXml['tag'])) {
                return new TagReference((string)$refAttrsXml['tag'], $isOptional);
            } else if (isset($refAttrsXml['tags-set'])) {
                return new TagsCollectionReference(
                    $sp->convertStringToTags((string)$refAttrsXml['tags-set']),
                    'set',
                    $isOptional
                );
            } else if (isset($refAttrsXml['tags-map'])) {
                return new TagsCollectionReference(
                    $sp->convertStringToTags((string)$refAttrsXml['tags-map']),
                    'map',
                    $isOptional
                );
            }
        });
        $ior->setByTypeCallback(function() use($refAttrsXml) {
            $isOptional = true;
            if (isset($refAttrsXml['is-optional']) && (string)$refAttrsXml['is-optional'] == 'false') {
                $isOptional = false;
            }

            if (isset($refAttrsXml['type'])) {
                return new TypeReference((string)$refAttrsXml['type'], $isOptional);
            } else if (isset($refAttrsXml['type-set'])) {
                return new TypeCollectionReference(
                    (string)$refAttrsXml['type-set'],
                    'set',
                    $isOptional
                );
            } else if (isset($refAttrsXml['type-map'])) {
                return new TypeCollectionReference(
                    (string)$refAttrsXml['type-map'],
                    'map',
                    $isOptional
                );
            }
        });

        $result = $ior->resolve();
        if (!$result) {
            $params = array('id', 'qualifier', 'tags-set', 'tags-map', 'tags', 'type', 'type-set', 'type-map');
            throw new Exception('One of these attributes is required to be provided: '.implode(', ', $params).'.');
        }

        return $result;
    }

    public function convertStringToTags($input)
    {
        $expInput = explode(',', $input);
        $tags = array();
        foreach ($expInput as $segment) {
            $tags[] = trim($segment);
        }
        return $tags;
    }

    public function parseArray(\SimpleXMLElement $arrayXml, array $enabledSubElements = array('ref', 'service', 'el'))
    {
        if ($arrayXml->getName() != 'array') {
            throw Exception('Root element name must be "array".');
        }

        $result = array();
        foreach ($arrayXml->children() as $elXml) {
            if ($elXml->getName() != 'el') {
                continue; // TODO add an extension point here
            }

            $result = array_merge($result, $this->parseArrayEl($elXml, $enabledSubElements));
        }
        return $result;
    }

    public function parseArrayEl(\SimpleXMLElement $elXml, array $enabledSubElements = array('ref', 'service', 'el'))
    {
        $elAttrsXml = $elXml->attributes();
        $result = array();

        $index = isset($elAttrsXml['index']) ? (string)$elAttrsXml['index'] : 0;
        $value = null;
        if (isset($elAttrsXml['value'])) { // value has priority
            $value = (string)$elAttrsXml['value'];
        } else if ($elXml->count() > 0) {
            $value = array();
            foreach ($elXml->children() as $childElXml) {
                $elName = (string)$childElXml->getName();
                if (!in_array($elName, $enabledSubElements)) {
                    continue;
                }

                /*
                 * array_merge's here act as index implicit index incrementers
                 */
                if ($elName == 'ref') {
                    $value = array_merge(
                        $value,
                        array($this->parseArrayElRef($childElXml))
                    );
                } else if ($elName == 'service') {
                    $value = array_merge(
                        $value,
                        array($this->parseArrayElService($childElXml))
                    );
                } else if ($elName == 'el') {
                    $value = array_merge(
                        $value,
                        $this->parseArrayEl($childElXml, $enabledSubElements)
                    );
                }
            }
        } else { // inline
            $value = (string)$elXml;
        }
        $result[$index] = $value;

        return $result;
    }

    public function parseArrayElRef(\SimpleXMLElement $refXml)
    {
        return $this->parseRef($refXml);
    }

    public function parseArrayElService(\SimpleXMLElement $serviceXml)
    {
        $result = $this->parseServiceTag($serviceXml);
        return $result[1];
    }

    public function parseServiceConstructorTag(\SimpleXMLElement $constructorXml)
    {
        $result = array();
        foreach ($constructorXml->children() as $argXml) {
            $result[] = $this->parseServiceConstructorArgTag($argXml);
        }
        return $result;
    }

    public function parseServiceConstructorArgTag(\SimpleXMLElement $argXml)
    {
        $supportedTags = array('array', 'service', 'ref', 'value');
        $childrenTag = $argXml->children();

        if(sizeof($childrenTag) == 1) {
            $childTag = $childrenTag[0];

            $methodName = sprintf('parseServiceConstructorArg%sTag', (string)$childTag->getName());
            return $this->{$methodName}($childTag);
        } else {
            throw new Exception('arg can contain only 1 sub-element');
        }
    }

    public function parseServiceConstructorArgArrayTag(\SimpleXMLElement $arrayXml)
    {
        return $this->parseArray($arrayXml);
    }

    public function parseServiceConstructorArgServiceTag(\SimpleXMLElement $serviceXml)
    {
        list($id, $def) = $this->parseServiceTag($serviceXml);
        return $def;
    }

    public function parseServiceConstructorArgRefTag(\SimpleXMLElement $refXml)
    {
        return $this->parseRef($refXml);
    }

    public function parseServiceConstructorArgValueTag(\SimpleXMLElement $valueXml)
    {
        $valueAttrsXml = $valueXml->attributes();
        if (isset($valueAttrsXml['type'])) {
            return $this->castServicePropertiesPropertyTagValue((string)$valueXml, (string)$valueAttrsXml['type']);
        } else {
            return (string)$valueXml;
        }
    }

    // ---

    /**
     * @return array
     */
    public function parseServicePropertiesTag(\SimpleXMLElement $propertiesXml)
    {
        $result = array();
        foreach ($propertiesXml as $propertyXml) {
            /* @var \SimpleXMLElement $propertyXml */
            if ($propertyXml->getName() != 'property') {
                continue; // TODO add an extension point here
            }

            $propertyAttrsXml = $propertyXml->attributes();
            if (!isset($propertyAttrsXml['name'])) {
                throw new Exception('<property> tag must always contain "name" attribute.');
            }
            
            $result[(string)$propertyAttrsXml['name']] = $this->parseServicePropertiesPropertyTag($propertyXml);
        }

        return $result;
    }

    public function parseServicePropertiesPropertyTag(\SimpleXMLElement $propertyXml)
    {
        $propertyAttrsXml = $propertyXml->attributes();

        $propertyValue = null;
        if (isset($propertyAttrsXml['ref'])) { // ref has the highest priority
            $propertyValue = new IdReference((string)$propertyAttrsXml['ref']);
        } else if (isset($propertyAttrsXml['value'])) { // value has priority over inline body
            if (isset($propertyAttrsXml['type'])) {
                $propertyValue = $this->castServicePropertiesPropertyTagValue(
                    (string)$propertyAttrsXml['value'],
                    (string)$propertyAttrsXml['type']
                );
            } else {
                $propertyValue = (string)$propertyAttrsXml['value'];
            }
        } else if ($propertyXml->count() == 0) { // inline
            if (isset($propertyAttrsXml['type'])) {
                $propertyValue = $this->castServicePropertiesPropertyTagValue(
                    (string)$propertyXml,
                    (string)$propertyAttrsXml['type']
                );
            } else {
                $propertyValue = (string)$propertyXml;
            }
        } else if ($propertyXml->count() == 1) {
            $propertyChildrenXml = $propertyXml->children();
            $propertyValue = $this->parseServicePropertiesPropertyChildTag($propertyChildrenXml[0]);
        } else if ($propertyXml->count() > 1) { // TODO only one child is supported, should we consider multiple injection ?
            throw new Exception('<property> tag must contain only one root element!');
        }

        return $propertyValue;
    }

    public function parseServicePropertiesPropertyChildTag(\SimpleXMLElement $childXml)
    {
        $elName = $childXml->getName();
        if ($elName == 'array') {
            return $this->parseServicePropertiesPropertyArrayTag($childXml);
        } else if ($elName == 'service') {
            return $this->parseServicePropertiesPropertyChildServiceTag($childXml);
        } else if ($elName == 'ref') {
            return $this->parseServicePropertiesPropertyChildRefTag($childXml);
        } else if ($elName == 'conf-ref') {
            return $this->parseServicePropertiesPropertyChildConfRefTag($childXml);
        } else {
            // TODO add extension point
        }
    }

    public function parseServicePropertiesPropertyArrayTag(\SimpleXMLElement $arrayXml)
    {
        return $this->parseArray($arrayXml);
    }

    public function parseServicePropertiesPropertyChildServiceTag(\SimpleXMLElement $serviceXml)
    {
        list($id, $def) = $this->parseServiceTag($serviceXml);
        return $def;
    }

    public function parseServicePropertiesPropertyChildRefTag(\SimpleXMLElement $refXml)
    {
        return $this->parseRef($refXml);
    }

    public function parseServicePropertiesPropertyChildConfRefTag(\SimpleXMLElement $confRefXml)
    {
        $isOptional = true;
        if (isset($confRefXml['is-optional']) && (string)$confRefXml['is-optional'] == 'false') {
            $isOptional = false;
        }
        
        return new ConfigPropertyReference((string)$confRefXml['name'], $isOptional);
    }

    public function parseConfigTag(\SimpleXMLElement $configXml)
    {
        $values = array();
        foreach ($configXml->children() as $propertyXml) {
            $propertyXmlAttrs = $propertyXml->attributes();
            if (!isset($propertyXmlAttrs['name'])) {
                throw new Exception('config/property[name] is required.');
            }

            $values[(string)$propertyXmlAttrs['name']] = $this->parseConfigPropertyTag($propertyXml);
        }

        return $values;
    }

    public function parseConfigPropertyTag(\SimpleXMLElement $propertyXml)
    {
        $propertyXmlAttrs = $propertyXml->attributes();

        $value = null;
        if (isset($propertyXmlAttrs['value'])) {
            return (string)$propertyXmlAttrs['value'];
        } else if ($propertyXml->count() == 1) { // array
            $propertyChildrenXml = $propertyXml->children();
            if ($propertyChildrenXml[0]->getName() != 'array') {
                throw new Exception("config/property body may contain only one inner tag and it has to be <array>.");
            }

            return $this->parseArray($propertyChildrenXml[0], array());
        } else {
            return (string)$propertyXml;
        }
    }

    protected function castServicePropertiesPropertyTagValue($value, $type)
    {
        if (in_array($type, array('bool', 'boolean'))) {
            return $value == 'true';
        }

        throw new Exception("Unable to cast a value to type '$type'");
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
