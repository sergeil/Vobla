<?php

namespace Vobla\ServiceConstruction\Assemblers\Injection;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\Exception;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class FieldAccessReferenceInjector implements ReferenceInjector
{
    public function inject($obj, $paramName, $paramValue, ServiceDefinition $definition)
    {
        $reflClass = new \ReflectionClass($obj);

        if (!$reflClass->hasProperty($paramName)) {
            throw new Exception(
                sprintf(
                    'Unable to inject parameter "%s" because the field with the same name is missing in class "%s"',
                    $paramName, get_class($obj)
                )
            );
        }

        $reflProperty = $reflClass->getProperty($paramName);
        $reflProperty->setAccessible(true);

        $reflProperty->setValue($obj, $paramValue);
    }
}
