<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Assemblers\AssemblersManager,
    Vobla\ServiceConstruction\Assemblers\Assembler,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class AssemblersManagerTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @expectedException \Vobla\Exception
     */
    public function test__construct()
    {
        new AssemblersManager(array());
    }

    public function testProceed()
    {
        $tc = $this;
        $definition = new ServiceDefinition();

        $serviceObj = new \stdClass();

        $a1 = $this->mf->createTestCaseAware(Assembler::CLAZZ)->addMethod('execute', function($self, $am, $argDef) use($tc, $definition, $serviceObj) {
            $tc->assertSame($definition, $argDef);

            $serviceObj->foo = true;

            return $am->proceed($argDef, $serviceObj);
        }, 1, 'a1')->createMock();

        $a2 = $this->mf->createTestCaseAware(Assembler::CLAZZ)->addMethod('execute', function($self, $am, $argDef, $argServiceObj) use($tc, $definition, $serviceObj) {
            $tc->assertSame($definition, $argDef);
            $tc->assertSame($argServiceObj, $serviceObj);

            $serviceObj->bar = true;

            return $serviceObj;
        }, 1, 'a2')->createMock();

        $a3 = $this->mf->createTestCaseAware(Assembler::CLAZZ)->addMethod('execute', function() {
        }, 0, 'a3')->createMock();

        $assemblers = array($a1, $a2, $a3);
        $am = new AssemblersManager($assemblers);
        $resultingServiceObj = $am->proceed($definition);

        $this->assertTrue(isset($serviceObj->foo), 'It seems that first assembler was not invoked.');
        $this->assertTrue(isset($serviceObj->bar), 'It seems that second assembler was not invoked.');
        $this->assertSame($resultingServiceObj, $serviceObj, "AssemblerManager didn't return an result of assemblers chain execution. ( assembled service object )");
    }
}
