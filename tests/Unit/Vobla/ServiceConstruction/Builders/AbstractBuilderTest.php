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

namespace Vobla\ServiceConstruction\Builders;

require_once __DIR__.'/../../../../bootstrap.php';

use Vobla\ServiceConstruction\Builders\XmlBuilder\ProcessorsProvider;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class AbstractBuilderTest extends \PHPUnit_Framework_TestCase
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

    public function test__construct()
    {
        $pp = new \stdClass();

        /* @var \Vobla\ServiceConstruction\Builders\AbstractBuilder $ab */
        $ab = $this->mf->createTestCaseAware(AbstractBuilder::clazz(), false)
        ->addDelegateMethod('__construct')
        ->addDelegateMethod('getProcessorsProvider')
        ->addMethod('getDefaultProcessorsProvider', $pp, 3)
        ->createMock();
    }

    public function testGetProcessorsAndGetProcessor()
    {
        $processors = array(new \stdClass(), new \SimpleXMLElement('<x></x>'));

        $pp = $this->mf->createTestCaseAware(ProcessorsProvider::CLAZZ)
        ->addMethod('getProcessors', $processors, 1)
        ->createMock();

        /* @var \Vobla\ServiceConstruction\Builders\AbstractBuilder $ab */
        $ab = $this->mf->createTestCaseAware(AbstractBuilder::clazz(), false)
        ->addDelegateMethod('__construct')
        ->addMethod('getDefaultProcessorsProvider', $pp)
        ->addDelegateMethod('getProcessorsProvider')
        ->addDelegateMethod('getProcessors')
        ->addDelegateMethod('getProcessor')
        ->createMock();

        $this->assertSame($pp, $ab->getProcessorsProvider());
        $cachedProcessors = $ab->getProcessors();
        $this->assertEquals(
            $processors,
            $cachedProcessors,
            sprintf(
                'Cached processors must be absolutely the same as implementation %s::getProcessors returned',
                ProcessorsProvider::CLAZZ
            )
        );

        $this->assertSame($processors[0], $ab->getProcessor('stdClass'));
        $this->assertSame($processors[1], $ab->getProcessor('SimpleXMLElement'));

    }
}
