<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Assemblers\ReferencesWeaverAssembler,
    Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Assemblers\AssemblersManager,
    Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Definition\QualifiedReference;

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
            'foo' => new ServiceReference('fooService'),
            'bar' => new QualifiedReference('barQualifier')
        ));

        $injectMethod = function($self, $obj, $paramName, $paramValue, $def) use($tc) {
            $args = $def->getArguments();
            
            $tc->assertTrue(isset($args[$paramName]), 'Expected parameter was not injected.');

            if ($paramName == 'foo') {
                $tc->assertEquals('resolvedByIdService', $paramValue);
            } else if ($paramName == 'bar') {
                $tc->assertEquals('resolvedByQualifierService', $paramValue);
            } else {
                $tc->fail('Injector should have been used only for injection of "foo", "bar" parameters.');
            }
        };
        $ri = $this->mf->createTestCaseAware(ReferenceInjector::CLAZZ)->addMethod('inject', $injectMethod, 2)->createMock();

        $proceedMethod = function($self, $am, $obj) use($tc, $serviceObj) {
            $tc->assertSame($serviceObj, $obj);
        };
        $ma = $this->mf->create(AssemblersManager::clazz(), true)->addMethod('proceed', $proceedMethod, 1)->createMock();

        $c = $this->mf->createTestCaseAware(Container::clazz())
        ->addMethod('getServiceById', function($self, $id) use($tc) {
            $tc->assertEquals(
                'fooService',
                $id,
                'Resolving by id should have been done only for service with id "fooService".'
            );

            return 'resolvedByIdService';
        }, 1)
        ->addMethod('getServiceByQualifier', function($self, $qualifier) use($tc) {
            $tc->assertEquals(
                'barQualifier',
                $qualifier,
                'Resolving by qualifier should have been done only for service with qualifier "barQualifier"'
            );

            return 'resolvedByQualifierService';
        }, 1)->createMock();

        $rwa = new ReferencesWeaverAssembler($ri);
        $rwa->init($c);
        $rwa->execute($ma, $def, $serviceObj);
    }
}
