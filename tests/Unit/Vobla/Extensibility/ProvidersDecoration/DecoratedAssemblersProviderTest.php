<?php

namespace Vobla\Extensibility\ProvidersDecoration;

require_once __DIR__.'/../../../../bootstrap.php';

use Vobla\ServiceConstruction\Assemblers\AssemblersProvider,
    Vobla\Extensibility\ProvidersDecoration\DecoratedAssemblersProvider,
    Vobla\Extensibility\PluginManager;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class DecoratedAssemblersProviderTest extends \PHPUnit_Framework_TestCase
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

    public function testGetAssemblers()
    {
        $p1 = new \stdClass();
        $p2 = new \SimpleXMLElement('<x></x>');

        $assemblersProvider = $this->mf->createTestCaseAware(AssemblersProvider::CLAZZ)
        ->addMethod('getAssemblers', array($p1, $p2))
        ->createMock();

        $pm = $this->mf->createTestCaseAware(PluginManager::clazz())->createMock();

        $decoratedAssemblersProvider = new DecoratedAssemblersProvider($assemblersProvider, $pm);
        $result = $decoratedAssemblersProvider->getAssemblers();
        $this->assertTrue(is_array($result));

        $this->assertTrue(
            isset($result[0]),
            sprintf('%s::getAssemblers() method should return non-associative array', DecoratedAssemblersProvider::clazz())
        );
        $this->assertSame($p1, $result[0]);

        $this->assertTrue(
            isset($result[1]),
            sprintf('%s::getAssemblers() method should return non-associative array', DecoratedAssemblersProvider::clazz())
        );
        $this->assertSame($p2, $result[1]);
    }
}
