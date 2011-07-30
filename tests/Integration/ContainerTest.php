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

require_once __DIR__.'/fixtures/RootService.php';
require_once __DIR__.'/fixtures/LoggerFactory.php';
require_once __DIR__.'/fixtures/CacheMap.php';
require_once __DIR__ . '/fixtures/CacheDrivers/CacheDriver.php';
require_once __DIR__ . '/fixtures/CacheDrivers/ApcDriver.php';
require_once __DIR__ . '/fixtures/CacheDrivers/MemcacheDriver.php';
require_once __DIR__ . '/fixtures/CacheDrivers/ArrayDriver.php';
require_once __DIR__ . '/fixtures/Controllers/Controller.php';
require_once __DIR__ . '/fixtures/Controllers/DashboardController.php';
require_once __DIR__ . '/fixtures/Controllers/TestController.php';
require_once __DIR__ . '/fixtures/Controllers/SettingsController.php';


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
        $container = new Container();
        $container->getConfigHolder()->set('memcacheServerUrl', 'some-foo-memcache-url');

        $ab = new AnnotationsBuilder($container);
        $skippedFilesWithExceptions = $ab->processPath($container, __DIR__.'/fixtures');
        $skippedClassNames = array();
        foreach ($skippedFilesWithExceptions as $entry) {
            $skippedClassNames[] = $entry[0];
        }

        $this->assertEquals(
            array(),
            $skippedClassNames,
            'No files must have been skipped during scanning.'
        );

        try {
            $this->tc($container);
        } catch (\Exception $e) {
            \Vobla\Tools\Toolkit::printException($e);
            throw $e;
        }
    }

    public function testItWithXmls()
    {
        $container = new Container();

        $xb = new XmlBuilder($container);

        foreach ($xb->getProcessors() as $p) {
            if ($p instanceof ImportProcessor) {
                /* @var \Vobla\ServiceConstruction\Builders\XmlBuilder\Processors\Import\ImportProcessor $p */
                $p->setPathResolver(new StaticPathResolver(__DIR__.'/fixtures/context/'));
            }
        }

        try {
            $xb->processXml(file_get_contents(__DIR__ . '/fixtures/context/a.xml'), $container);

            $this->tc($container);
        } catch (\Exception $e) {
            //\Vobla\Tools\Toolkit::printException($e);
            throw $e;
        }
    }

    protected function tc(Container $container)
    {
        /* @var \RootService $rootService1 */
        $rootService1 = $container->getServiceById('rootService');
        $this->assertType('RootService', $rootService1);

        $rootService2 = $container->getServiceById('rootService');
        $this->assertType('RootService', $rootService2);

        $this->assertNotSame($rootService1, $rootService2);

        $this->assertType('LoggerFactory', $rootService1->loggerFactory);
        $this->assertType('CacheMap', $rootService1->cacheMap);


        /* @var \CacheMap $cacheMap */
        $cacheMap = $rootService1->cacheMap;
        $this->assertTrue(
            is_array($cacheMap->cacheDrivers),
            "RootService::cacheDrivers property wasn't initialized as array"
        );
        $this->assertEquals(3, sizeof($cacheMap->cacheDrivers));

        $cacheDriverIds = array_keys($cacheMap->cacheDrivers);
        sort($cacheDriverIds);
        $this->assertEquals(
            array('apcCacheDriver', 'arrayCacheDriver', 'memcacheCacheDriver'),
            $cacheDriverIds
        );
        $this->assertType('ApcDriver', $cacheMap->cacheDrivers['apcCacheDriver']);
        $this->assertType('ArrayDriver', $cacheMap->cacheDrivers['arrayCacheDriver']);
        $this->assertType('MemcacheDriver', $cacheMap->cacheDrivers['memcacheCacheDriver']);

        $this->assertEquals($cacheMap->cacheDrivers['memcacheCacheDriver']->serverUrl, 'some-foo-memcache-url');

        $this->assertTrue(is_array($rootService1->controllers));
        $this->assertEquals(2, sizeof($rootService1->controllers));
        $controllerIds = array_keys($rootService1->controllers);
        sort($controllerIds);
        $this->assertEquals(array('dashboardController', 'settingsController'), $controllerIds);
        $this->assertType(\DashboardController::clazz(),$rootService1->controllers['dashboardController']);
        $this->assertType(\SettingsController::clazz(),$rootService1->controllers['settingsController']);
    }
}
