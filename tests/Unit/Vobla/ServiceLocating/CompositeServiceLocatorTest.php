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

namespace Vobla\ServiceLocating;

require_once __DIR__.'/../../../bootstrap.php';

use Vobla\Container,
    Vobla\Configuration,
    Vobla\ServiceLocating\ServiceLocator,
    Vobla\ServiceLocating\CompositeServiceLocator,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class CompositeServiceLocatorTest extends \PHPUnit_Framework_TestCase
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

    protected function createMockContainer($locators)
    {
        $sl = $this->mf->createTestCaseAware(ServiceLocatorsProvider::CLAZZ)
        ->addMethod('getServiceLocators', $locators, 1)
        ->createMock();

        $cfg = $this->mf->createTestCaseAware(Configuration::clazz())
        ->addMethod('getServiceLocatorsProvider', $sl, 1)
        ->createMock();

        $ctr = $this->mf->createTestCaseAware(Container::clazz())
        ->addMethod('getConfiguration', $cfg, 1)
        ->createMock();

        return $ctr;
    }
    
    public function testAnalyze()
    {
        $tc = $this;

        $id = 'fooId';
        $def = new ServiceDefinition();

        $callback = function($self, $argId, $argDef) use($tc, $id, $def) {
            $tc->assertEquals(
                $id,
                $argId,
                'Aggregated by CompositeServiceLocators locators received different id that CSL was provided with.'
            );
            $tc->assertSame(
                $def,
                $argDef,
                'CompositeServiceLocator should pass the same instance of ServiceDefinition to all aggregated locators.'
            );
        };

        $l1 = $this->mf->createTestCaseAware(ServiceLocator::CLAZZ)
        ->addMethod('analyze', $callback, 1)
        ->createMock();
        
        $l2 = $this->mf->createTestCaseAware(ServiceLocator::CLAZZ)
        ->addMethod('analyze', $callback, 1)
        ->createMock();

        $csl = new CompositeServiceLocator();
        $csl->init($this->createMockContainer(array($l1, $l2)));

        $csl->analyze($id, $def);
    }

    public function testLocate()
    {
        $tc = $this;
        
        $criteria = new \stdClass();

        $clk1 = function($self, $argCriteria) use($tc, $criteria) {
            $tc->assertSame(
                $criteria,
                $argCriteria,
                'CompositeServiceLocator should pass the same instance of criteria to all aggregated locators.'
            );

            return array('fooId');
        };
        $clk2 = function($self, $argCriteria) use($tc, $criteria) {
            $tc->assertSame(
                $criteria,
                $argCriteria,
                'CompositeServiceLocator should pass the same instance of criteria to all aggregated locators.'
            );

            return array('barId');
        };

        $l1 = $this->mf->createTestCaseAware(ServiceLocator::CLAZZ)->addMethod('locate', $clk1, 1)->createMock(array(), 'm1');
        $l2 = $this->mf->createTestCaseAware(ServiceLocator::CLAZZ)->addMethod('locate', $clk2, 1)->createMock(array(), 'm2');
        
        $csl = new CompositeServiceLocator();
        $csl->init($this->createMockContainer(array($l1, $l2)));

        $this->assertEquals(
            array('fooId', 'barId'),
            $csl->locate($criteria),
            'CompositeServiceLocator was not able to return proper location-result. It seems that it didn\'t delegate return value of one of aggregated locators.'
        );
    }
}
