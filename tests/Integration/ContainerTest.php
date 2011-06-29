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

namespace Vobla;

require_once __DIR__.'/../bootstrap.php';

require_once __DIR__ . '/fixtures/Foo.php';
require_once __DIR__ . '/fixtures/Bar.php';
require_once __DIR__ . '/fixtures/FooBar.php';

use Vobla\Container,
    Vobla\Configuration,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\ServiceConstruction\Builders\XmlBuilder\XmlBuilder,
    Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import\ImportProcessor,
    Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import\StaticPathResolver;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testItWithAnnotations()
    {
        $container = new Container(new Configuration());

        $ab = new AnnotationsBuilder();
        $ab->processPath($container, __DIR__.'/fixtures');

        $this->tc($container);
    }

    public function testItWithXmls()
    {
        $container = new Container(new Configuration());

        $xb = new XmlBuilder();

        foreach ($xb->getProcessors() as $p) {
            if ($p instanceof ImportProcessor) {
                /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import\ImportProcessor $p */
                $p->setPathResolver(new StaticPathResolver(__DIR__.'/fixtures/context/'));
            }
        }
        
        $xb->processXml(file_get_contents(__DIR__.'/fixtures/context/a.xml'), $container);

        $this->tc($container);
    }

    protected function tc(Container $container)
    {
        /* @var FooService $fooService */
        $fooService = $container->getServiceById('fooService');
        $this->assertType('Foo', $fooService);
        $this->assertType('Bar', $fooService->bar);
        $this->assertType('FooBar', $fooService->foobar);
    }
}
