<?php

namespace Vobla\Context\ScopeHandlers;

use Vobla\Context\ContextScopeHandler,
    Vobla\Container;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
abstract class AbstractHandler implements ContextScopeHandler
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

    public function init(Container $container)
    {
        $this->container = $container;
    }
}
