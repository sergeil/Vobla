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

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Assemblers\ReferencesWeaverAssembler,
    Vobla\ServiceConstruction\Assemblers\Injection\ReferenceInjector,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Assemblers\AssemblersManager,
    Vobla\Container,
    Vobla\ServiceConstruction\Definition\References\IdReference,
    Vobla\ServiceConstruction\Definition\References\QualifiedReference,
    Vobla\ServiceConstruction\Definition\References\TagReference,
    Vobla\ServiceLocating\ServiceLocator,
    Vobla\ServiceConstruction\Definition\References\TagsCollectionReference,
    Vobla\ServiceConstruction\Definition\References\TypeCollectionReference,
    Vobla\ServiceLocating\DefaultImpls\TagServiceLocator;

require_once 'fixtures/classes.php';
require_once __DIR__.'/../../../../bootstrap.php';

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ReferencesWeaverAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
    }

    public function testExecute()
    {
        $tc = $this;

        $serviceObj = new \stdClass();

        $def = $this->mf->createTestCaseAware(ServiceDefinition::clazz())
        ->addMethod('getArguments', function() {
            return array(
                'idProperty' => new IdReference('fooId'),
                'qlrProperty' => new QualifiedReference('fooQlr'),
                'tagReferenceProperty' => new TagReference('fooTag1'),
                'tagsCollectionSet' => new TagsCollectionReference(array('fooTag1', 'barTag1'), 'set'),
                'tagsCollectionMap' => new TagsCollectionReference(array('fooTag2', 'barTag2'), 'map'),
//                'typeCollectionSet' => new TypeCollectionReference('megaType', 'set'),
//                'typeCollectionMap' => new TypeCollectionReference('megaType', 'map')
            );
        })
        ->createMock();

        $injectMethod = function($self, $obj, $paramName, $paramValue, $def) use($tc) {
            $args = $def->getArguments();
            
            $tc->assertTrue(isset($args[$paramName]), 'Expected parameter was not injected.');

            $rvs = array(
                'idProperty' => 'resolvedIdReferenceProperty',
                'qlrProperty' => 'qlrPropertyService',
                'tagReferenceProperty' => 'resolvedIdForFooTag1Value',
                'tagsCollectionSet' => array('resolvedIdForFooTag1Value', 'resolvedIdForBarTag1Value'),
                'tagsCollectionMap' => array('resolvedIdForFooTag2' => 'resolvedIdForFooTag2Value', 'resolvedIdForBarTag2' => 'resolvedIdForBarTag2Value'),
//                'resolvedIdForFooTag2' => 'resolvedIdForFooTag2Value',
//                'tagsCollectionSet' => array('resolvedBazService', 'resolvedBazService'),
//                'tagsCollectionMap' => 'resolvedTagsCollectionMap',
//                'typeCollectionSet' => 'resolvedTypeCollectionSet',
//                'typeCollectionMap' => 'resolvedTypeCollectionMap'
            );

            $tc->assertTrue(
                isset($rvs[$paramName]),
                sprintf(
                    'Injector should have been used only for injection of %s parameters, but was used for "%s" as well.',
                    implode(', ', array_keys($rvs)), $paramName
                )
            );

            $tc->assertEquals($paramValue, $rvs[$paramName]);
        };
        $ri = $this->mf->createTestCaseAware(ReferenceInjector::CLAZZ)->addMethod('inject', $injectMethod, 5)->createMock();

        $proceedMethod = function($self, $am, $obj) use($tc, $serviceObj) {
            $tc->assertSame($serviceObj, $obj);
        };

        $ma = $this->mf->create(AssemblersManager::clazz(), true)->addMethod('proceed', $proceedMethod, 1)->createMock();

        $serviceLocator = $this->mf->createTestCaseAware(ServiceLocator::CLAZZ)
        ->addMethod('locate', function($self, $c) {
            $map = array(
                TagServiceLocator::createCriteria('fooTag1') => array('resolvedIdForFooTag1'),
                TagServiceLocator::createCriteria('fooTag2') => array('resolvedIdForFooTag2'),
                TagServiceLocator::createCriteria('barTag1') => array('resolvedIdForBarTag1'),
                TagServiceLocator::createCriteria('barTag2') => array('resolvedIdForBarTag2')
            );

            return $map[$c];
        }, 5)
        ->createMock();

        $c = $this->mf->createTestCaseAware(Container::clazz())
        ->addMethod('getServiceById', function($self, $id) use($tc) {
            $map = array(
                'fooId' => 'resolvedIdReferenceProperty',
                'fooQlrPropertyIdResolvingResult' => 'qlrPropertyService',
                'resolvedIdForFooTag1' => 'resolvedIdForFooTag1Value',
                'resolvedIdForBarTag1' => 'resolvedIdForBarTag1Value',
                'resolvedIdForFooTag2' => 'resolvedIdForFooTag2Value',
                'resolvedIdForBarTag2' => 'resolvedIdForBarTag2Value'
            );

            $tc->assertTrue(
                isset($map[$id]),
                "Unexpected invocation of 'getServiceById' with id '$id'"
            );

            return $map[$id];
        }, 6)
        ->addMethod('getServiceByQualifier', function($self, $qualifier) use($tc) {
            $tc->assertEquals(
                'fooQlr',
                $qualifier,
                'Resolving by qualifier should have been done only for service with qualifier "fooQlr"'
            );

            return 'qlrPropertyService';
        }, 1)
        ->addMethod('getServiceLocator', $serviceLocator, 0)
        ->createMock();
        
        $rwa = new ReferencesWeaverAssembler($ri);
        $rwa->init($c);

        try {
            $rwa->execute($ma, $def, $serviceObj);
        } catch (\Exception $e) {
            \Vobla\Tools\Toolkit::printException($e);
        }
    }
}
