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
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\TypeProcessor,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\NotByTypeWiringCandidate;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class TypeProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\TypeProcessor
     */
    protected $tp;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $ar;

    public function setUp()
    {
        $this->tp = new TypeProcessor();
        $this->ar = new AnnotationReader();
    }

    public function tearDown()
    {
        $this->tp = null;
        $this->ar = null;
    }

    public function testHandle()
    {
        $def = new ServiceDefinition();

        $this->tp->handle($this->ar, new \ReflectionClass(ClassWithNotByTypeWiringCandidateAnnotation::clazz()), $def);

        $this->assertTrue(
            $def->getMetaEntry('notByTypeWiringCandidate'),
            sprintf(
                "If a class is annotated with '%s' then its meta-property 'notByTypeWiringCandidate' should initialized to TRUE.",
                NotByTypeWiringCandidate::clazz()
            )
        );
    }

    public function testHandle_wihoutAnnotation()
    {
        $def = new ServiceDefinition();

        $this->tp->handle($this->ar, new \ReflectionClass(ClassWithoutNotByTypeWiringCandidate::clazz()), $def);

        $this->assertNull($def->getMetaEntry('notByTypeWiringCandidate'));
    }

}
