<?php

namespace Vobla\Extensibility\ProvidersDecoration;

require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class AbstractDecorationAwareProviderTest extends \PHPUnit_Framework_TestCase
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
        $this->p = $this->mf->createTestCaseAware(AbstractDecorationAwareProvider::clazz())
                      ->addDelegateMethod('putBefore')
                      ->addDelegateMethod('putAfter')
                      ->addDelegateMethod('exists')
                      ->addDelegateMethod('getSortedProviders')
                      ->addDelegateMethod('put')
                      ->addDelegateMethod('putFirst')
                      ->addDelegateMethod('putLast');
    }

    public function tearDown()
    {
        $this->p = null;
    }

    public function testExists()
    {
        $providers = array(
            new \stdClass()
        );

        /* @var \Vobla\Extensibility\ProvidersDecoration\DecorationAwareProvider $p */
        $p = $this->p->addMethod('getProviders', $providers)->createMock();

        $this->assertTrue($p->exists('stdClass'));
        $this->assertFalse($p->exists('foo'));
    }

    public function xtestPutBefore()
    {
        $fooProvider = new \stdClass();
        $barProvider = new \SimpleXMLElement('<x></x>');

        $mockProviders = array(
            $fooProvider
        );

        /* @var \Vobla\Extensibility\ProvidersDecoration\DecorationAwareProvider $p */
        $p = $this->p->addMethod('getProviders', $mockProviders)->createMock();
        
        $p->putBefore('stdClass', $barProvider);

        $providers = array_values($p->getSortedProviders());
        $this->assertEquals(2, sizeof($providers));
        $this->assertSame($barProvider, $providers[0]);
        $this->assertSame($fooProvider, $providers[1]);
    }

    public function testPutAfter()
    {
        $fooProvider = new \stdClass();
        $barProvider = new \SimpleXMLElement('<x></x>');
        $bazProvider = new \SplStack();

        $mockProviders = array(
            $fooProvider,
            $barProvider
        );

        /* @var \Vobla\Extensibility\ProvidersDecoration\DecorationAwareProvider $p */
        $p = $this->p->addMethod('getProviders', $mockProviders)->createMock();

        $p->putAfter('stdClass', $bazProvider);

        $providers = array_values($p->getSortedProviders());

        $this->assertEquals(3, sizeof($providers));
        $this->assertSame($fooProvider, $providers[0]);
        $this->assertSame($bazProvider, $providers[1]);
        $this->assertSame($barProvider, $providers[2]);
    }

    public function testPutFirst()
    {
        $fooProvider = new \stdClass();
        $barProvider = new \SimpleXMLElement('<x></x>');

        $mockProviders = array(
            $fooProvider
        );

        /* @var \Vobla\Extensibility\ProvidersDecoration\DecorationAwareProvider $p */
        $p = $this->p->addMethod('getProviders', $mockProviders)->createMock();

        $p->putFirst($barProvider);

        $providers = array_values($p->getSortedProviders());
        $this->assertEquals(2, sizeof($providers));
        $this->assertSame($barProvider, $providers[0]);
        $this->assertSame($fooProvider, $providers[1]);
    }

    public function testPutLast()
    {
        $fooProvider = new \stdClass();
        $barProvider = new \SimpleXMLElement('<x></x>');

        $mockProviders = array(
            $fooProvider
        );

        /* @var \Vobla\Extensibility\ProvidersDecoration\DecorationAwareProvider $p */
        $p = $this->p->addMethod('getProviders', $mockProviders)->createMock();

        $p->putLast($barProvider);

        $providers = array_values($p->getSortedProviders());
        $this->assertEquals(2, sizeof($providers));
        $this->assertSame($fooProvider, $providers[0]);
        $this->assertSame($barProvider, $providers[1]);
    }
}
