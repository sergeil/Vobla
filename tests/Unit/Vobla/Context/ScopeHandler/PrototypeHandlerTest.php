<?php
/*
 * Copyright (c) 2011 Sergei Lissovski, http://sergei.lissovski.org
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:

 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Vobla\Context\ScopeHandler;

require_once __DIR__.'/../../../../bootstrap.php';

use \Vobla\Context\ScopeHandlers\PrototypeHandler,
    \Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
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
