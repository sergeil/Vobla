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

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Assemblers\AssemblersManager,
    Vobla\ServiceConstruction\Assemblers\Assembler,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

require_once __DIR__.'/../../../../bootstrap.php';

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class AssemblersManagerTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @expectedException \Vobla\Exception
     */
    public function test__construct()
    {
        new AssemblersManager(array());
    }

    public function testProceed()
    {
        $tc = $this;
        $definition = new ServiceDefinition();

        $serviceObj = new \stdClass();

        $a1 = $this->mf->createTestCaseAware(Assembler::CLAZZ)->addMethod('execute', function($self, $am, $argDef) use($tc, $definition, $serviceObj) {
            $tc->assertSame($definition, $argDef);

            $serviceObj->foo = true;

            return $am->proceed($argDef, $serviceObj);
        }, 1, 'a1')->createMock();

        $a2 = $this->mf->createTestCaseAware(Assembler::CLAZZ)->addMethod('execute', function($self, $am, $argDef, $argServiceObj) use($tc, $definition, $serviceObj) {
            $tc->assertSame($definition, $argDef);
            $tc->assertSame($argServiceObj, $serviceObj);

            $serviceObj->bar = true;

            return $serviceObj;
        }, 1, 'a2')->createMock();

        $a3 = $this->mf->createTestCaseAware(Assembler::CLAZZ)->addMethod('execute', function() {
        }, 0, 'a3')->createMock();

        $assemblers = array($a1, $a2, $a3);
        $am = new AssemblersManager($assemblers);
        $resultingServiceObj = $am->proceed($definition);

        $this->assertTrue(isset($serviceObj->foo), 'It seems that first assembler was not invoked.');
        $this->assertTrue(isset($serviceObj->bar), 'It seems that second assembler was not invoked.');
        $this->assertSame($resultingServiceObj, $serviceObj, "AssemblerManager didn't return an result of assemblers chain execution. ( assembled service object )");
    }
}
