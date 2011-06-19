<?php

namespace Vobla\Context;

use Vobla\Container;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface ContextScopeHandlersProvider
{
    const CLAZZ = 'Vobla\Context\ContextScopeHandlersProvider';

    public function init(Container $container);

    /**
     * @return array
     */
    public function getContextScopeHandlers();
}
