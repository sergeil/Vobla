<?php

namespace Vobla\Context;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\Container;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface ContextScopeHandler
{
    const CLAZZ = 'Vobla\Context\ContextScopeHandler';

    public function isRegisterResponsible($id, ServiceDefinition $serviceDefinition, $obj);

    public function isDispenseResponsible($id);

    public function contains($id);

    public function register($id, $obj);

    public function dispense($id);

    public function init(Container $container);
}
