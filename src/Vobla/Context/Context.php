<?php

namespace Vobla\Context;

use Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface Context
{
    public function init(Container $container);

    public function register($id, $obj);

    public function dispense($id);

    public function contains($id);
}
