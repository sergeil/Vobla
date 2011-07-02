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
    Vobla\ServiceConstruction\Definition\References\IdReference,
    Vobla\ServiceConstruction\Definition\References\QualifiedReference,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired,
    Vobla\ServiceConstruction\Builders\InjectorsOrderResolver,
    Vobla\ServiceConstruction\Definition\References\TypeReference,
    Vobla\ServiceConstruction\Definition\References\TagReference,
    Vobla\ServiceConstruction\Definition\References\TagsCollectionReference,
    Vobla\ServiceConstruction\Definition\References\TypeCollectionReference;

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

    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->pp = new PropertiesProcessor();
        $this->ar = new AnnotationReader();
        $this->mf = new \Moko\MockFactory($this);
    }

    public function tearDown()
    {
        $this->pp = null;
        $this->ar = null;
        $this->mf = null;
    }

    public function testHandle()
    {
        $def = new ServiceDefinition();

        $resolved = array();

        $ior = $this->mf->createTestCaseAware(InjectorsOrderResolver::clazz())
        ->addDelegateMethod('setByIdCallback')
        ->addDelegateMethod('setByTagCallback')
        ->addDelegateMethod('setByTypeCallback')
        ->addDelegateMethod('setByQualifierCallback')
        ->addDelegateMethod('getByIdCallback')
        ->addDelegateMethod('getByQualifierCallback')
        ->addDelegateMethod('getByTypeCallback')
        ->addDelegateMethod('getByTagCallback')
        ->addMethod('resolve', function($self) use(&$resolved) {
            /* @var \Vobla\ServiceConstruction\Builders\InjectorsOrderResolver $self */

            $idCb = $self->getByIdCallback(); /* @var  \Closure $idCb */
            $qlrCb = $self->getByQualifierCallback(); /* @var  \Closure $qlrCb */
            $typeCb = $self->getByTypeCallback(); /* @var  \Closure $typeCb */
            $tagCb = $self->getByTagCallback(); /* @var  \Closure $tagCb */

            $resolved[] = array(
                'id' => $idCb instanceof \Closure ? $idCb() : null,
                'qlr' => $qlrCb instanceof \Closure ? $qlrCb() : null,
                'type' => $typeCb(),
                'tag' => $tagCb()
            );
        }, 6)
        ->createMock();

        $this->pp->setInjectorsOrderResolver($ior);
        $this->pp->handle($this->ar, new \ReflectionClass(GeneralizedAutowiringClass::clazz()), $def);
        
        $this->assertEquals(6, sizeof($resolved));
        $expectedKeys = array('id', 'qlr', 'type', 'tag');
        $this->assertEquals(array_keys($resolved[0]), $expectedKeys);
        $this->assertEquals(array_keys($resolved[1]), $expectedKeys);

        $this->doTestResolvedResult($resolved[0], 'bar');
        $this->doTestResolvedResult($resolved[3], 'foo');

        $this->doTestCollectionResult($resolved[1], 'bar', 'set');
        $this->doTestCollectionResult($resolved[2], 'bar', 'map');
        $this->doTestCollectionResult($resolved[4], 'foo', 'set');
        $this->doTestCollectionResult($resolved[5], 'foo', 'map');
    }

    protected function doTestResolvedResult(array $resolvedEntry, $propertyName)
    {
        $this->assertType(IdReference::clazz(), $resolvedEntry['id']);
        $this->assertEquals($propertyName.'Id', $resolvedEntry['id']->getServiceId());
        $this->assertType(QualifiedReference::clazz(), $resolvedEntry['qlr']);
        $this->assertEquals($propertyName.'Qualifier', $resolvedEntry['qlr']->getQualifier());
        $this->assertType(TypeReference::clazz(), $resolvedEntry['type']);
        $this->assertEquals($propertyName.'Type', $resolvedEntry['type']->getType());
        $this->assertType(TagReference::clazz(), $resolvedEntry['tag']);
        $this->assertEquals($propertyName.'Tag', $resolvedEntry['tag']->getTag());
    }

    protected function doTestCollectionResult(array $resolvedEntry, $propertyName, $stereotype)
    {
        $this->assertType(TypeCollectionReference::clazz(), $resolvedEntry['type']);
        $this->assertEquals($propertyName.'Type', $resolvedEntry['type']->getType());
        $this->assertType(TagsCollectionReference::clazz(), $resolvedEntry['tag']);
        $this->assertEquals(
            array($propertyName.'Tag1', $propertyName.'Tag2'),
            $resolvedEntry['tag']->getTags()
        );
    }
}
