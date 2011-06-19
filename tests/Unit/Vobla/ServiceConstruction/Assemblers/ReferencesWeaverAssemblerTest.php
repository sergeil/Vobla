<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Assemblers\ReferencesWeaverAssembler,
    Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Assemblers\AssemblersManager;

require_once 'fixtures/classes.php';
require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ReferencesWeaverAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
    }

    public function testExecute()
    {
        $tc = $this;

        $serviceObj = new \stdClass();

        $def = new ServiceDefinition();
        $def->setArguments(array(
            'foo' => 'fooVal',
            'bar' => 'barVal'
        ));

        $injectMethod = function($self, $obj, $paramName, $paramValue, $def) use($tc) {
            $args = $def->getArguments();
            $tc->assertTrue(isset($args[$paramName]), 'Expected parameter was not injected.');
            $tc->assertEquals($paramValue, $args[$paramName], 'Value of a parameter to be injected is not the one we expected.');
        };
        $ri = $this->mf->createTestCaseAware(ReferenceInjector::CLAZZ)->addMethod('inject', $injectMethod, 2)->createMock();

        $proceedMethod = function($self, $am, $obj) use($tc, $serviceObj) {
            $tc->assertSame($serviceObj, $obj);
        };
        $ma = $this->mf->create(AssemblersManager::clazz(), true)->addMethod('proceed', $proceedMethod, 1)->createMock();

        $rwa = new ReferencesWeaverAssembler($ri);
        $rwa->execute($ma, $def, $serviceObj);
    }
}
