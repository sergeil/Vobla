<?php

namespace Vobla\Context;

use Vobla\Container;

/**
 * @todo throw an exception if no context-scope-handlers were found
 *
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class CompositeContext implements Context
{
    /**
     * @var array
     */
    protected $cachedHandlers = null;

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
     * @param \Vobla\Container $container
     */
    public function init(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    protected function getScopeHandlers()
    {
        if (null === $this->cachedHandlers) {
            $this->cachedHandlers = $this->getContainer()
                                         ->getConfiguration()
                                         ->getContextScopeHandlersProvider()
                                         ->getContextScopeHandlers();

            foreach ($this->cachedHandlers as $scopeHandler) {
                $scopeHandler->init($this->getContainer());
            }
        }

        return $this->cachedHandlers;
    }

    public function register($id, $obj)
    {
        foreach ($this->getScopeHandlers() as $handler) {
            $definition = $this->getContainer()->getDefinitionsHolder()->get($id);

            if ($handler->isRegisterResponsible($id, $definition, $obj)) {
                $handler->register($id, $obj);

                return;
            }
        }
    }

    public function dispense($id)
    {
        foreach ($this->getScopeHandlers() as $handler) {
            if ($handler->isDispenseResponsible($id)) {
                return $handler->dispense($id);
            }
        }
    }

    public function contains($id)
    {
        foreach ($this->getScopeHandlers() as $cachedHandler) {
            if ($cachedHandler->contains($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
