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

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors;

require_once __DIR__.'/../../../../../../bootstrap.php';
require_once __DIR__.'/fixtures/classes.php';

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\ConfigProcessor,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\ServiceConstruction\Definition\References\ConfigPropertyReference;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ConfigProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\ConfigProcessor
     */
    protected $cp;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $ar;

    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
        $this->cp = new ConfigProcessor();
        $this->ar = new AnnotationReader();
    }

    public function tearDown()
    {
        $this->cp = null;
        $this->ar = null;
        $this->mf = null;
    }

    public function testHandle()
    {
        $tc = $this;
        $def = $this->mf->createTestCaseAware(ServiceDefinition::clazz())
        ->addMethod('setArguments', function($self, $args) use($tc) {
            $tc->assertEquals(
                3,
                sizeof($args),
                sprintf('Only two properties must have been injected into %s::arguments', ServiceDefinition::clazz())
            );
            $tc->assertTrue(isset($args['somePreviousProp']));

            $tc->assertTrue(
                isset($args['fooField']),
                'Resulting arguments must contain "fooField" element.'
            );
            $tc->assertType(ConfigPropertyReference::clazz(), $args['fooField']);
            $tc->assertEquals('fooProp', $args['fooField']->getName());

            $tc->assertTrue(
                isset($args['barField']),
                'Resulting arguments must contain "barField" element.'
            );
            $tc->assertType(ConfigPropertyReference::clazz(), $args['barField']);
            $tc->assertEquals('barProp', $args['barField']->getName());
        }, 1)
        ->addMethod('getArguments', array('somePreviousProp' => 'somePreviousPropValue'))
        ->createMock();

        $this->cp->handle($this->ar, new \ReflectionClass(ClassWithSomeConfig::clazz()), $def);
    }
}
