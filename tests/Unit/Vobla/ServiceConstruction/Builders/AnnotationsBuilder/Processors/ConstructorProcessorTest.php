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
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\ConstructorProcessor,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor,
    Vobla\ServiceConstruction\Definition\References\QualifiedReference,
    Vobla\ServiceConstruction\Definition\References\IdReference,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Parameter;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ConstructorProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $ar;

    /**
     * @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\ConstructorProcessor
     */
    protected $cp;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
        $this->ar = new AnnotationReader();
        $this->cp = new ConstructorProcessor();
    }

    public function tearDown()
    {
        $this->mf = null;
        $this->ar = null;
        $this->cp = null;
    }

    /**
     * @expectedException \Vobla\Exception
     */
    public function testHandle_withTwoConstructors()
    {
        $reflClass = new \ReflectionClass(ClassWithTwoConstructors::clazz());
        $this->cp->handle($this->ar, $reflClass, new ServiceDefinition());
    }

    public function testHandle_withLocalFactoryMethodAndParameters()
    {
        $reflClass = new \ReflectionClass(ClassWithLocalFactoryMethod::clazz());
        $def = new ServiceDefinition();
        $this->cp->handle($this->ar, $reflClass, $def);

        $this->assertEquals(
            'fooFactory',
            $def->getFactoryMethod(),
            sprintf(
                '%s::fooFactory method should have been used as constructor because there\'s %s annotation',
                ClassWithLocalFactoryMethod::clazz(), Constructor::clazz()
            )
        );

        $args = $def->getConstructorArguments();
        $this->assertEquals(
            3,
            sizeof($args),
            sprintf(
                "%s::fooFactory's %s annotation defines two parameters but since the method has 3 parameters, third parameter should be deduced from method's signature",
                ClassWithLocalFactoryMethod::clazz(), Constructor::clazz()
            )
        );

        /* @var \Vobla\ServiceConstruction\Definition\References\QualifiedReference $param1 */
        $param1 = $args[0];
        $this->assertType(
            QualifiedReference::clazz(), $param1,
            sprintf(
                "When %s::params array contains a %s with qualifier key != null, an instance of %s must be created",
                 Constructor::clazz(), Parameter::clazz(), QualifiedReference::clazz()
            )
        );
        $this->assertEquals('fooQfr', $param1->getQualifier());

        /* @var \IdReference\ServiceConstruction\Definition\ServiceReference $param2 */
        $param2 = $args[1];
        $this->assertType(
            IdReference::clazz(),
            $param2,
            sprintf(
                "If no %s defined for a method's parameter then an instance of %s must be created for a service with the same name as the parameter has",
                Parameter::clazz(), IdReference::clazz()
            )
        );
        $this->assertEquals(
            'bService',
            $param2->getServiceId(),
            sprintf(
                "ID of automatically created %s for constructor's parameter must be the same as the parameter name",
                IdReference::clazz()
            )
        );

        $param3 = $args[2];
        $this->assertType(
            IdReference::clazz(),
            $param3,
            sprintf(
                "Whenever there's ID is specified for %s an instance of %s must be created.",
                Parameter::clazz(), IdReference::clazz()
            )
        );
    }
}
