<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Assemblers\AssemblersManager,
    Vobla\ServiceConstruction\Assemblers\ObjectFactoryAssembler,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Definition\QualifiedReference,
    Vobla\Container;

require_once 'fixtures/classes.php';
require_once '../../../bootstrap.php';

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ObjectFactoryAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    /**
     * @var \Vobla\ServiceConstruction\Assemblers\ObjectFactoryAssembler
     */
    protected $ofa;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
        $this->ofa = new ObjectFactoryAssembler();
    }

    public function tearDown()
    {
        $this->mf = null;
        $this->ofa = null;
    }

    protected function createContainerMock()
    {
        $tc = $this;

        $container = $this->mf->createTestCaseAware(Container::clazz())
             ->addMethod('getServiceById', function($self, $serviceId) use($tc) {
            return $serviceId;
        }, 1)->addMethod('getServiceByQualifier', function($self, $qualifier) use ($tc) {
            return $qualifier;
        }, 1)->createMock();

        return $container;
    }

    protected function createAssemblersManagerMock()
    {
        $am = $this->mf
                   ->createTestCaseAware(AssemblersManager::clazz())
                   ->addMethod('proceed', function() {}, 1)->createMock();

        return $am;
    }

    public function testExecute_defaultConstructor()
    {
        $am = $this->createAssemblersManagerMock();
        $container = $this->createContainerMock();

        $this->ofa->init($container);

        $def = new ServiceDefinition();
        $def->setClassName(MockWithDefaultConstructor::clazz());
        $def->setConstructorArguments(array(
            new ServiceReference('fooService'),
            new QualifiedReference('fooQualifiedService')
        ));

        /* @var \Vobla\ServiceConstruction\Assemblers\MockWithDefaultConstructor $obj */
        $obj = $this->ofa->execute($am, $def);

        $this->assertType(MockWithDefaultConstructor::clazz(), $obj);
        $this->assertEquals('fooService', $obj->foo);
        $this->assertEquals('fooQualifiedService', $obj->bar);
    }

    public function testExecute_withLocalFactory()
    {
        $am = $this->createAssemblersManagerMock();
        $c = $this->createContainerMock();

        $this->ofa->init($c);

        $def = new ServiceDefinition();
        $def->setFactoryMethod('fooFactory');
        $def->setClassName(MockWithLocalFactory::clazz());
        $def->setConstructorArguments(array(
            new ServiceReference('fooService'),
            new QualifiedReference('fooQualifiedService')
        ));

        /* @var \Vobla\ServiceConstruction\Assemblers\MockWithLocalFactory $obj */
        $obj = $this->ofa->execute($am, $def);

        $this->assertType(MockWithLocalFactory::clazz(), $obj);
        $this->assertEquals('fooService', $obj->foo);
    }

    public function testExecute_withOtherComponentFactory()
    {
        $tc = $this;

        $am = $this->createAssemblersManagerMock();
        $c = $this->mf->createTestCaseAware(Container::clazz())->addMethod('getServiceById', function($self, $serviceId) use ($tc) {
            if ($serviceId == 'someFactoryService') {
                return new MockFactoryOfOtherClass();
            } else if (in_array($serviceId, array('fooService', 'barService'))) {
                return $serviceId;
            } else {
                $tc->fail(
                    sprintf(
                        '%s::getServiceById method should not be invoked with service ID other than %s',
                        Container::clazz(), implode(', ', array('someFactoryService', 'fooService', 'barService'))
                    )
                );
            }
        }, 3)->createMock();

        $this->ofa->init($c);

        $def = new ServiceDefinition();
        $def->setFactoryMethod('barFactory');
        $def->setFactoryService('someFactoryService');
        $def->setConstructorArguments(array(
            new ServiceReference('fooService'),
            new ServiceReference('barService')
        ));

        /* @var \Vobla\ServiceConstruction\Assemblers\MockWithDefaultConstructor $obj */
        $obj = $this->ofa->execute($am, $def);
        $this->assertType(MockWithDefaultConstructor::clazz(), $obj);
        $this->assertEquals('fooService', $obj->foo);
        $this->assertEquals('barService', $obj->bar);
    }
}
