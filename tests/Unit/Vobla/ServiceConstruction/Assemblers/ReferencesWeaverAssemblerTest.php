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

use Vobla\ServiceConstruction\Assemblers\ReferencesWeaverAssembler,
    Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Assemblers\AssemblersManager,
    Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Definition\QualifiedReference;

require_once 'fixtures/classes.php';
require_once __DIR__.'/../../../../bootstrap.php';

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ReferencesWeaverAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
    }

    public function testExecute()
    {
        $tc = $this;

        $serviceObj = new \stdClass();

        $def = new ServiceDefinition();
        $def->setArguments(array(
            'foo' => new ServiceReference('fooService'),
            'bar' => new QualifiedReference('barQualifier')
        ));

        $injectMethod = function($self, $obj, $paramName, $paramValue, $def) use($tc) {
            $args = $def->getArguments();
            
            $tc->assertTrue(isset($args[$paramName]), 'Expected parameter was not injected.');

            if ($paramName == 'foo') {
                $tc->assertEquals('resolvedByIdService', $paramValue);
            } else if ($paramName == 'bar') {
                $tc->assertEquals('resolvedByQualifierService', $paramValue);
            } else {
                $tc->fail('Injector should have been used only for injection of "foo", "bar" parameters.');
            }
        };
        $ri = $this->mf->createTestCaseAware(ReferenceInjector::CLAZZ)->addMethod('inject', $injectMethod, 2)->createMock();

        $proceedMethod = function($self, $am, $obj) use($tc, $serviceObj) {
            $tc->assertSame($serviceObj, $obj);
        };
        $ma = $this->mf->create(AssemblersManager::clazz(), true)->addMethod('proceed', $proceedMethod, 1)->createMock();

        $c = $this->mf->createTestCaseAware(Container::clazz())
        ->addMethod('getServiceById', function($self, $id) use($tc) {
            $tc->assertEquals(
                'fooService',
                $id,
                'Resolving by id should have been done only for service with id "fooService".'
            );

            return 'resolvedByIdService';
        }, 1)
        ->addMethod('getServiceByQualifier', function($self, $qualifier) use($tc) {
            $tc->assertEquals(
                'barQualifier',
                $qualifier,
                'Resolving by qualifier should have been done only for service with qualifier "barQualifier"'
            );

            return 'resolvedByQualifierService';
        }, 1)->createMock();

        $rwa = new ReferencesWeaverAssembler($ri);
        $rwa->init($c);
        $rwa->execute($ma, $def, $serviceObj);
    }
}
