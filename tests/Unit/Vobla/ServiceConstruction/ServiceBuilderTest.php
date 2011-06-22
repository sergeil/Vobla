<?php

namespace Vobla\ServiceConstruction;

use Vobla\Container,
    Vobla\ServiceConstruction\Assemblers\AssemblersProvider,
    \Vobla\ServiceConstruction\ServiceBuilder,
    \Vobla\ServiceConstruction\Definition\ServiceDefinition,
    \Vobla\Configuration,
    \Vobla\ServiceConstruction\Assemblers\Assembler;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ServiceBuilderTest extends \PHPUnit_Framework_TestCase
{
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

    public function testProcess()
    {
        $tc = $this;
        $def = new ServiceDefinition();

        $asmb = $this->mf->createTestCaseAware(Assembler::CLAZZ)->addMethod('execute', function($self, $am, $argDef) use($tc, $def) {
            $tc->assertSame($def, $argDef);
        }, 1)->createMock();

        $ap = $this->mf->createTestCaseAware(AssemblersProvider::CLAZZ)->addMethod('getAssemblers', function() use ($asmb) {
            return array(
                $asmb
            );
        }, 1)->createMock();

        $cfg = $this->mf->createTestCaseAware(Configuration::clazz())->addMethod('getAssemblersProvider', function() use($ap) {
            return $ap;
        }, 1)->createMock();

        $ctr = $this->mf->createTestCaseAware(Container::clazz())->addMethod('getConfiguration', function() use($cfg) {
            return $cfg;
        }, 1)->createMock();

        $sb = new ServiceBuilder();
        $sb->init($ctr);

        $this->assertNull($sb->process($def));
    }
}
