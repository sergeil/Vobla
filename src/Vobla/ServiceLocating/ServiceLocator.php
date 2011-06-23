<?php

namespace Vobla\ServiceLocating;

use Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface ServiceLocator
{
    const CLAZZ = 'Vobla\ServiceLocating\ServiceLocator';

    public function init(Container $container);

    /**
     * @param mixed $criteria
     * @return string|false  Id of a service of FALSE
     */
    public function locate($criteria);

    /**
     * Here you are able to analyze service definition and store it for further lookup.
     */
    public function analyze($id, ServiceDefinition $serviceDefinition);
}
