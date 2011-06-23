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
     * @return void
     */
    public function locate($criteria);

    /**
     *
     */
    public function analyze($id, ServiceDefinition $serviceDefinition);
}
