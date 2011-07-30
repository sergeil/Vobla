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

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\ServiceConstruction\Builders\ServiceIdGenerator,
    Vobla\Container,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ScanPathsProvider,
    Vobla\Exception,
    Vobla\ServiceConstruction\Builders\AbstractBuilder,
    Doctrine\Common\Annotations\AnnotationRegistry;

if (!defined('VOBLA_ANNOTATIONS_LOADED')) {
    \Vobla\Tools\Toolkit::loadDirectory(__DIR__.'/Annotations');
    define('VOBLA_ANNOTATIONS_LOADED', true);
}

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class AnnotationsBuilder extends AbstractBuilder
{
    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $annotationReader;

    /**
     * @var \Vobla\ServiceConstruction\Builders\ServiceIdGenerator
     */
    protected $serviceIdGenerator;

    /**
     * @var array
     */
    protected $scanPathsProviders = array();

    /**
     * @var array
     */
    protected $eligibleFileTypes = array('php');

    /**
     * @return array
     */
    public function getScanPathsProviders()
    {
        return $this->scanPathsProviders;
    }

    public function addScanPathProvider(ScanPathsProvider $scanPathProvider)
    {
        $scanPathProvider->setAnnotationBuilder($this);
        $this->scanPathsProviders[] = $scanPathProvider;
    }

    /**
     * @return mixed
     */
    protected function getDefaultProcessorsProvider()
    {
        return new DefaultProcessorsProvider();
    }

    /**
     * @param \Doctrine\Common\Annotations\AnnotationReader $annotationReader
     */
    public function setAnnotationReader($annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    public function getAnnotationReader()
    {
        if (null === $this->annotationReader) {
            $this->annotationReader = new AnnotationReader();
        }

        return $this->annotationReader;
    }

    public function setServiceIdGenerator($serviceIdGenerator)
    {
        $this->serviceIdGenerator = $serviceIdGenerator;
    }

    /**
     * @return \Vobla\ServiceConstruction\Builders\ServiceIdGenerator
     */
    public function getServiceIdGenerator()
    {
        if (null === $this->serviceIdGenerator) {
            $this->serviceIdGenerator = new ServiceIdGenerator();
        }

        return $this->serviceIdGenerator;
    }
    
    public function processClass($clazz)
    {
        $reflClass = $clazz instanceof \ReflectionClass ? $clazz : new \ReflectionClass($clazz);

        $serviceAnnotation = $this->getAnnotationReader()->getClassAnnotation($reflClass, Service::clazz());
        if (!$serviceAnnotation) {
            return false;
        }

        $definition = new ServiceDefinition();

        foreach ($this->getProcessors() as $processor) {
            try {
                /* @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors\Processor $processor */
                $processor->handle($reflClass, $definition, $this);
            } catch (\Exception $e) {
                throw new Exception(
                    sprintf('Execution of "%s" processor failed.', get_class($processor)),
                    null,
                    $e
                );
            }
        }

        $serviceId = $this->getServiceIdGenerator()->generate($reflClass, $serviceAnnotation->id, $definition);
        return array(
            $serviceId,
            $definition
        );
    }

    /**
     * @param \Vobla\Container $container
     * @param  $path
     * @return array  Names of files we were not able to process for some reason. Most of the time they will be malformed files.
     */
    public function processPath(Container $container, $path)
    {
        $skippedFiles = array();
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $explodedFilename = explode('.', $file->getFilename());

            if (!in_array(end($explodedFilename), $this->eligibleFileTypes)) {
                continue;
            }

            try {
                $reflFile = new ReflectionFile(file_get_contents($file->getPathname()));

                $classNames = array(
                    implode('\\', array($reflFile->getNamespace(), $reflFile->getClassName()))
                );

                require_once $file->getPathname();

                $reflClasses = array();
                foreach ($classNames as $className) {
                    $reflClass = new \ReflectionClass($className);
                    if ($reflClass->isInterface()) { // TODO not under test
                        continue;
                    }
                    $reflClasses[] = $reflClass;
                }

                foreach ($reflClasses as $reflClass) { // let it be that a file may contain several class declarations
                    $data = $this->processClass($reflClass);
                    if (is_array($data)) {
                        $container->addServiceDefinition($data[0], $data[1]);
                    } else {
                        $skippedFiles[] = $file->getFilename();
                    }
                }
            } catch (\Exception $e) {
                $skippedFiles[] = array(
                    $file->getFilename(),
                    $e
                );
            }
        }

        return $skippedFiles;
    }

    /**
     * @return array  Filenames we were not able to process
     */
    public function configure(Container $container)
    {
        $skippedFiles = array();
        foreach ($this->getScanPathsProviders() as $pathsProvider) {
            foreach ($pathsProvider->getScanPaths($container) as $path) {
                $skippedFiles = array_merge($skippedFiles, $this->processPath($container, $path));
            }
        }
        return $skippedFiles;
    }
}
