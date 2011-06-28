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

use Doctrine\Common\Annotations\AnnotationReader,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Definition\QualifiedReference,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class PropertiesProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\PropertiesProcessor
     */
    protected $pp;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $ar;

    public function setUp()
    {
        $this->pp = new PropertiesProcessor();
        $this->ar = new AnnotationReader();
    }

    public function tearDown()
    {
        $this->pp = null;
        $this->ar = null;
    }

    public function testHandle()
    {
        $def = new ServiceDefinition();

        /* @var \Vobla\ServiceConstruction\Definition\ServiceDefinition $def */
        $result = $this->pp->handle($this->ar, new \ReflectionClass(SomeDumbService::clazz()), $def);
        $args = $def->getArguments();
        $this->assertTrue(is_array($args));

        if (sizeof($args) == 3) {
            $this->fail('It seems that annotations from parent class were not taken into account.');
        } else if (sizeof($args) == 5) {
            $this->fail(
                'It looks that annotations from a parent class were taken into account nevertheless the fact it doesn\'t have Service annotation.'
            );
        } else {
            $this->assertEquals(4, sizeof($args), 'Declared service references were not collected properly.');
        }

        $this->assertTrue(isset($args['ref1x']));
        $this->assertType(ServiceReference::clazz(), $args['ref1x']);
        $this->assertEquals('ref1x', $args['ref1x']->getServiceId());

        $this->assertTrue(isset($args['ref2x']));
        $this->assertType(ServiceReference::clazz(), $args['ref2x']);
        $this->assertEquals('barbaz', $args['ref2x']->getServiceId());

        $this->assertTrue(isset($args['ref3x']));
        $this->assertType(
            QualifiedReference::clazz(),
            $args['ref3x'],
            sprintf(
                'When parameter "qualifier" of annotation "%s" is provided an instance of "%s" must be injected in "%s".',
                Autowired::clazz(), QualifiedReference::clazz(), ServiceDefinition::clazz()
            )
        );
        $this->assertEquals('booz', $args['ref3x']->getQualifier());
    }
}
