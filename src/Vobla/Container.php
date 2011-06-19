<?php

namespace Vobla;

use Vobla\ServiceConstruction\ServiceBuilder;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class Container 
{
    /**
     * @var \Vobla\ServiceConstruction\ServiceBuilder
     */
    protected $serviceBuilder;

    protected $context;
    
    protected $definitionsHolder;

    /**
     * @var \Vobla\Configuration
     */
    protected $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->setConfiguration($configuration);
    }

    /**
     * @return \Vobla\ServiceConstruction\ServiceBuilder
     */
    public function getServiceBuilder()
    {
        if (null === $this->serviceBuilder) {
            $this->serviceBuilder = new ServiceBuilder();
            $this->serviceBuilder->init($this);
        }

        return $this->serviceBuilder;
    }

    /**
     * @param \Vobla\ServiceConstruction\ServiceBuilder $serviceBuilder
     */
    public function setServiceBuilder(ServiceBuilder $serviceBuilder)
    {
        $this->serviceBuilder = $serviceBuilder;
    }
    
    public function getServiceById($id)
    {
        $cx = $this->getContext();
        if (!$cx->has($id)) {
            $definition = $this->getDefinitionsHolder()->get($id);
            if (!$definition) {
                throw new ServiceNotFoundException("Unable to find a service '$id'.");
            }

            $obj = $this->getServiceBuilder()->process($definition);

            $cx->register($definition, $obj);
        } else {
            return $this->getContext()->get($id);
        }
    }

    public function getServiceByQualifier($qualifier)
    {
            
    }

    static public function clazz()
    {
        return get_called_class();
    }

    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return \Vobla\Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
