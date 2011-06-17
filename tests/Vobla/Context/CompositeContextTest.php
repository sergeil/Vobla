<?php

namespace Vobla\Context;

require_once __DIR__.'/../../bootstrap.php';

use Vobla\Container,
    Vobla\Configuration,
    Vobla\Context\ContextScopeHandler;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
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

    protected function createMockContainer(array $sessionScopeHandlers)
    {
        $shs = $sessionScopeHandlers;

        $cfg = $this->mf->createTestCaseAware(Configuration::clazz())->addMethod('getContextScopeHandlers', function() use ($shs) {
            return $shs;
        }, 1)->createMock();

        $ctr = $this->mf->createTestCaseAware(Container::clazz(), true)->addMethod('getConfiguration', function() use($cfg) {
            return $cfg;
        }, 1)->createMock();

        return $ctr;
    }

    public function testRegister()
    {
        $ser1 = new \stdClass();

        $tc = $this;

        $sh1 = $this->mf->createTestCaseAware(ContextScopeHandler::CLAZZ)->addMethod('register', function() use($tc) {
            
        }, 1)->addMethod('isRegisterResponsible', function($self, $id, $obj) use ($tc, $ser1) {
            $tc->assertEquals('ser1', $id, 'The ID passed to the CompositeContext::isRegisterResponsible() and to one of its ContextScopeHandler are different.');
            $tc->assertSame($obj, $ser1, 'Object passed to the CompositeContext::isRegisterResponsible($id, $obj) and to one of its ContextScopeHandler are different.');
            return true;
        }, 1, 'sh1')->createMock();

        $sh2 = $this->mf->createTestCaseAware(ContextScopeHandler::CLAZZ)->addMethod('register', function() {
        }, 0)->addMethod('isRegisterResponsible', function() {
            
        }, 0, 'sh2')->createMock();

        $ctr = $this->createMockContainer(array($sh1, $sh2));

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
        }, 1, 'sh1')->createMock();

        $sh2 = $this->mf->createTestCaseAware(ContextScopeHandler::CLAZZ)->addMethod('dispense', function() {
                
        }, 0)->addMethod('isDispenseResponsible', function() {
                
        }, 0, 'sh2')->createMock();

        $ctr = $this->createMockContainer(array($sh1, $sh2));

        $cx = $this->cx;
        $cx->init($ctr);

        $cx->dispense('ser1');
    }
}
