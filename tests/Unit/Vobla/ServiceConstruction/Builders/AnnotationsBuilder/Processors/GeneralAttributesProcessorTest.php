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

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\GeneralAttributesProcessor,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class GeneralAttributesProcessorTest extends AbstractTest
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\GeneralAttributesProcessor
     */
    protected $gap;

    public function doSetUp()
    {
        $this->gap = new GeneralAttributesProcessor();
    }

    public function doTearDown()
    {
        $this->gap = null;
    }

    public function testHandle()
    {
        $def = new ServiceDefinition();
        $this->gap->handle(new \ReflectionClass(ClassWithAllGeneralProperties::clazz()), $def, $this->ab);

        $this->assertEquals(
            ClassWithAllGeneralProperties::clazz(),
            $def->getClassName(),
            "Class name doesn't match."
        );
        $this->assertEquals(
            'someQualifier',
            $def->getQualifier(),
            "Qualifier value doesn't match."
        );
        $this->assertFalse(
            $def->isAbstract(),
            sprintf("Even if %s::isScope is string it should be properly casted to bool value!", Service::clazz())
        );
        $this->assertEquals(
            'fooScope',
            $def->getScope(),
            "Scope doesn't match."
        );
    }
}
