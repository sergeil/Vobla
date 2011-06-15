<?php

namespace Vobla\ServiceConstruction\Assemblers\Injection;

use Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface ReferenceInjector
{
    const CLAZZ = 'Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector';

    public function inject($obj, $paramName, $paramValue, ServiceDefinition $definition);
}
