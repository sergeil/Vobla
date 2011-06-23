<?php

namespace Vobla;

require_once __DIR__.'/../bootstrap.php';

require_once __DIR__.'/Fixtures/Foo.php';
require_once __DIR__.'/Fixtures/Bar.php';
require_once __DIR__.'/Fixtures/FooBar.php';

use Vobla\Container,
    Vobla\Configuration,
    Vobla\Context\DefaultContextScopeHandlersProvider,
    Vobla\ServiceConstruction\Assemblers\DefaultAssemblersProvider,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder,
    Doctrine\Common\Annotations\AnnotationReader,
    \Vobla\ServiceLocating\DefaultServiceLocatorsProvider;

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
        $cfg->setServiceLocatorsProvider(new DefaultServiceLocatorsProvider());

        $container = new Container($cfg);

        $ar = new AnnotationReader();
        $ar->setAutoloadAnnotations(true);

        $ab = new AnnotationsBuilder($ar);
        $ab->processPath($container, __DIR__.'/Fixtures');

        /* @var FooService $fooService */
        $fooService = $container->getServiceById('fooService');
        $this->assertType('Foo', $fooService);
        $this->assertType('Bar', $fooService->bar);
        $this->assertType('FooBar', $fooService->foobar);
    }
}
