<?php

namespace Vobla\Context\ScopeHandler;

require_once __DIR__.'/../../../../bootstrap.php';

use \Vobla\Context\ScopeHandlers\SingletonHandler,
    \Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class SingletonHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\Context\ScopeHandlers\SingletonHandler
     */
    protected $sh;

    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->sh = new SingletonHandler();
        $this->mf = new \Moko\MockFactory($this);
    }

    public function tearDown()
    {
        $this->sh = null;
        $this->mf = null;
    }

    public function testIsRegisterResponsible()
    {
        $def1 = $this->mf->createTestCaseAware(ServiceDefinition::clazz())->addMethod('getScope', function() {
            return 'singleton';
        }, 1)->createMock();

        $def2 = $this->mf->createTestCaseAware(ServiceDefinition::clazz())->addMethod('getScope', function() {
            return '';
        }, 1)->createMock();

        $def3 = $this->mf->createTestCaseAware(ServiceDefinition::clazz())->addMethod('getScope', function() {
            return 'foo';
        }, 1)->createMock();

        $def = new ServiceDefinition();

        $this->assertTrue(
            $this->sh->isRegisterResponsible('foo', $def1, null),
            sprintf('It is expected that "%s" is responsible for handling of "singleton" scope.', SingletonHandler::clazz())
        );
        $this->assertTrue(
            $this->sh->isRegisterResponsible('foo', $def2, null),
            sprintf(
                'It is expected that "%s" is responsible for handling as a default scope handling, in other words - scope field is empty.',
                SingletonHandler::clazz()
            )
        );
        $this->assertFalse(
            $this->sh->isRegisterResponsible('foo', $def3, null),
            sprintf('"%s" should be responsible only for "singleton" and "empty" scopes..', SingletonHandler::clazz())
        );
    }

    public function testRegisterThenContainsThenDispense()
    {
        $this->assertFalse(
            $this->sh->contains('fooService'),
            sprintf('"%s" shouldn\'t contain any services by default!', SingletonHandler::clazz())
        );

        $obj = new \stdClass();
        $this->sh->register('fooService', $obj);

        $this->assertTrue(
            $this->sh->contains('fooService'),
            sprintf(
                'We just registered "fooService" but "%s" for some reason is not able to understand this fact.',
                SingletonHandler::clazz()
            )
        );

        $dispensedObj = $this->sh->dispense('fooService');
        $this->assertType('stdClass', $dispensedObj, 'Returned object must be of "stdClass" type.');
        $this->assertSame(
            $obj,
            $dispensedObj,
            sprintf(
                'Once a service is registered, %s::dispense should return the same instance of it.',
                SingletonHandler::clazz()
            )
        );
    }
}
