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
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ScanPathsProvider;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class AnnotationsBuilder 
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
     * @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\ProcessorsProvider
     */
    protected $processorsProvider;

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
     * @var array
     */
    protected $cachedProcessors;

    public function __construct($processorsProvider = null)
    {
        if (null === $processorsProvider) {
            $this->processorsProvider = new DefaultProcessorsProvider();
        } else {
            $this->processorsProvider = $processorsProvider;
        }
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

    public function getProcessors()
    {
        if (null === $this->cachedProcessors) {
            $this->cachedProcessors = $this->processorsProvider->getProcessors();

            // TODO throw an exception if no processors provided
        }

        return $this->cachedProcessors;
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
            $processor->handle($this->getAnnotationReader(), $reflClass, $definition);
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
                    $reflClasses[] = new \ReflectionClass($className);
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
                $skippedFiles[] = $file->getFilename();
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

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
