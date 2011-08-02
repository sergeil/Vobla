<?php

namespace Vobla\Extensibility\ProvidersDecoration\Builders;

require_once __DIR__.'/../../../../../bootstrap.php';

use Vobla\ServiceConstruction\Builders\XmlBuilder\ProcessorsProvider as XmlBuilderProcessorsProvider,
    Vobla\Extensibility\ProvidersDecoration\Builders\DecoratedXmlBuilderProcessorsProvider,
    Vobla\Extensibility\PluginManager;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class DecoratedXmlBuilderProcessorsProviderTest extends \PHPUnit_Framework_TestCase
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

        $xmlBuilderProcessorsProvider = $this->mf->createTestCaseAware(XmlBuilderProcessorsProvider::CLAZZ)
        ->addMethod('getProcessors', array($p1, $p2))
        ->createMock();

        $pm = $this->mf->createTestCaseAware(PluginManager::clazz())->createMock();

        $decoratedXmlBuilderProcessorsProvider = new DecoratedXmlBuilderProcessorsProvider($xmlBuilderProcessorsProvider, $pm);
        $result = $decoratedXmlBuilderProcessorsProvider->getProcessors();
        $this->assertTrue(is_array($result));

        $this->assertSame($p1, current($result));
        $this->assertSame($p2, next($result));
    }
}
