<?php

namespace Vobla\Context;

use \Vobla\Container,
    Vobla\Context\ScopeHandlers\SingletonHandler;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class DefaultContextScopeHandlersProvider implements ContextScopeHandlersProvider
{
    protected $scopeHandlers = array();

    public function __construct()
    {
        $this->scopeHandlers = array(
            new SingletonHandler()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContextScopeHandlers()
    {
        return $this->scopeHandlers;
    }

    /**
     * {@inheritdoc}
     */
    public function init(Container $container)
    {
    }
}