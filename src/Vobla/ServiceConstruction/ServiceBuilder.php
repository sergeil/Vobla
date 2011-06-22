<?php

namespace Vobla\ServiceConstruction;

use Vobla\ServiceConstruction\Assemblers\AssemblersManager,
    Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ServiceBuilder
{
    /**
     * @var \Vobla\Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $cachedAssemblers;

    /**
     * @var \Vobla\ServiceConstruction\Assemblers\AssemblersManager
     */
    protected $assemblersManager;

    public function getContainer()
    {
        return $this->container;
    }
    
    public function init(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    protected function getAssemblers()
    {
        return $this->getContainer()->getConfiguration()->getAssemblersProvider()->getAssemblers();
    }

    /**
     * @return \Vobla\ServiceConstruction\Assemblers\AssemblersManager
     */
    public function getAssemblersManager()
    {
        if (null === $this->assemblersManager) {
            $this->assemblersManager = new AssemblersManager($this->getAssemblers());
        }

        return $this->assemblersManager;
    }

    public function process(ServiceDefinition $serviceDefinition)
    {
        return $this->getAssemblersManager()->proceed($serviceDefinition);
    }
}
