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

namespace Vobla\ServiceLocating\DefaultImpls;

require_once __DIR__.'/fixtures/classes.php';
require_once __DIR__.'/../../../../bootstrap.php';

use Vobla\ServiceLocating\DefaultImpls\TypeServiceLocator as TSL,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class TypeServiceLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    /**
     * @var \Vobla\ServiceLocating\DefaultImpls\TypeServiceLocator
     */
    protected $locator;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
        $this->locator = new TSL();
    }

    public function tearDown()
    {
        $this->mf = null;
        $this->locator = null;
    }

    public function testCreateCriteria()
    {
        $this->assertEquals('byType:foo', TSL::createCriteria('foo'));
    }

    public function testAnalyzeAndLocate()
    {
        $def = $this->mf->createTestCaseAware(ServiceDefinition::clazz())
        ->addMethod('getClassName', function($self) {
            return FirstClass::clazz();
        }, 1)
        ->createMock();

        $this->locator->analyze('fullyLoadedService', $def);

        $this->assertEquals(
            array('fullyLoadedService'),
            $this->locator->locate(TSL::createCriteria(FirstClass::clazz()))
        );

        $def2 = $this->mf->createTestCaseAware(ServiceDefinition::clazz())
        ->addMethod('getClassName', function($self) {
            return SecondClass::clazz();
        }, 1)
        ->createMock();

        $this->locator->analyze('fullyLoadedService2', $def2);

        $this->assertEquals(
            array('fullyLoadedService', 'fullyLoadedService2'),
            $this->locator->locate(TSL::createCriteria(FirstClass::clazz()))
        );
    }
}