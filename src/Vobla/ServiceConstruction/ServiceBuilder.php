<?php

namespace Vobla\ServiceConstruction;

use Vobla\ServiceConstruction\Assemblers\AssemblersManager,
    Vobla\Container;

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

    public function process(ServiceDefition $serviceDefinition)
    {
        $this->getAssemblersManager()->proceed($serviceDefinition);
    }
}
