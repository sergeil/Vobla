<?php

namespace Vobla;

use Vobla\ServiceConstruction\ServiceBuilder,
    Vobla\ServiceConstruction\DefinitionsHolder,
    Vobla\Context\CompositeContext,
    Vobla\Context\Context;

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

    /**
     * @var \Vobla\Context\Context
     */
    protected $context;

    /**
     * @var \Vobla\ServiceConstruction\DefinitionsHolder
     */
    protected $definitionsHolder;

    /**
     * @var \Vobla\Configuration
     */
    protected $configuration;

    public function __construct(Configuration $configuration)
    {
        $configuration->validate();
        $this->setConfiguration($configuration);
    }
    
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        if (null == $this->context) {
            $this->context = new CompositeContext();
            $this->context->init($this);
        }

        return $this->context;
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

    public function setDefinitionsHolder(DefinitionsHolder $definitionsHolder)
    {
        $this->definitionsHolder = $definitionsHolder;
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

    /**
     * @return ServiceConstruction\DefinitionsHolder
     */
    public function getDefinitionsHolder()
    {
        if (null === $this->definitionsHolder) {
            $this->definitionsHolder = new DefinitionsHolder();
        }

        return $this->definitionsHolder;
    }
        
    public function getServiceById($id)
    {
        $cx = $this->getContext();
        if (!$cx->contains($id)) {
            $definition = $this->getDefinitionsHolder()->get($id);
            if (!$definition) {
                throw new ServiceNotFoundException("Unable to find a service '$id'.");
            }

            $obj = $this->getServiceBuilder()->process($definition);

            $cx->register($definition, $definition, $obj);
        } else {
            return $this->getContext()->dispense($id);
        }
    }

    public function getServiceByQualifier($qualifier)
    {
        // TODO
    }
    
    static public function clazz()
    {
        return get_called_class();
    }
}
