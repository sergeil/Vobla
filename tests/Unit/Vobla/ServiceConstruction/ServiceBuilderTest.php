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

namespace Vobla\ServiceConstruction;

use Vobla\Container,
    Vobla\ServiceConstruction\Assemblers\AssemblersProvider,
    \Vobla\ServiceConstruction\ServiceBuilder,
    \Vobla\ServiceConstruction\Definition\ServiceDefinition,
    \Vobla\Configuration,
    \Vobla\ServiceConstruction\Assemblers\Assembler;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ServiceBuilderTest extends \PHPUnit_Framework_TestCase
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

    public function testProcess()
    {
        $tc = $this;
        $def = new ServiceDefinition();

        $asmb = $this->mf->createTestCaseAware(Assembler::CLAZZ)->addMethod('execute', function($self, $am, $argDef) use($tc, $def) {
            $tc->assertSame($def, $argDef);
        }, 1)->createMock();

        $ap = $this->mf->createTestCaseAware(AssemblersProvider::CLAZZ)->addMethod('getAssemblers', function() use ($asmb) {
            return array(
                $asmb
            );
        }, 1)->createMock();

        $cfg = $this->mf->createTestCaseAware(Configuration::clazz())->addMethod('getAssemblersProvider', function() use($ap) {
            return $ap;
        }, 1)->createMock();

        $ctr = $this->mf->createTestCaseAware(Container::clazz())->addMethod('getConfiguration', function() use($cfg) {
            return $cfg;
        }, 1)->createMock();

        $sb = new ServiceBuilder();
        $sb->init($ctr);

        $this->assertNull($sb->process($def));
    }
}
