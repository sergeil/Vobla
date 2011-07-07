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

namespace Vobla\ServiceConstruction\Builders;

require_once __DIR__.'/../../../../bootstrap.php';

use Vobla\ServiceConstruction\Builders\XmlBuilder\ProcessorsProvider;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class InjectorsOrderResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Vobla\ServiceConstruction\Builders\InjectorsOrderResolver
     */
    protected $ior;

    public function setUp()
    {
        $this->ior = new InjectorsOrderResolver();
    }

    public function tearDown()
    {
        $this->ior = null;
    }

    protected function createCallback($name, &$resolvedOrder)
    {
        return function() use ($name, &$resolvedOrder) {
            $resolvedOrder[] = $name;
        };
    }

    public function testResolve()
    {
        $resolvedOrder = array();

        $this->ior->setByQualifierCallback($this->createCallback('qualifier', $resolvedOrder));
        $this->ior->setByTagCallback($this->createCallback('tag', $resolvedOrder));
        $this->ior->setByTypeCallback($this->createCallback('type', $resolvedOrder));
        $this->ior->setByIdCallback($this->createCallback('id', $resolvedOrder));

        $this->ior->resolve();

        $this->assertEquals(
            array('qualifier', 'tag', 'type', 'id'),
            $resolvedOrder,
            'Callbacks were invoked in wrong order.'
        );
    }
}
