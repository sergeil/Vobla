<?php

namespace Vobla\Extensibility\ProvidersDecoration;

require_once __DIR__.'/../../../../bootstrap.php';

use Vobla\Context\ContextScopeHandlersProvider,
    Vobla\Extensibility\ProvidersDecoration\DecoratedContextScopeHandlersProvider,
    Vobla\Extensibility\PluginManager;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class DecoratedContextScopeHandlersProviderTest extends \PHPUnit_Framework_TestCase
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

        $contextScopeHandlersProvider = $this->mf->createTestCaseAware(ContextScopeHandlersProvider::CLAZZ)
        ->addMethod('getContextScopeHandlers', array($p1, $p2))
        ->createMock();

        $pm = $this->mf->createTestCaseAware(PluginManager::clazz())->createMock();

        $decoratedContextScopeHandlersProvider = new DecoratedContextScopeHandlersProvider($contextScopeHandlersProvider, $pm);
        $result = $decoratedContextScopeHandlersProvider->getContextScopeHandlers();
        $this->assertTrue(is_array($result));

        $this->assertSame($p1, current($result));
        $this->assertSame($p2, next($result));
    }
}
