<?php

namespace Vobla\Context;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface ContextScopeHandler
{
    const CLAZZ = 'Vobla\Context\ContextScopeHandler';

    public function isRegisterResponsible($id, $obj);

    public function isDispenseResponsible($id);

    public function register($id, $obj);

    public function dispense($id);
}
