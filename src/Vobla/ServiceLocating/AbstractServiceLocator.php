<?php

namespace Vobla\ServiceLocating;

use Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
abstract class AbstractServiceLocator implements ServiceLocator
{
    /**
     * @var \Vobla\Container
     */
    protected $container;

    /**
     * @return \Vobla\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function init(Container $container)
    {
        $this->container = $container;
    }
}
