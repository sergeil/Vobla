<?php

namespace Vobla\ServiceConstruction\Builders;

use Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ServiceIdGenerator
{
    public function generate(\ReflectionClass $reflClass, $declaredId, ServiceDefinition $serviceDef)
    {
        return $declaredId ? $declaredId : $reflClass->getName().'_'.spl_object_hash($serviceDef);
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
