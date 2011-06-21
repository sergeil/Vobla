<?php

namespace Vobla;

require_once __DIR__.'/../bootstrap.php';

use Vobla\Container,
    Vobla\Configuration,
    Vobla\Context\DefaultContextScopeHandlersProvider,
    Vobla\ServiceConstruction\Assemblers\DefaultAssemblersProvider,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder,
    Doctrine\Common\Annotations\AnnotationReader;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testIt()
    {
        $cfg = new Configuration();
        $cfg->setContextScopeHandlersProvider(new DefaultContextScopeHandlersProvider());
        $cfg->setAssemblersProvider(new DefaultAssemblersProvider());

        $c = new Container($cfg);

        $ab = new AnnotationsBuilder(new AnnotationReader());
        $ab->processClass();
    }
}
