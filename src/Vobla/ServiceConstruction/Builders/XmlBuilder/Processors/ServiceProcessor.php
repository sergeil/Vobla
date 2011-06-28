<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder\Processors;

use Vobla\Container,
    Vobla\ServiceConstruction\Builders\XmlBuilder\XmlBuilder,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\Exception,
    Vobla\ServiceConstruction\Definition\ServiceReference;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ServiceProcessor implements Processor
{
    public function processXml($xmlBody, Container $container, XmlBuilder $xmlBuilder)
    {
        $ed = $xmlBuilder->getEventDispatcher();

        
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

    public function parseServiceTag(\SimpleXMLElement $xmlElement)
    {
        $xmlAttrs = $xmlElement->attributes();
        $def = new ServiceDefinition();

        $defMethods = get_class_methods($def);
        foreach ($xmlAttrs as $name=>$attribute) {
            $setterName = $this->createMethodNameFromAttributeName($name);
            if (in_array($setterName, $defMethods)) {
                $def->{$setterName}((string)$attribute);
            }
        }

        $serviceId = isset($xmlAttrs['id']) ? (string)$xmlAttrs['id'] : null;
        return array(
            $serviceId,
            $def
        );
    }

    /**
     * @return array
     */
    public function parseServiceParametersTag(\SimpleXMLElement $paramsXml)
    {
        $result = array();
        foreach ($paramsXml as $paramXml) {
            /* @var \SimpleXMLElement $paramXml */
            if ($paramXml->getName() != 'property') {
                throw new Exception();
            }

            $propertyAttrsXml = $paramXml->attributes();
            if (!isset($propertyAttrsXml['name'])) {
                throw new Exception();
            }
            $propertyName = (string)$propertyAttrsXml['name'];
            
        }

        return $result;
    }

    public function parseServiceParametersPropertyTag(\SimpleXMLElement $propertyXml)
    {
        $result = array();

        $propertyAttrsXml = $propertyXml->attributes();
        if (!isset($propertyAttrsXml['name'])) {
            throw new Exception();
        }
        
        $propertyName = (string)$propertyAttrsXml['name'];
        $propertyValue = null;

        if (isset($propertyAttrsXml['ref'])) { // ref has the highest priority
            $propertyValue = new ServiceReference((string)$propertyAttrsXml['ref']);
        } else if (isset($propertyAttrsXml['value'])) { // value has priority over inline body
            if (isset($propertyAttrsXml['type'])) {
                $propertyValue = $this->castServiceParametersPropertyTagValue(
                    (string)$propertyAttrsXml['value'],
                    (string)$propertyAttrsXml['type']
                );
            } else {
                $propertyValue = (string)$propertyAttrsXml['value'];
            }
        } else if ($propertyXml->count() == 0) { // inline
            $propertyValue = (string)$propertyXml;
        } else if ($propertyXml->count() == 1) {
            $propertyChildrenXml = $propertyXml->children();
            $propertyValue = $this->parseServiceParametersPropertyChildTag($propertyChildrenXml[0]);
        } else if ($propertyXml->count() > 1) { // TODO only one child is supported, should we consider multiple injection ?
            throw new Exception();
        }
        $result[$propertyName] = $propertyValue;

        return $result;
    }

    public function parseServiceParametersPropertyChildTag(\SimpleXMLElement $childXml)
    {
        if ($childXml->getName() == 'array') {
            return $this->parseServiceParametersPropertyChildArrayTag($childXml);
        } else if ($childXml->getName() == 'service') {
            return $this->parseServiceParametersPropertyChildServiceTag($childXml);
        }
    }

    public function parseServiceParametersPropertyChildServiceTag(\SimpleXMLElement $serviceXml)
    {
        return $this->parseServiceTag($serviceXml);
    }

    public function parseServiceParametersPropertyChildArrayTag(\SimpleXMLElement $arrayXml)
    {
        $result = array();

        

        return $result;
    }

    public function parseServiceParametersPropertyChildArrayElTag(\SimpleXMLElement $elXml)
    {
        $elAttrsXml = $elXml->attributes();
        $result = array();

        $index = isset($elAttrsXml['index']) ? (string)$elAttrsXml['index'] : 0; // TODO never incremented!
        $value = null;
        if (isset($elAttrsXml['value'])) { // value has priority
            $value = (string)$elAttrsXml['value'];
        } else if ($elXml->count() > 0) {
            $value = array();
            foreach ($elXml->children() as $childElXml) {
                $elName = (string)$childElXml->getName();
                if ($elName == 'ref') {
                    $value = array_merge(
                        $value,
                        array($this->parseServiceParametersPropertyChildArrayElRefTag($childElXml))
                    );
                } else if ($elName == 'service') {
                    $value = array_merge(
                        $value,
                        array($this->parseServiceParametersPropertyChildArrayElServiceTag($childElXml))
                    );
                } else if ($elName == 'el') {
                    $value = array_merge(
                        $value,
                        $this->parseServiceParametersPropertyChildArrayElTag($childElXml)
                    );
                }
            }
        } else { // inline
            $value = (string)$elXml;
        }
        $result[$index] = $value;

        return $result;
    }

    public function parseServiceParametersPropertyChildArrayElRefTag(\SimpleXMLElement $refXml)
    {
        $refAttrsXml = $refXml->attributes();
        if (!isset($refAttrsXml['id'])) {
            throw new Exception();
        }

        return new ServiceReference((string)$refAttrsXml['id']);
    }

    public function parseServiceParametersPropertyChildArrayElServiceTag(\SimpleXMLElement $serviceXml)
    {
        $result = $this->parseServiceTag($serviceXml);
        return $result[1];
    }

    protected function castServiceParametersPropertyTagValue($value, $type)
    {
        if (in_array($type, array('bool', 'boolean'))) {
            return $value == 'true';
        }
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
