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

namespace Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import;

require_once __DIR__.'/../../../../../../../bootstrap.php';

use Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\XsdNamespaces,
    Vobla\ServiceConstruction\Builders\XmlBuilder\XmlBuilder,
    Vobla\Container;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.net>
 */ 
class ImportProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Moko\MockFactory
     */
    protected $mf;

    public function setUp()
    {
        $this->mf = new \Moko\MockFactory($this);
    }

    public function tearDown()
    {
        $this->mf = null;
    }

    public function testGetPathResolver()
    {
        $ip = new ImportProcessor();
        $this->assertType(
            PathResolver::CLAZZ,
            $ip->getPathResolver(),
            sprintf(
                'If no %s manually defined some default implementation must be created on first request',
                PathResolver::CLAZZ
            )
        );
    }

    public function testParseImportTag()
    {
        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns="http://vobla-project.org/xsd/context"
 xmlns:x="xxx">
    <import resource="foo.xml" />
    <import resource="bar.xml" />

    <x:import resource="woodo-resource.xml" />
</context>
XML;
        $xmlContext = new \SimpleXMLElement($xml, 0, false, XsdNamespaces::CONTEXT);
        $xmlContextChildren = $xmlContext->children();
        $importXml1 = $xmlContextChildren[0];
        $importXml2 = $xmlContextChildren[1];

        $ip = new ImportProcessor();

        $this->assertEquals(
            'foo.xml',
            $ip->parseImportTag($importXml1),
            "Resource name to be import is incorrect"
        );
        $this->assertEquals(
            'bar.xml',
            $ip->parseImportTag($importXml2),
            "Resource name to be import is incorrect"
        );
    }

    public function testProcessXml()
    {
        $xml = <<<XML
<context xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns="http://vobla-project.org/xsd/context"
 xmlns:x="xxx">
    <import resource="a.xml" />

    <x:import resource="woodo-resource.xml" />
</context>
XML;

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import\PathResolver $pathResolver */
        $pathResolver = $this->mf->createTestCaseAware(PathResolver::CLAZZ)
        ->addMethod('resolve', function($self, $path) {
            return implode(DIRECTORY_SEPARATOR, array(__DIR__, 'fixtures', $path));
        }, 3)
        ->createMock();

        $ip = new ImportProcessor();
        $ip->setPathResolver($pathResolver);

        /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\XmlBuilder $xmlBuilder */
        $xmlBuilder = $this->mf->createTestCaseAware(XmlBuilder::clazz())
        ->addMethod('processXml', function($self, $xmlBody) use($ip) {
            $ip->processXml($xmlBody, $self);
        }, 2)
        ->createMock();

        /* @var \Vobla\Container $container */
        $container = $this->mf->create(Container::clazz())->createMock();

        $ip->processXml($xml, $xmlBuilder);

        $resolvedResources = $ip->getResolvedResources();
        
        $this->assertTrue(is_array($resolvedResources));
        $this->assertEquals(
            2,
            sizeof($resolvedResources),
            'Only two resources must have been loaded'
        );

        $hasA = $hasB = false;
        foreach ($resolvedResources as $rn) {
            $expRn = explode(DIRECTORY_SEPARATOR, $rn);
            $rn = end($expRn);
            if ('a.xml' == $rn) {
                $hasA = true;
            }
            if ('b.xml' == $rn) {
                $hasB = true;
            }
        }
        $this->assertTrue($hasA, 'a.xml resource must have been loaded');
        $this->assertTrue($hasB, 'b.xml resource must have been loaded');

    }
}
