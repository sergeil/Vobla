<?php

namespace Vobla\Extensibility\ProvidersDecoration;

require_once __DIR__.'/../../../../bootstrap.php';

use Vobla\ServiceLocating\ServiceLocatorsProvider,
    Vobla\Extensibility\ProvidersDecoration\DecoratedServiceLocatorsProvider,
    Vobla\Extensibility\PluginManager;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class DecoratedServiceLocatorsProviderTest extends \PHPUnit_Framework_TestCase
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

        $serviceLocatorsProvider = $this->mf->createTestCaseAware(ServiceLocatorsProvider::CLAZZ)
        ->addMethod('getServiceLocators', array($p1, $p2))
        ->createMock();

        $pm = $this->mf->createTestCaseAware(PluginManager::clazz())->createMock();

        $decoratedServiceLocatorsProvider = new DecoratedServiceLocatorsProvider($serviceLocatorsProvider, $pm);
        $result = $decoratedServiceLocatorsProvider->getServiceLocators();
        $this->assertTrue(is_array($result));

        $this->assertSame($p1, current($result));
        $this->assertSame($p2, next($result));
    }
}
