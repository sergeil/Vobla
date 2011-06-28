<?php

namespace Vobla\ServiceConstruction\Builders;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
abstract class AbstractBuilder
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\ServiceIdGenerator
     */
    protected $serviceIdGenerator;

    /**
     * @var array
     */
    protected $cachedProcessors;

    /**
     * @return array
     */
    public function getProcessors()
    {
        if (null === $this->cachedProcessors) {
            $this->cachedProcessors = $this->processorsProvider->getProcessors();

            // TODO throw an exception if no processors provided
        }

        return $this->cachedProcessors;
    }

    public function setServiceIdGenerator($serviceIdGenerator)
    {
        $this->serviceIdGenerator = $serviceIdGenerator;
    }

    /**
     * @return \Vobla\ServiceConstruction\Builders\ServiceIdGenerator
     */
    public function getServiceIdGenerator()
    {
        if (null === $this->serviceIdGenerator) {
            $this->serviceIdGenerator = new ServiceIdGenerator();
        }

        return $this->serviceIdGenerator;
    }

    /**
     * @return mixed
     */
    abstract protected function getDefaultProcessorsProvider();

    public function __construct($processorsProvider = null)
    {
        if (null === $processorsProvider) {
            $this->processorsProvider = $this->getDefaultProcessorsProvider();
        } else {
            $this->processorsProvider = $processorsProvider;
        }
    }
}
