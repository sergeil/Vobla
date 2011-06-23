<?php

namespace Vobla\ServiceLocating;

require_once __DIR__.'/../../../bootstrap.php';

use Vobla\Container,
    Vobla\Configuration,
    Vobla\ServiceLocating\ServiceLocator,
    Vobla\ServiceLocating\CompositeServiceLocator,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
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
        $sl = $this->mf->createTestCaseAware(ServiceLocatorsProvider::CLAZZ)->addMethod('getServiceLocators', function() use($locators) {
            return $locators;
        }, 1)->createMock();

        $cfg = $this->mf->createTestCaseAware(Configuration::clazz())->addMethod('getServiceLocatorsProvider', function() use($sl) {
            return $sl;
        }, 1)->createMock();

        $ctr = $this->mf->createTestCaseAware(Container::clazz())->addMethod('getConfiguration', function() use($cfg) {
            return $cfg;
        }, 1)->createMock();

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

        $l1 = $this->mf->createTestCaseAware(ServiceLocator::CLAZZ)->addMethod('analyze', $callback, 1)->createMock();
        $l2 = $this->mf->createTestCaseAware(ServiceLocator::CLAZZ)->addMethod('analyze', $callback, 1)->createMock();

        $csl = new CompositeServiceLocator();
        $csl->init($this->createMockContainer(array($l1, $l2)));

        $csl->analyze($id, $def);
    }

    public function testLocate()
    {
        $tc = $this;
        
        $criteria = new \stdClass();

        $clk = function($self, $argCriteria) use($tc, $criteria) {
            $tc->assertSame(
                $criteria,
                $argCriteria,
                'CompositeServiceLocator should pass the same instance of criteria to all aggregated locators.'
            );

            return false;
        };
        $clkReturn = function($self, $argCriteria) use($tc, $criteria) {
            $tc->assertSame(
                $criteria,
                $argCriteria,
                'CompositeServiceLocator should pass the same instance of criteria to all aggregated locators.'
            );

            return 'fooId';
        };

        $l1 = $this->mf->createTestCaseAware(ServiceLocator::CLAZZ)->addMethod('locate', $clk, 1)->createMock(array(), 'm1');
        $l2 = $this->mf->createTestCaseAware(ServiceLocator::CLAZZ)->addMethod('locate', $clkReturn, 1)->createMock(array(), 'm2');
        $l3 = $this->mf->createTestCaseAware(ServiceLocator::CLAZZ)->addMethod('locate', $clk, 0)->createMock(array(), 'm3');

        $csl = new CompositeServiceLocator();
        $csl->init($this->createMockContainer(array($l1, $l2, $l3)));

        $this->assertEquals(
            'fooId',
            $csl->locate($criteria),
            'CompositeServiceLocator was not able to return proper location-result. It seems that it didn\'t delegate return value of one of aggragated locators.'
        );
    }
}
