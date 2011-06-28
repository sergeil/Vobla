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

use \Vobla\Context\ScopeHandlers\SingletonHandler,
    \Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
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
