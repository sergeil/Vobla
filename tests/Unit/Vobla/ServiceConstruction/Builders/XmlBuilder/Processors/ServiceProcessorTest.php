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

namespace Vobla\ServiceConstruction\Builders\XmlBuilder\Processors;

require_once __DIR__.'/../../../../../../bootstrap.php';

use Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Definition\References\IdReference,
    Vobla\ServiceConstruction\Builders\XmlBuilder\XmlBuilder,
    Vobla\ServiceConstruction\Definition\References\QualifiedReference,
    Vobla\ServiceConstruction\Builders\ServiceIdGenerator,
    Vobla\ServiceConstruction\Definition\References\TagsCollectionReference,
    Vobla\ServiceConstruction\Definition\References\TagReference,
    Vobla\ServiceConstruction\Definition\References\TypeReference,
    Vobla\ServiceConstruction\Definition\References\TypeCollectionReference,
    Vobla\ConfigHolder,
    Vobla\ServiceConstruction\Definition\References\ConfigPropertyReference;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class ServiceProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    /**
     * @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor
     */
    protected $sp;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
        $this->sp = new ServiceProcessor();
    }

    public function tearDown()
    {
        $this->mf = null;
        $this->sp = null;
    }

    public function testParseRef()
    {
        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns="http://vobla-project.org/xsd/context"
 xmlns:foo="fooNs">
    <ref id="fooId"></ref>
    <ref qualifier="fooQc"></ref>

    <ref tag="fooTag" is-optional="false"/>
    <ref tags-set="fooTag1, barTag1" is-optional="true" />
    <ref tags-map="fooTag2, barTag2" />

    <ref type="fooType" />
    <ref type-set="fooType" is-optional="true" />
    <ref type-map="fooType" />
</context>
XML;

        $xmlContext = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $xmlChildren = $xmlContext->children();
        list($ref1Xml, $ref2Xml, $ref3Xml, $ref4Xml, $ref5Xml, $ref6Xml, $ref7Xml, $ref8Xml) = $xmlChildren;

        /* @var \IdReference\ServiceConstruction\Definition\ServiceReference $result */
        $result = $this->sp->parseRef($ref1Xml);
        $this->assertType(
            IdReference::clazz(),
            $result,
            sprintf(
                '%s::parseRef must return an instance of %s when ID attribute is present',
                ServiceProcessor::clazz(), IdReference::clazz()
            )
        );
        $this->assertEquals(
            $result->getServiceId(),
            'fooId',
            "IdReference ID doesn't match"
        );

        /* @var \Vobla\ServiceConstruction\Definition\References\QualifiedReference $result */
        $result = $this->sp->parseRef($ref2Xml);
        $this->assertType(
            QualifiedReference::clazz(),
            $result,
            sprintf(
                "%s::parseRef must return an instance of %s when 'qualifier' attribute is provided",
                ServiceProcessor::clazz(), QualifiedReference::clazz()
            )
        );
        $this->assertEquals(
            'fooQc',
            $result->getQualifier(),
            "Qualifier value doesn't match"
        );

        /* @var \Vobla\ServiceConstruction\Definition\References\TagReference $result */
        $result = $this->sp->parseRef($ref3Xml);
        $this->assertType(
            TagReference::clazz(),
            $result,
            sprintf(
                '%s::parseRef must return an instance of %s when "tag" attribute is provided',
                ServiceProcessor::clazz(), TagReference::clazz()
            )
        );
        $this->assertEquals(
            'fooTag',
            $result->getTag(),
            "'Tag' value doesn't match."
        );
        $this->assertFalse($result->isOptional());

        /* @var \Vobla\ServiceConstruction\Definition\References\TagsCollectionReference $result */
        $result = $this->sp->parseRef($ref4Xml);
        $this->assertType(
            TagsCollectionReference::clazz(),
            $result,
            sprintf(
                '%s::parseRef must return an instance of %s when "tags-set" attribute is provided ( stereotype = set )',
                ServiceProcessor::clazz(), TagsCollectionReference::clazz()
            )
        );
        $this->assertEquals(
            array('fooTag1', 'barTag1'),
            $result->getTags()
        );
        $this->assertEquals(
            'set',
            $result->getStereotype()
        );
        $this->assertTrue($result->isOptional());

        /* @var \Vobla\ServiceConstruction\Definition\References\TagsCollectionReference $result */
        $result = $this->sp->parseRef($ref5Xml);
        $this->assertType(
            TagsCollectionReference::clazz(),
            $result,
            sprintf(
                '%s::parseRef must return an instance of %s when "tags-map" attribute is provided ( stereotype = map )',
                ServiceProcessor::clazz(), TagsCollectionReference::clazz()
            )
        );
        $this->assertEquals(
            array('fooTag2', 'barTag2'),
            $result->getTags()
        );
        $this->assertEquals(
            'map',
            $result->getStereotype()
        );
        $this->assertTrue($result->isOptional());


        /* @var \Vobla\ServiceConstruction\Definition\References\TypeReference $result */
        $result = $this->sp->parseRef($ref6Xml);
        $this->assertType(
            TypeReference::clazz(),
            $result,
            sprintf(
                '%s::parseRef must return an instance of %s when "type" attribute is provided',
                TypeReference::clazz(), TagReference::clazz()
            )
        );
        $this->assertEquals(
            'fooType',
            $result->getType(),
            "'type' value doesn't match."
        );
        $this->assertTrue($result->isOptional());

        /* @var \Vobla\ServiceConstruction\Definition\References\TypeCollectionReference $result */
        $result = $this->sp->parseRef($ref7Xml);
        $this->assertType(
            TypeCollectionReference::clazz(),
            $result,
            sprintf(
                '%s::TypeCollectionReference must return an instance of %s when "type-set" attribute is provided ( stereotype = set )',
                ServiceProcessor::clazz(), TypeCollectionReference::clazz()
            )
        );
        $this->assertEquals(
            'fooType',
            $result->getType()
        );
        $this->assertEquals(
            'set',
            $result->getStereotype()
        );
        $this->assertTrue($result->isOptional());

        /* @var \Vobla\ServiceConstruction\Definition\References\TypeCollectionReference $result */
        $result = $this->sp->parseRef($ref8Xml);
        $this->assertType(
            TypeCollectionReference::clazz(),
            $result,
            sprintf(
                '%s::parseRef must return an instance of %s when "type-map" attribute is provided ( stereotype = map )',
                ServiceProcessor::clazz(), TypeCollectionReference::clazz()
            )
        );
        $this->assertEquals(
            'fooType',
            $result->getType()
        );
        $this->assertEquals(
            'map',
            $result->getStereotype()
        );
        $this->assertTrue($result->isOptional());
    }

    public function testParseArrayElRef()
    {
        $tc = $this;

        $xmlEl = new \SimpleXMLElement('<x></x>');

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseRef', function($self, $argXmlEl) use($tc, $xmlEl) {
            $tc->assertSame($xmlEl, $argXmlEl);
        }, 1)
        ->addDelegateMethod('parseArrayElRef', 1)
        ->createMock();

        $sp->parseArrayElRef($xmlEl);
    }

    public function testParseArrayEl()
    {
        $tc = $this;

        $xml = <<<XML
        <context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">
            <el index="assoc">
                <el>
                    <el index="sa1" value="sav1" />
                    <el index="sa2">
                        sav2
                    </el>
                    <el index="sa3">
                        <service />
                        <el index="sa3Sub">
                            <el>sa3SubSubValue</el>
                        </el>
                        <ref id="anId" />
                    </el>
                </el>
                <el>rootValue2</el>
                <el>rootValue3</el>
            </el>
        </context>
XML;

        $xmlContext = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $xmlContextChildren = $xmlContext->children();
        $xmlEl = $xmlContextChildren[0];

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseArrayElRef', function($self, $xmlEl) use($tc) {
            $xmlAttrs = $xmlEl->attributes();
            $tc->assertTrue(
                isset($xmlAttrs['id']),
                sprintf(
                    "Method %s::parseArrayElRef must be invoked only once and it must contain ID attribute",
                    ServiceProcessor::clazz()
                )
            );
            $tc->assertEquals(
                'anId',
                (string)$xmlAttrs['id'],
                sprintf(
                    "Method %s::parseArrayElRef was invoked with a <ref> that has wrong ID attribute",
                    ServiceProcessor::clazz()
                )
            );
            return 'foo-ref';
        }, 1)
        ->addMethod('parseArrayElService', function($self, $xmlEl) use($tc) {
            return 'foo-anonym-service';
        }, 1)
        ->addDelegateMethod('parseArrayEl', 9)
        ->createMock();

        $result = $sp->parseArrayEl($xmlEl);

        $this->assertTrue(
            is_array($result),
            sprintf("%s::parseArrayEl must return an array", ServiceProcessor::clazz())
        );
        $this->assertTrue(
            isset($result['assoc']),
            "There's not 'assoc' element in /"
        );

        $rAssoc = $result['assoc'];
        $this->assertTrue(
            is_array($rAssoc),
            '/assoc must be an array'
        );
        $this->assertEquals(
            3,
            sizeof($rAssoc),
            '/assos must be an array with 3 elements'
        );
        $this->assertEquals(
            'rootValue2',
            $rAssoc[1],
            "/1 value doesn't match"
        );
        $this->assertEquals(
            'rootValue3',
            $rAssoc[2],
            "/2 value doesn't match"
        );

        $rAssoc0 = $rAssoc[0];

        $this->assertTrue(
            isset($rAssoc0['sa1']),
            'Unable to find /assoc/0/sa1'
        );
        $this->assertEquals(
            'sav1',
            $rAssoc0['sa1'],
            "/assoc/0/sa1 value doesn't match"
        );

        $this->assertTrue(
            isset($rAssoc0['sa2']),
            'Unable to find /assoc/0/sa2'
        );
        $this->assertTrue(
            strpos($rAssoc0['sa2'], 'sav2') !== false,
            "/assoc/0/sa2 value doesn't match"
        );

        $this->assertTrue(
            is_array($rAssoc0['sa3']),
            '/assoc/0/sa3 must be an array'
        );

        $rAssoc0sa3 = $rAssoc0['sa3'];
        $this->assertEquals(
            3,
            sizeof($rAssoc0sa3),
            '/assoc/0/sa3 array must contain only 3 elements'
        );
        $this->assertEquals(
            'foo-anonym-service',
            $rAssoc0sa3[0],
            sprintf('/assoc/0/sa3/0 must be a resolved service')
        );
        $this->assertEquals(
            'foo-ref',
            $rAssoc0sa3[1],
            sprintf('/assoc/0/sa3/1 must be a resolved service')
        );
        $this->assertTrue(
            isset($rAssoc0sa3['sa3Sub']),
            'Unable to find element /assoc/0/sa3/sa3sub'
        );
        $this->assertTrue(
            is_array($rAssoc0sa3['sa3Sub']),
            "/assoc/0/sa3/sa3sub must be an array"
        );
        $this->assertEquals(
            'sa3SubSubValue',
            $rAssoc0sa3['sa3Sub'][0],
            "/assoc/0/sa3/sa3sub/0 value doesn't match"
        );
    }

    public function testParseArray()
    {
        $xml = <<<XML
        <context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		         xmlns="http://vobla-project.org/xsd/context"
		         xmlns:foo="fooNs">
            <array>
                <el index="fooIndex1" value="fooValue1" />
                <el>
                    <service />
                </el>
                <el>
                    <ref />
                </el>
            </array>
        </context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $contextChildrenXml = $xmlEl->children();
        $arrayXml = $contextChildrenXml[0];

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseArrayEl', function() {
            return array('foo');
        }, 3)
        ->addDelegateMethod('parseArray', 1)
        ->createMock();

        $result = $sp->parseArray($arrayXml);
        $this->assertTrue(
            is_array($result),
            sprintf("%s::parseArray must return an array", ServiceProcessor::clazz())
        );
        $this->assertEquals(
            3,
            sizeof($result),
            'Resulting array must contain 3 root elements.'
        );
//        $this->assertTrue(
//            isset($result['fooIndex1']),
//            'Resulting array must contain an element with "fooIndex1" index.'
//        );
    }

    public function testParseServicePropertiesPropertyTag()
    {
        $xml = <<<XML
        <context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">

    <service>
        <parameters>
            <property name="scalarProperty" value="fooValue" />
            <property name="trueBoolProperty" value="true" type="bool" />
            <property name="falseBoolProperty" value="false" type="bool" />
            <property name="refProperty" ref="anotherService" />
            <property name="inlineProp">
                someInlineValue
            </property>
            <property name="assocProp">
                <array>
                    <el name="someRef">
                        <ref id="someReferencedId" />
                    </el>
                    <el name="subAssoc">
                        <el>
                            <el name="sa1" value="sav1" />
                            <el name="sa2" value="sav2" />
                        </el>
                    </el>
                </array>
            </property>
            <property name="inlineCastProp" type="bool">true</property>
        </parameters>
    </service>
</context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $contextChildrenXml = $xmlEl->children();
        $serviceChildrenXml = $contextChildrenXml->children();
        $serviceParametersXml = $serviceChildrenXml[0]->children();

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addDelegateMethod('parseServicePropertiesPropertyTag', 7)
        ->addDelegateMethod('castServicePropertiesPropertyTagValue', 3)
        ->addMethod('parseServicePropertiesPropertyChildTag', function() {
            return 'assoc-value';
        }, 1)
        ->createMock();

        // scalarProperty
        $result = $sp->parseServicePropertiesPropertyTag($serviceParametersXml[0]);
        $this->assertEquals(
            'fooValue',
            $result,
            $this->createWrongParametersPropertyValue('scalarProperty')
        );

        // trueBoolProperty
        $result = $sp->parseServicePropertiesPropertyTag($serviceParametersXml[1]);
        $this->assertTrue(
            $result,
            $this->createWrongParametersPropertyValue('trueBoolProperty')
        );

        // falseBoolProperty
        $result = $sp->parseServicePropertiesPropertyTag($serviceParametersXml[2]);
        $this->assertFalse(
            $result,
            $this->createWrongParametersPropertyValue('falseBoolProperty')
        );

        // refProperty
        $result = $sp->parseServicePropertiesPropertyTag($serviceParametersXml[3]);
        $this->assertType(
            IdReference::clazz(),
            $result,
            $this->createWrongParametersPropertyValue('refProperty')
        );
        $this->assertEquals(
            'anotherService',
            $result->getServiceId()
        );

        // inlineProp
        $result = $sp->parseServicePropertiesPropertyTag($serviceParametersXml[4]);
        $this->assertTrue(
            strpos($result, 'someInlineValue') !== false,
            $this->createWrongParametersPropertyValue('inlineProp')
        );

        // assocProp
        $result = $sp->parseServicePropertiesPropertyTag($serviceParametersXml[5]);
        $this->assertEquals(
            'assoc-value',
            $result
        );

        // inlineCastProp
        $result = $sp->parseServicePropertiesPropertyTag($serviceParametersXml[6]);
        $this->assertTrue(
            $result,
            'properties/property[name="inlineCastProp"] must be === true'
        );
    }

    public function testParseServicePropertiesPropertyChildConfRefTag()
    {
        $xml = <<<XML
        <context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		         xmlns="http://vobla-project.org/xsd/context"
		         xmlns:foo="fooNs">
            <cfg-ref name="fooProp1" is-optional="true" />
            <cfg-ref name="fooProp2" is-optional="false" />
        </context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        list($fooProp1Xml, $fooProp2Xml) = $xmlEl->children();

        /* @var \Vobla\ServiceConstruction\Definition\References\ConfigPropertyReference $fooProp1Result */
        $fooProp1Result = $this->sp->parseServicePropertiesPropertyChildConfRefTag($fooProp1Xml);
        $this->assertType(
          ConfigPropertyReference::clazz(),
            $fooProp1Result
        );
        $this->assertTrue($fooProp1Result->isOptional());

        /* @var \Vobla\ServiceConstruction\Definition\References\ConfigPropertyReference $fooProp2Result */
        $fooProp2Result = $this->sp->parseServicePropertiesPropertyChildConfRefTag($fooProp2Xml);
        $this->assertType(
          ConfigPropertyReference::clazz(),
            $fooProp2Result
        );
        $this->assertFalse($fooProp2Result->isOptional());
    }

    public function testParseServicePropertiesPropertyChildServiceTag()
    {
        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">

        <service />
</context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        list($serviceXml) = $xmlEl->children();

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addDelegateMethod('parseServicePropertiesPropertyChildServiceTag', 1)
        ->addMethod('parseServiceTag', function() {
                
            return array('id', 'service-def');
        }, 1)
        ->createMock();

        $result = $sp->parseServicePropertiesPropertyChildServiceTag($serviceXml);
        $this->assertEquals('service-def', $result);
        
    }

    public function testParseServicePropertiesTag()
    {
        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">

        <properties>
            <property name="prop1" />
            <property name="prop2" />
        </properties>
</context>
XML;

        $xml = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $xmlChildren = $xml->children();
        $propertiesXml = $xmlChildren[0];

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseServicePropertiesPropertyTag', function() {
            return 'property-value';
        }, 2)
        ->addDelegateMethod('parseServicePropertiesTag', 1)
        ->createMock();

        $result = $sp->parseServicePropertiesTag($propertiesXml);
        $this->assertTrue(
            is_array($result),
            sprintf('%s::parseServiceParametersTag execution result must be an array', ServiceProcessor::clazz())
        );
        $this->assertEquals(
            2,
            sizeof($result),
            'Resulting array must contain 2 root-elements.'
        );
    }

    private function createWrongPropertyParsingResult()
    {
        return sprintf('%s::parseServicePropertiesPropertyTag result must be an array', ServiceProcessor::clazz());
    }

    private function createWrongParametersPropertyValue($propertyName)
    {
        return sprintf('parameters/property[name="%s"] value doesn\'t match in resulting array.', $propertyName);
    }

    private function createMissingParametersPropertyValue($propertyName)
    {
        return sprintf('parameters/property[name="%s"] value is missing in resulting array.', $propertyName);
    }

    public function testParseServiceConstructorArgArrayTag()
    {
        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseArray', function() {}, 1)
        ->addDelegateMethod('parseServiceConstructorArgArrayTag', 1)
        ->createMock();

        $sp->parseServiceConstructorArgArrayTag(new \SimpleXMLElement('<x></x>'));
    }

    public function testParseServiceConstructorArgServiceTag()
    {
        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseServiceTag', function() {}, 1)
        ->addDelegateMethod('parseServiceConstructorArgServiceTag', 1)
        ->createMock();

        $sp->ParseServiceConstructorArgServiceTag(new \SimpleXMLElement('<x></x>'));
    }

    public function testParseServiceConstructorArgRefTag()
    {
        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseRef', function() {}, 1)
        ->addDelegateMethod('parseServiceConstructorArgRefTag', 1)
        ->createMock();

        $sp->parseServiceConstructorArgRefTag(new \SimpleXMLElement('<x></x>'));
    }

    public function testParseServiceConstructorArgValueTag_casted()
    {
        $tc = $this;

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $spWithType = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('castServicePropertiesPropertyTagValue', function($self, $value, $type) use($tc) {
            $tc->assertEquals(
                'fooValue',
                $value,
                "Passed <value> tag body doesn't match."
            );
            $tc->assertEquals(
                'fooType',
                $type,
                "Passed type to cast to doesn't match."
            );

            return $value.'-return';
        }, 1)
        ->addDelegateMethod('parseServiceConstructorArgValueTag', 1)
        ->createMock();
        $result = $spWithType->parseServiceConstructorArgValueTag(new \SimpleXMLElement('<value type="fooType">fooValue</value>'));

        $this->assertEquals(
            'fooValue-return',
            $result,
            "Casted <value>'s tag body doesn't match."
        );
    }

    public function testParseServiceConstructorArgValueTag()
    {
        $tc = $this;

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addDelegateMethod('parseServiceConstructorArgValueTag', 1)
        ->createMock();

        $result = $sp->parseServiceConstructorArgValueTag(new \SimpleXMLElement('<value>fooValue</value>'));
        $this->assertEquals(
            'fooValue',
            $result,
            "Returned <value>'s tag body doesn't match."
        );
    }

    public function testParseServiceConstructorArg()
    {
        $tc = $this;

        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">

        <arg>
            <array />
        </arg>
        <arg>
            <service />
        </arg>
        <arg>
            <ref />
        </arg>
        <arg>
            <value></value>
        </arg>
</context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        list($arrayXml, $serviceXml, $refXml, $valueXml) = $xmlEl->children();

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseServiceConstructorArgArrayTag', function($self, $el) use($tc) {
            $tc->assertType('SimpleXMLElement', $el);
            $tc->assertEquals('array', $el->getName());
        }, 1)
        ->addMethod('parseServiceConstructorArgServiceTag', function($self, $el) use($tc) {

        }, 1)
        ->addMethod('parseServiceConstructorArgRefTag', function($self, $el) use($tc) {

        }, 1)
        ->addMethod('parseServiceConstructorArgValueTag', function($self, $el) use($tc) {

        }, 1)
        ->addDelegateMethod('parseServiceConstructorArgTag', 4)
        ->createMock();

        $sp->parseServiceConstructorArgTag($arrayXml);
        $sp->parseServiceConstructorArgTag($serviceXml);
        $sp->parseServiceConstructorArgTag($refXml);
        $sp->parseServiceConstructorArgTag($valueXml);
    }

    public function testParseServiceConstructor()
    {
        $tc = $this;

        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">

    <constructor>
        <arg />
        <arg />
    </constructor>
</context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $xmlChildren = $xmlEl->children();
        $constructorXml = $xmlChildren[0];

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseServiceConstructorArgTag', function($self, $argXml) use($tc) {
            $tc->assertEquals(
                'arg',
                $argXml->getName(),
                sprintf(
                    'Argument passed to the %s::parseServiceConstructorArgTag must always be <arg> tag.',
                    ServiceDefinition::clazz()
                )
            );
        }, 2)
        ->addDelegateMethod('parseServiceConstructorTag', 1)
        ->createMock();

        $result = $sp->parseServiceConstructorTag($constructorXml);
        $this->assertTrue(
            is_array($result),
            sprintf('%s::parseServiceConstructor must return an array', ServiceProcessor::clazz())
        );
        $this->assertEquals(
            2,
            sizeof($result),
            'Resulting array must contain to elements'
        );
    }

    public function testParseServiceTag()
    {
        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">

	<foo:service />

    <service id="fooServiceId"
             factory-method="fooFactoryMethod"
             factory-service="fooFactoryService"
             is-abstract="false"
             init-method="fooInitMethod"
             tags="fooTag, barTag"
             not-by-type-wiring-candidate="true">

             <constructor />

             <properties />
    </service>
</context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $children = $xmlEl->children();
        $serviceXmlEl = $children[0];

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseServiceConstructorTag', function() {
            return array('constructor-args');
        }, 1)
        ->addMethod('parseServicePropertiesTag', function() {
            return array('properties');
        }, 1)
        ->addDelegateMethod('parseServiceTag', 1)
        ->addDelegateMethod('createMethodNameFromAttributeName', 1)
        ->createMock();

        $def = $sp->parseServiceTag($serviceXmlEl);
        $this->assertTrue(
            is_array($def),
            sprintf(
                '%s::parseServiceTag must return an array on successful invocation',
                ServiceProcessor::clazz(), ServiceDefinition::clazz()
            )
        );

        $this->assertEquals(
            $def[0],
            'fooServiceId',
            "Service ID doesn't match."
        );

        $this->assertType(
            ServiceDefinition::clazz(),
            $def[1],
            sprintf(
                '%s::parserServiceTag must return instance of %s after successful parsing.',
                ServiceProcessor::clazz(), ServiceDefinition::clazz()
            )
        );
        /* @var \Vobla\ServiceConstruction\Definition\ServiceDefinition $def */
        $def = $def[1];

        $this->assertEquals(
            'fooFactoryMethod',
            $def->getFactoryMethod(),
            "Factory-method name doesn't match."
        );

        $this->assertEquals(
            'fooFactoryService',
            $def->getFactoryService(),
            "Factory-service doesn't match."
        );

        $this->assertFalse(
            $def->isAbstract(),
            "Service with id 'fooServiceId' must have isAbstract attribute set to FALSE."
        );

        $this->assertEquals(
            'fooInitMethod',
            $def->getInitMethod(),
            "init-method doesn't match."
        );

        $this->assertSame(
            array('constructor-args'),
            $def->getConstructorArguments(),
            "Constructor-args don\'t match"
        );

        $this->assertSame(
            array('properties'),
            $def->getArguments(),
            'Properties don\'t match'
        );

        $this->assertSame(
            array('fooTag', 'barTag'),
            $def->getMetaEntry('tags'),
            'Tags attribute was not properly processed.'
        );

        $this->assertTrue(
            $def->getMetaEntry('notByTypeWiringCandidate'),
            '"not-by-type-wiring-candidate" attribute was not properly processed.'
        );
    }

    public function testProcessXml()
    {
        $tc = $this;

        $def = new ServiceDefinition();

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseServiceTag', function($self) use($def) {
            return array('anId', $def);
        }, 1)
        ->addDelegateMethod('processXml', 1)
        ->createMock();

        $container = $this->mf->createTestCaseAware(Container::clazz())
        ->addMethod('addServiceDefinition', function($self, $id, $argDef) use($tc, $def) {
            $tc->assertSame($argDef, $def);
        }, 1)
        ->createMock();

        $xmlBuilder = $this->mf->createTestCaseAware(XmlBuilder::clazz())
                               ->addMethod('getContainer', $container)
                              ->createMock();

        $xml = <<<XML
    <context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">

	<foo:service />

    <service />
</context>
XML;
;
        $sp->processXml($xml, $xmlBuilder);
    }

    public function testProcessXml_serviceWithNoId()
    {
        $tc = $this;

        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">
    
    <service />
</context>
XML;

        $def = $this->mf->createTestCaseAware(ServiceDefinition::clazz())
        ->addMethod('getClassName', 'stdClass')
        ->createMock();
        
        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseServiceTag', array('', $def), 1)
        ->addDelegateMethod('processXml')
        ->createMock();

        $serviceIdGenerator = $this->mf->createTestCaseAware(ServiceIdGenerator::clazz())
        ->addMethod('generate', function($self, $reflClass, $declaredId, $argDef) use($tc, $def) {
            $tc->assertSame(
                $def,
                $argDef,
                sprintf(
                    '%s::generate must receive the same instance of %s that %s::parseServiceTag created.',
                    ServiceIdGenerator::clazz(), ServiceDefinition::clazz(), ServiceProcessor::clazz()
                )
            );
            $tc->assertEquals('', $declaredId);
        }, 1)
        ->createMock();

        $container = $this->mf->createTestCaseAware(Container::clazz())
        ->addMethod('addServiceDefinition')
        ->createMock();

        $xmlBuilder = $this->mf->createTestCaseAware(XmlBuilder::clazz())
        ->addMethod('getServiceIdGenerator', $serviceIdGenerator)
        ->addMethod('getContainer', $container)
        ->createMock();
        
        $sp->processXml($xml, $xmlBuilder);
    }

    public function testParseConfigTag()
    {
        $tc = $this;

        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">

    <config>
        <property name="prop1" value="value1" />
        <property name="prop2">
            <array>
                <el index="foo" value="bar" />
            </array>
        </property>
    </config>
</context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $children = $xmlEl->children();
        $configXml = $children[0];

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addMethod('parseConfigPropertyTag', function($self, $propertyXmlEl) use($tc) {
            $tc->assertEquals('property', $propertyXmlEl->getName());

            $attrs = $propertyXmlEl->attributes();
            return $attrs['name'].'Value';
        }, 2)
        ->addDelegateMethod('parseConfigTag', 1)
        ->createMock();

        $result = $sp->parseConfigTag($configXml);
        $this->assertTrue(
            is_array($result),
            sprintf('%s::parseConfigPropertyTag execution always must be an array!', ServiceProcessor::clazz())
        );

        $ids = array_keys($result);
        sort($ids);
        $this->assertEquals(array('prop1', 'prop2'), $ids);

        $values = array_values($result);
        sort($values);
        $this->assertEquals(array('prop1Value', 'prop2Value'), $values);
    }

    public function testParseConfigPropertyTag()
    {
        $tc = $this;

        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">
        
        <property name="prop1" value="value1" />
        <property name="prop2">
            <array>
                <el index="foo" value="bar" />
            </array>
        </property>
</context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $children = $xmlEl->children();
        list($prop1Xml, $prop2Xml, $prop3Xml) = $children;

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp */
        $sp = $this->mf->createTestCaseAware(ServiceProcessor::clazz())
        ->addDelegateMethod('parseConfigPropertyTag', 2)
        ->addMethod('parseArray', 'array-value', 1)
        ->createMock();

        $prop1Value = $sp->parseConfigPropertyTag($prop1Xml);
        $this->assertEquals('value1', $prop1Value);

        $prop2Value = $sp->parseConfigPropertyTag($prop2Xml);
        $this->assertEquals('array-value', $prop2Value);
    }

    public function testProcessXml_integration()
    {
        $tc = $this;

        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">

	<foo:service />

	<config>
        <property name="cfg1" value="cfg1Value" />
        <property name="cfg2">cfg2Value</property>
        <property name="cfg3">
            <array>
                <el index="fooIndex">fooIndexValue</el>
            </array>
        </property>
    </config>

    <service id="fooServiceId"
             factory-method="fooFactoryMethod"
             factory-service="fooFactoryService"
             is-abstract="true"
             init-method="fooInitMethod">

             <constructor>
                <arg>
                    <value>fooValue</value>
                </arg>
                <arg>
                    <service init-method="anAnonymousInitMethod" />
                </arg>
                <arg>
                    <ref id="fooRef" />
                </arg>
             </constructor>

             <properties>
                <property name="barProp">
                    <service />
                </property>
                <property name="fooProp">
                    <array>
                        <el index="fooPropSub">fooValueSub</el>
                    </array>
                </property>
             </properties>
    </service>
</context>
XML;

        $defs = array();

        $configHolder = $this->mf->createTestCaseAware(ConfigHolder::clazz())
        ->addMethod('set', function($self, $name, $value) use($tc) {
            switch ($name) {
                case 'cfg1':
                    $tc->assertEquals('cfg1Value', $value);
                break;

                case 'cfg2':
                    $tc->assertEquals('cfg2Value', $value);
                break;

                case 'cfg3':
                    $tc->assertEquals(array('fooIndex' => 'fooIndexValue'), $value);
                break;
            }
        }, 3)
        ->createMock();

        $container = $this->mf->createTestCaseAware(Container::clazz())
        ->addMethod('addServiceDefinition', function($self, $id, $argDef) use(&$defs) {
            $defs[$id] = $argDef;
        }, 1)
        ->addMethod('getConfigHolder', $configHolder, 1)
        ->createMock();

        $xmlBuilder = $this->mf->createTestCaseAware(XmlBuilder::clazz())
        ->addMethod('getContainer', $container)
        ->createMock();

        $this->sp->processXml($xml, $xmlBuilder);

        $this->assertEquals(
            1,
            sizeof($defs)
        );
        $this->assertTrue(
            isset($defs['fooServiceId'])
        );
        $this->assertType(
            ServiceDefinition::clazz(),
            $defs['fooServiceId']
        );

        /* @var \Vobla\ServiceConstruction\Definition\ServiceDefinition $def1 */
        $def1 = $defs['fooServiceId'];
        $this->assertEquals('fooFactoryMethod', $def1->getFactoryMethod());
        $this->assertEquals('fooFactoryService', $def1->getFactoryService());
        $this->assertTrue($def1->isAbstract());
        $this->assertEquals('fooInitMethod', $def1->getInitMethod());

        $def1cp = $def1->getConstructorArguments();
        $this->assertTrue(is_array($def1cp));
        $this->assertEquals(3, sizeof($def1cp));
        $this->assertEquals('fooValue', $def1cp[0]);

        /* @var \Vobla\ServiceConstruction\Definition\ServiceDefinition $def1cpArg2 */
        $def1cpArg2 = $def1cp[1];
        $this->assertType(
            ServiceDefinition::clazz(),
            $def1cpArg2,
            sprintf("Second constructor's argument must be an instance of the %s", ServiceDefinition::clazz())
        );
        $this->assertEquals('anAnonymousInitMethod', $def1cpArg2->getInitMethod());

        /* @var \Vobla\ServiceConstruction\Definition\ServiceDefinition $def1cpArg3 */
        $def1cpArg3 = $def1cp[2];
        $this->assertType(
            IdReference::clazz(),
            $def1cpArg3,
            sprintf("Third constructor's argument must be an instance of the %s", IdReference::clazz())
        );

        $def1Props = $def1->getArguments();
        $this->assertTrue(is_array($def1Props));
        $this->assertEquals(2, sizeof($def1Props));

        $this->assertTrue(isset($def1Props['barProp']));
        $this->assertType(
            ServiceDefinition::clazz(),
            $def1Props['barProp'],
            sprintf(
                'service[id="fooServiceId"]/properties/property[name="barProp"] value must be an instance of %s',
                ServiceDefinition::clazz()
            )
        );

        $this->assertTrue(isset($def1Props['fooProp']));
        $def1Props2 = $def1Props['fooProp'];
        $this->assertSame(
            array('fooPropSub'=>'fooValueSub'),
            $def1Props2
        );
    }
}
