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

namespace Vobla\Context;

require_once __DIR__.'/../../../bootstrap.php';

use Vobla\Container,
    Vobla\Configuration,
    Vobla\Context\ContextScopeHandler,
    Vobla\Context\ContextScopeHandlersProvider,
    Vobla\ServiceConstruction\DefinitionsHolder,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class CompositeContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    /**
     * @var \Vobla\Context\CompositeContext
     */
    protected $cx;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
        $this->cx = new CompositeContext();
    }

    public function tearDown()
    {
        $this->mf = null;
        $this->cx = null;
    }

    /**
     * @param  array $sessionScopeHandlers
     * @return \Moko\Integrated\TestCaseAwareMockDefinition
     */
    protected function getContainerMockDefinition($sessionScopeHandlers)
    {
        $shs = $sessionScopeHandlers;

        $getContextScopeHandlers = function() use($shs) {
            return $shs;
        };
        $schProvider = $this->mf->createTestCaseAware(ContextScopeHandlersProvider::CLAZZ)->addMethod(
            'getContextScopeHandlers',
            $getContextScopeHandlers,
            1
        )->createMock();

        $cfg = $this->mf->createTestCaseAware(Configuration::clazz())->addMethod('getContextScopeHandlersProvider', function() use ($schProvider) {
            return $schProvider;
        }, 1)->createMock();

        $ctr = $this->mf->createTestCaseAware(Container::clazz(), true)->addMethod('getConfiguration', function() use($cfg) {
            return $cfg;
        }, 1);

        return $ctr;
    }

    protected function createMockContainer(array $sessionScopeHandlers)
    {
        return $this->getContainerMockDefinition($sessionScopeHandlers)->createMock();
    }

    public function test_properAssemblersInitialization()
    {
        $ser1 = new \stdClass();

        $tc = $this;

        $sh1 = $this->mf->createTestCaseAware(ContextScopeHandler::CLAZZ)->addMethod('init', function() {
        }, 1)->addMethod('isRegisterResponsible', function() {
                return true;
        }, 1)->addMethod('register', function() {
        }, 1)->createMock();
        
        $serviceDef = new ServiceDefinition();
        $definitionsHolder = $this->mf->createTestCaseAware(DefinitionsHolder::clazz())->addMethod('get', function() use($serviceDef) {
            return $serviceDef;
        }, 1)->createMock();

        /* @var \Moko\Integrated\TestCaseAwareMockDefinition $ctrMock */
        $ctrMock = $this->getContainerMockDefinition(array($sh1));
        $ctr = $ctrMock->addMethod('getDefinitionsHolder', function() use($definitionsHolder) {
            return $definitionsHolder;
        }, 1)->createMock();

        $cx = $this->cx;
        $cx->init($ctr);

        $cx->register('ser1', $ser1);
    }

    public function testRegister()
    {
        $ser1 = new \stdClass();

        $tc = $this;

        $sh1 = $this->mf->createTestCaseAware(ContextScopeHandler::CLAZZ)->addMethod('register', function() use($tc) {
            
        }, 1)->addMethod('isRegisterResponsible', function($self, $id, $serviceDefinition, $obj) use ($tc, $ser1) {
            $tc->assertEquals('ser1', $id, 'The ID passed to the CompositeContext::isRegisterResponsible() and to one of its ContextScopeHandler are different.');
            $tc->assertSame($obj, $ser1, 'Object passed to the CompositeContext::isRegisterResponsible($id, $obj) and to one of its ContextScopeHandler are different.');
            return true;
        }, 1, 'sh1')->addMethod('init', function() {}, 1)->createMock();

        $sh2 = $this->mf->createTestCaseAware(ContextScopeHandler::CLAZZ)->addMethod('register', function() {
        }, 0)->addMethod('isRegisterResponsible', function() {
            
        }, 0, 'sh2')->addMethod('init', function() {}, 1)->createMock();

        $serviceDef = new ServiceDefinition();
        $definitionsHolder = $this->mf->createTestCaseAware(DefinitionsHolder::clazz())->addMethod('get', function() use($serviceDef) {
            return $serviceDef;
        }, 1)->createMock();
        
        /* @var \Moko\Integrated\TestCaseAwareMockDefinition $ctrMock */
        $ctrMock = $this->getContainerMockDefinition(array($sh1, $sh2));
        $ctr = $ctrMock->addMethod('getDefinitionsHolder', function() use($definitionsHolder) {
            return $definitionsHolder;
        }, 1)->createMock();

        $cx = $this->cx;
        $cx->init($ctr);

        $cx->register('ser1', $ser1);
    }

    public function testDispense()
    {
        $tc = $this;

        $sh1 = $this->mf->createTestCaseAware(ContextScopeHandler::CLAZZ)->addMethod('dispense', function() use($tc) {
                
        }, 1)->addMethod('isDispenseResponsible', function($self, $id) use ($tc) {
            $tc->assertEquals('ser1', $id, 'The ID passed to the CompositeContext::isDispenseResponsible() and to one of its ContextScopeHandler are different.');
            return true;
        }, 1, 'sh1')->addMethod('init', function() {}, 1)->createMock();

        $sh2 = $this->mf->createTestCaseAware(ContextScopeHandler::CLAZZ)->addMethod('dispense', function() {
                
        }, 0)->addMethod('isDispenseResponsible', function() {
                
        }, 0, 'sh2')->addMethod('init', function() {}, 1)->createMock();

        $ctr = $this->createMockContainer(array($sh1, $sh2));

        $cx = $this->cx;
        $cx->init($ctr);

        $cx->dispense('ser1');
    }

    public function testContains()
    {
        $tc = $this;

        $sh1 = $this->mf->createTestCaseAware(ContextScopeHandler::CLAZZ)->addMethod('contains', function($self, $id) use($tc) {
            $tc->assertEquals('fooId', $id);
            return true;
        }, 1)->addMethod('init', function() {}, 1)->createMock();

        $sh2 = $this->mf->createTestCaseAware(ContextScopeHandler::CLAZZ)->addMethod('contains', function($self, $id) {
        }, 0)->addMethod('init', function() {}, 1)->createMock();

        $ctr = $this->createMockContainer(array($sh1, $sh2));

        $cx = $this->cx;
        $cx->init($ctr);

        $this->assertTrue(
            $cx->contains('fooId'),
            sprintf(
                '%s should have understood that one of the %s contains component with id "fooId" but it didn\'t.',
                CompositeContext::clazz(), ContextScopeHandler::CLAZZ
            )
        );
    }
}
