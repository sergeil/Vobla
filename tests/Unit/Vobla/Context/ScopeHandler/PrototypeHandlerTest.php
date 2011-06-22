<?php

namespace Vobla\Context\ScopeHandler;

require_once __DIR__.'/../../../../bootstrap.php';

use \Vobla\Context\ScopeHandlers\PrototypeHandler,
    \Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class PrototypeHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\Context\ScopeHandlers\PrototypeHandler
     */
    protected $sh;

    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->sh = new PrototypeHandler();
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
            return 'prototype';
        }, 1)->createMock();

        $def3 = $this->mf->createTestCaseAware(ServiceDefinition::clazz())->addMethod('getScope', function() {
            return 'foo';
        }, 1)->createMock();

        $def = new ServiceDefinition();

        $this->assertTrue(
            $this->sh->isRegisterResponsible('foo', $def1, null),
            sprintf('It is expected that "%s" is responsible for handling of "prototype" scope.', PrototypeHandler::clazz())
        );
        $this->assertFalse(
            $this->sh->isRegisterResponsible('foo', $def3, null),
            sprintf('"%s" should be responsible only for "prototype" and "empty" scopes..', PrototypeHandler::clazz())
        );
    }

    public function testRegisterThenContainsThenDispense()
    {
        $this->assertFalse(
            $this->sh->contains('fooService'),
            sprintf('"%s" shouldn\'t contain any services by default!', PrototypeHandler::clazz())
        );

        $obj = new \stdClass();
        $this->sh->register('fooService', $obj);

        $this->assertTrue(
            $this->sh->contains('fooService'),
            sprintf(
                'We just registered "fooService" but "%s" for some reason is not able to understand this fact.',
                PrototypeHandler::clazz()
            )
        );

        $dispensedObj = $this->sh->dispense('fooService');
        $this->assertType('stdClass', $dispensedObj, 'Returned object must be of "stdClass" type.');
        $this->assertNotSame(
            $this->sh->dispense('fooService'),
            $this->sh->dispense('fooService'),
            sprintf('Every time an object is dispensed by "%s" a new copy should created.', PrototypeHandler::clazz())
        );
    }
}
