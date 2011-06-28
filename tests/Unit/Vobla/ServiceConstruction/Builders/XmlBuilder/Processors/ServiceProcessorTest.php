<?php

namespace Vobla\ServiceConstruction\Builders\XmlBuilder\Processors;

require_once __DIR__.'/../../../../../../bootstrap.php';

use Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Definition\ServiceReference;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
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

    public function testParseServiceParametersPropertyChildArrayElTag()
    {
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
                    </el>
                    <el>
                        <el>
                            <ref id="anId" />
                            <el index="foo" value="fooVal" />
                        </el>
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

        $result = $this->sp->parseServiceParametersPropertyChildArrayElTag($xmlEl);
        $this->assertTrue(
            is_array($result),
            sprintf("%s::parseServiceParametersPropertyChildArrayElTag must return an array", ServiceProcessor::clazz())
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
            2,
            sizeof($rAssoc0sa3),
            '/assoc/0/sa3 array must contain only 2 elements'
        );
        $this->assertType(
            ServiceDefinition::clazz(),
            $rAssoc0sa3[0],
            sprintf('/assoc/0/sa3/0 must be an instance of %s', ServiceDefinition::clazz())
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

    // TODO move this test down somewhere ?
    public function testParseServiceParametersPropertyChildArrayTag()
    {
        $xml = <<<XML
        <array>
            <el index="someRef">
                <el>
                    <ref id="anId" />
                </el>
            </el>
            <el index="subAssoc">
                <el>
                    <el index="sa1" value="sav1" />
                    <el index="sa2">
                        sav2
                    </el>
                    <el index="sa3">
                        <service />
                    </el>
                </el>
            </el>
            <el>
                <el index="fooKey" value="fooValue" />
            </el>
        </array>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $contextChildrenXml = $xmlEl->children();
        $arrayXml = $contextChildrenXml;

        $result = $this->sp->parseServiceParametersPropertyChildArrayTag($arrayXml);
        $this->assertTrue(
            is_array($result),
            sprintf("%s::parseServiceParametersPropertyChildArrayTag must return an array", ServiceProcessor::clazz())
        );
    }

    public function testParseServiceParametersPropertyChildTag()
    {
        $tc = $this;

        $xml = <<<XML
        <context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">

        <property name="prop1">
            <array>
            </array>
        </property>

        <property name="prop2">
            <service>
            </service>
        </property>
</context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $contextChildrenXml = $xmlEl->children();
        $prop1Xml = $contextChildrenXml[0]->children();
        $prop2Xml = $contextChildrenXml[1]->children();

        $arrayXml = $prop1Xml[0];
        $serviceXml = $prop2Xml[0];

        // array
        $cb1 = function($self, $argArrayXml) use($arrayXml, $tc) {
            $tc->assertSame($arrayXml, $argArrayXml);
        };
        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\ServiceProcessor $sp1 */
        $sp1 = $this->mf->createTestCaseAware(ServiceProcessor::clazz())->addMethod(
            'parseServiceParametersPropertyChildArrayTag',
            $cb1,
            1
        )->addDelegateMethod('parseServiceParametersPropertyChildTag', 1)->createMock();

        $sp1->parseServiceParametersPropertyChildTag($arrayXml);

        // service
        $cb2 = function($self, $argServiceXml) use($serviceXml, $tc) {
            $tc->assertSame($serviceXml, $argServiceXml);
        };

        $sp2 = $this->mf->createTestCaseAware(ServiceProcessor::clazz())->addMethod(
            'parseServiceParametersPropertyChildServiceTag',
            $cb2,
            1
        )->addDelegateMethod('parseServiceParametersPropertyChildTag', 1)->createMock();

        $sp2->parseServiceParametersPropertyChildTag($serviceXml);
    }

    public function testParseServiceParametersPropertyTag()
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
            <property name="assocValue">
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
        </parameters>
    </service>
</context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $contextChildrenXml = $xmlEl->children();
        $serviceChildrenXml = $contextChildrenXml->children();
        $serviceParametersXml = $serviceChildrenXml[0]->children();

        // scalarProperty
        $result = $this->sp->parseServiceParametersPropertyTag($serviceParametersXml[0]);
        $this->assertTrue(
            is_array($result),
            $this->createWrongPropertyParsingResult()
        );
        $this->assertTrue(
            isset($result['scalarProperty']),
            $this->createMissingParametersPropertyValue('scalarProperty')
        );
        $this->assertEquals(
            'fooValue',
            $result['scalarProperty'],
            $this->createWrongParametersPropertyValue('scalarProperty')
        );

        // trueBoolProperty
        $result = $this->sp->parseServiceParametersPropertyTag($serviceParametersXml[1]);
        $this->assertTrue(
            is_array($result),
            $this->createWrongPropertyParsingResult()
        );
        $this->assertTrue(
            isset($result['trueBoolProperty']),
            $this->createMissingParametersPropertyValue('trueBoolProperty')
        );
        $this->assertTrue(
            $result['trueBoolProperty'],
            $this->createWrongParametersPropertyValue('trueBoolProperty')
        );

        // falseBoolProperty
        $result = $this->sp->parseServiceParametersPropertyTag($serviceParametersXml[2]);
        $this->assertTrue(
            is_array($result),
            $this->createWrongPropertyParsingResult()
        );
        $this->assertFalse(
            isset($result['trueBoolProperty']),
            $this->createMissingParametersPropertyValue('falseBoolProperty')
        );
        $this->assertFalse(
            $result['falseBoolProperty'],
            $this->createWrongParametersPropertyValue('falseBoolProperty')
        );

        // refProperty
        $result = $this->sp->parseServiceParametersPropertyTag($serviceParametersXml[3]);
        $this->assertTrue(
            is_array($result),
            $this->createWrongPropertyParsingResult()
        );
        $this->assertTrue(
            isset($result['refProperty']),
            $this->createMissingParametersPropertyValue('refProperty')
        );
        $this->assertType(
            ServiceReference::clazz(),
            $result['refProperty'],
            $this->createWrongParametersPropertyValue('refProperty')
        );
        $this->assertEquals(
            'anotherService',
            $result['refProperty']->getServiceId()
        );

        // inlineProp
        $result = $this->sp->parseServiceParametersPropertyTag($serviceParametersXml[4]);
        $this->assertTrue(
            is_array($result),
            $this->createWrongPropertyParsingResult()
        );
        $this->assertTrue(
            isset($result['inlineProp']),
            $this->createMissingParametersPropertyValue('inlineProp')
        );
        $this->assertTrue(
            strpos($result['inlineProp'], 'someInlineValue') !== false,
            $this->createWrongParametersPropertyValue('inlineProp')
        );
    }

    private function createWrongPropertyParsingResult()
    {
        return sprintf('%s::parseServiceParametersPropertyTag result must be an array', ServiceProcessor::clazz());
    }

    private function createWrongParametersPropertyValue($propertyName)
    {
        return sprintf('parameters/property[name="%s"] value is missing doesn\'t match in resulting array.', $propertyName);
    }

    private function createMissingParametersPropertyValue($propertyName)
    {
        return sprintf('parameters/property[name="%s"] value is missing in resulting array.', $propertyName);
    }

    public function testParseServiceParametersTag()
    {
        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
		 xmlns="http://vobla-project.org/xsd/context"
		 xmlns:foo="fooNs">

    <service>
        <parameters>
            <property name="prop1" />
            <property name="prop2" />
        </parameters>
    </service>
</context>
XML;

//        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
//        $contextChildrenXml = $xmlEl->children();
//        $serviceChildrenXml = $contextChildrenXml->children();
//        $serviceParametersXml = $serviceChildrenXml[0];
//
//        $result = $this->sp->parseServiceParametersTag($serviceParametersXml);
//        $this->assertTrue(
//            is_array($result),
//            sprintf("%s::parseServiceParametersTag result must be an array", ServiceProcessor::clazz())
//        );
//
//        $this->assertTrue(
//            isset($result['scalarProperty']),
//            $this->createInvalidParametersPropertyValue('scalarProperty')
//        );
//        $this->assertEquals(
//            'fooValue',
//            $result['scalarProperty'],
//            $this->createInvalidParametersPropertyValue('scalarProperty')
//        );

        
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
             init-method="fooInitMethod">

        <constructor>
            <param></param>
            <param></param>
        </constructor>
    </service>
</context>
XML;

        $xmlEl = new \SimpleXMLElement($xml, 0, false, 'http://vobla-project.org/xsd/context');
        $children = $xmlEl->children();
        $serviceXmlEl = $children[0];

        $def = $this->sp->parseServiceTag($serviceXmlEl);
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
    }
}
