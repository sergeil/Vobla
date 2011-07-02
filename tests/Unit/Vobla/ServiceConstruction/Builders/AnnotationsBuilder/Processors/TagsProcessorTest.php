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
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\TagsProcessor,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class TagsProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\TagsProcessor
     */
    protected $tp;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $ar;

    public function setUp()
    {
        $this->tp = new TagsProcessor();
        $this->ar = new AnnotationReader();
    }

    public function tearDown()
    {
        $this->tp = null;
        $this->ar = null;
    }

    public function testHandle()
    {
        $rc = new \ReflectionClass(ClassWithTags::clazz());
        $def = new ServiceDefinition();

        $this->tp->handle($this->ar, $rc, $def);

        $tags = $def->getMetaEntry('tags');
        $this->assertTrue(
            is_array($tags),
            sprintf(
                '"tags" meta property entry must have been initialized after invocation of %s::handle',
                ServiceDefinition::clazz()
            )
        );
        $this->assertEquals(
            array('fooTag', 'barTag'),
            $tags,
            'Tags were not properly initialized'
        );
    }

    public function testHandle_withParent()
    {
        $rc = new \ReflectionClass(AnotherClassWithTags::clazz());
        $def = new ServiceDefinition();

        $this->tp->handle($this->ar, $rc, $def);

        $this->assertEquals(
            array('fooTag', 'barTag', 'foo-fooTag', 'bar-barTag'),
            $def->getMetaEntry('tags'),
            'Tags from parent class seem to be not taken into account'
        );
    }
}
