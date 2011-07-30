<?php
/*
 * Copyright (c) 2011 Sergei Lissovski, http://sergei.lissovski.org
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:

 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Vobla;

use Vobla\ServiceConstruction\ServiceBuilder,
    Vobla\ServiceConstruction\DefinitionsHolder,
    Vobla\Context\CompositeContext,
    Vobla\Context\Context,
    Vobla\ServiceLocating\DefaultImpls\QualifierServiceLocator,
    Vobla\ServiceLocating\CompositeServiceLocator,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceLocating\ServiceLocator;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
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
     * @var \Vobla\ServiceLocating\ServiceLocator
     */
    protected $serviceLocator;

    /**
     * @var \Vobla\ConfigHolder
     */
    protected $configHolder;

    /**
     * @var \Vobla\Configuration
     */
    protected $configuration;

    /**
     * @var \Vobla\BuildersFactory
     */
    protected $buildersFactory;

    public function __construct($configuration = null)
    {
        if (null === $configuration) {
            $this->setConfiguration(new Configuration());
        }
    }

    public function setContext(Context $context)
    {
        $context->init($this);
        $this->context = $context;
    }

    /**
     * @return Vobla\Context\Context
     */
    public function getContext()
    {
        if (null == $this->context) {
            $this->setContext(new CompositeContext());
        }

        return $this->context;
    }

    /**
     * @param \Vobla\ServiceConstruction\ServiceBuilder $serviceBuilder
     */
    public function setServiceBuilder(ServiceBuilder $serviceBuilder)
    {
        $serviceBuilder->init($this);
        $this->serviceBuilder = $serviceBuilder;
    }

    /**
     * @return \Vobla\ServiceConstruction\ServiceBuilder
     */
    public function getServiceBuilder()
    {
        if (null === $this->serviceBuilder) {
            $this->setServiceBuilder(new ServiceBuilder());
        }

        return $this->serviceBuilder;
    }

    public function setDefinitionsHolder(DefinitionsHolder $definitionsHolder)
    {
        $this->definitionsHolder = $definitionsHolder;
    }

    /**
     * @return \Vobla\ServiceConstruction\DefinitionsHolder
     */
    public function getDefinitionsHolder()
    {
        if (null === $this->definitionsHolder) {
            $this->definitionsHolder = new DefinitionsHolder();
        }

        return $this->definitionsHolder;
    }

    public function setServiceLocator(ServiceLocator $serviceLocator)
    {
        $serviceLocator->init($this);
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return \Vobla\ServiceLocating\ServiceLocator
     */
    public function getServiceLocator()
    {
        if (null === $this->serviceLocator) {
            $this->setServiceLocator(new CompositeServiceLocator());
        }

        return $this->serviceLocator;
    }

    public function setConfiguration(Configuration $configuration)
    {
        $configuration->validate();
        
        $this->configuration = $configuration;
        $configuration->getAssemblersProvider()->init($this);
        $configuration->getContextScopeHandlersProvider()->init($this);
        $configuration->getServiceLocatorsProvider()->init($this);
    }
    
    /**
     * @return \Vobla\Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function setConfigHolder(ConfigHolder $configHolder)
    {
        $this->configHolder = $configHolder;
    }

    /**
     * @return \Vobla\ConfigHolder
     */
    public function getConfigHolder()
    {
        if (null === $this->configHolder) {
            $this->configHolder = new ConfigHolder();
        }

        return $this->configHolder;
    }

    /**
     * @param \Vobla\BuildersFactory $buildersFactory
     */
    public function setBuildersFactory(BuildersFactory $buildersFactory)
    {
        $buildersFactory->init($this);
        $this->buildersFactory = $buildersFactory;
    }

    /**
     * @return \Vobla\BuildersFactory
     */
    public function getBuildersFactory()
    {
        if (null === $this->buildersFactory) {
            $this->setBuildersFactory(new BuildersFactory());
        }

        return $this->buildersFactory;
    }

    public function addServiceDefinition($id, ServiceDefinition $serviceDefinition)
    {
        $this->getDefinitionsHolder()->register($id, $serviceDefinition);
        $this->getServiceLocator()->analyze($id, $serviceDefinition);
    }
        
    public function getServiceById($id)
    {
        $cx = $this->getContext();
        if (!$cx->contains($id)) {
            $definition = $this->getDefinitionsHolder()->get($id);
            if (!$definition) {
                throw new ServiceNotFoundException("Unable to find a service '$id'.");
            }

            $obj = null;
            try {
                $obj = $this->getServiceBuilder()->process($definition);
            } catch (\Exception $e) {
                throw new Exception(
                    sprintf('Unable to construct a service with ID "%s"', $id),
                    null,
                    $e
                );
            }

            $cx->register($id, $obj);
        }

        return $this->getContext()->dispense($id);
    }

    public function getServiceByQualifier($qualifier)
    {
        $result = $this->getServiceLocator()->locate(QualifierServiceLocator::createCriteria($qualifier));
        if (sizeof($result) > 1) {
            throw new Exception(
                sprintf(
                    'Only one service must correspond to a qualifier, but several were found: %s',
                    implode(', ', $result)
                )
            );
        } else if (sizeof($result) == 0) {
            throw new ServiceNotFoundException("Unable to find a service by qualifier '$qualifier'.");
        }

        return $this->getServiceById($result[0]);
    }
            
    static public function clazz()
    {
        return get_called_class();
    }
}
