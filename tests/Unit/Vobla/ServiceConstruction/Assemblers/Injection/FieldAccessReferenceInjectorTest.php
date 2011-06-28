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

namespace Vobla\ServiceConstruction\Assemblers\Injection;

require_once __DIR__.'/../../../../../bootstrap.php';
require_once __DIR__ . '/fixtures/classes.php';

use Vobla\ServiceConstruction\Assemblers\Injection\FieldAccessReferenceInjector,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class FieldAccessReferenceInjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\ServiceConstruction\Assemblers\Injection\FieldAccessReferenceInjector
     */
    protected $farj;

    public function setUp()
    {
        $this->farj = new FieldAccessReferenceInjector();
    }

    public function tearDown()
    {
        $this->farj = null;
    }

    public function testInject()
    {
        $target = new MockInjectionTarget();

        $def = new ServiceDefinition();

        $this->farj->inject($target, 'publicProperty', 'value1', $def);
        $this->assertEquals('value1', $target->getPublicProperty(), 'Injection to public field failed.');

        $this->farj->inject($target, 'protectedProperty', 'value2', $def);
        $this->assertEquals('value2', $target->getProtectedProperty(), 'Injection to protected field failed.');

        $this->farj->inject($target, 'privateProperty', 'value3', $def);
        $this->assertEquals('value3', $target->getPrivateProperty(), 'Injection to private field failed.');
    }
}
