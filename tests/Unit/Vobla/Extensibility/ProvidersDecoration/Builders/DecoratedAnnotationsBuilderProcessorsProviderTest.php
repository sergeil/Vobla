<?php

namespace Vobla\Extensibility\ProvidersDecoration\Builders;

require_once __DIR__.'/../../../../../bootstrap.php';

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ProcessorsProvider as AnnotationProcessorsProvider,
    Vobla\Extensibility\ProvidersDecoration\Builders\DecoratedAnnotationsBuilderProcessorsProvider,
    Vobla\Extensibility\PluginManager;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class DecoratedAnnotationsBuilderProcessorsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\Integrated\TestCaseAwareMockDefinition
     */
    protected $p;

    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
    }

    public function tearDown()
    {
        $this->mf = null;
    }

    public function testGetContextScopeHandlers()
    {
        $p1 = new \stdClass();
        $p2 = new \SimpleXMLElement('<x></x>');

        $annotationProcessorsProvider = $this->mf->createTestCaseAware(AnnotationProcessorsProvider::CLAZZ)
        ->addMethod('getProcessors', array($p1, $p2))
        ->createMock();

        $pm = $this->mf->createTestCaseAware(PluginManager::clazz())->createMock();

        $decoratedAnnotationProcessorsProvider = new DecoratedAnnotationsBuilderProcessorsProvider($annotationProcessorsProvider, $pm);
        $result = $decoratedAnnotationProcessorsProvider->getProcessors();
        $this->assertTrue(is_array($result));

        $this->assertSame($p1, current($result));
        $this->assertSame($p2, next($result));
    }
}
