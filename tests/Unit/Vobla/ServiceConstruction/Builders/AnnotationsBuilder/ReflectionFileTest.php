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

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder;

require_once __DIR__.'/../../../../../bootstrap.php';

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ReflectionFileTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClassNameAndGetNamespace_class()
    {
        $pathname = realpath(__DIR__.'/fixtures/GoodClass.php');
        $rf = new ReflectionFile(file_get_contents($pathname));

        $this->assertEquals(
            'GoodClass',
            $rf->getClassName(),
            "Unable to extract class-name from '$pathname' file."
        );
        $this->assertEquals(
            'Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder',
            $rf->getNamespace(),
            "Unable to extract namespace from '$pathname' file"
        );
    }

    public function testGetClassNameAndGetNamespace_interface()
    {
        $pathname = realpath(__DIR__.'/fixtures/GoodInterface.php');
        $rf = new ReflectionFile(file_get_contents($pathname));

        $this->assertEquals(
            'GoodInterface',
            $rf->getClassName(),
            "Unable to extract class-name(interface-name) from '$pathname' file."
        );
        $this->assertEquals(
            'Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder',
            $rf->getNamespace(),
            "Unable to extract namespace from '$pathname' file"
        );
    }
}
