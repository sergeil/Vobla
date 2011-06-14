<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder;

use Doctrine\Common\Annotations\AnnotationReader,
    Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Definition\QualifiedReference;

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Parameter,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Qualifier,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Reference,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class AnnotationsBuilder 
{
    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $annotationReader;

    protected $eligibleFileTypes = array();

    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    protected $scanPathsProviders = array();

    public function addScanPathProvider(ScanPathsProvider $scanPathProvider)
    {
        $scanPathProvider->setAnnotationBuilder($this);
        $this->scanPathsProviders[] = $scanPathProvider;
    }

    public function configure(Container $container)
    {
        foreach ($this->scanPathsProviders as $pathsProvider) {
            foreach ($pathsProvider->getScanPaths($container) as $path) {
                $this->processPath($container, $path);
            }
        }
    }

    public function processPath(Container $container, $path)
    {
        foreach (new RecursiveDirectoryIterator(new RecursiveIteratorIterator($path)) as $file) {
            $explodedFilename = explode('.', $file->getFilename());

            if (!in_array(end($explodedFilename), $this->eligibleFileTypes)) {
                continue;
            }

            $reflFile = new \Zend_Reflection_File($file->getPathname());
            $classesInFile = $reflFile->getClasses('ReflectionClass');

            foreach ($classesInFile as $reflClass) { // let it be that a file may contain several class declarations
                $data = $this->processClass($container, $reflClass);

                if (is_array($data)) {
                    $container->getDefinitionsHolder()->register(
                            $data[0],
                            $data[1] // don't get confused, we will be using a reference to the same object later
                    );
                }
            }
        }
    }


    /**
     * TODO: add support for parents scanning
     *
     * @throws Exception
     * @param Container $container
     * @param string|ReflectionClass $reflClass
     * @return bool|ServiceDefinition
     */
    public function processClass(Container $container, $clazz)
    {
        $reflClass = null;
        if (!($clazz instanceof \ReflectionClass)) {
            $reflClass = new \ReflectionClass($clazz);
        }

        $serviceDef = new ServiceDefinition();
        $args = $constructorArgs = array();

        /* @var Service $serviceAnnotation */
        $serviceAnnotation = $this->annotationReader->getClassAnnotation($reflClass, Service::clazz());
        if (!$serviceAnnotation) { // not a service, skipping
            return false;
        }
        $serviceDef->setAbstract($serviceAnnotation->isAbstract);
        $serviceDef->setArguments($this->processProperties($reflClass));

        $isConstructorFound = false;
        foreach ($reflClass->getMethods() as $reflMethod) {
            /* @var Constructor $constructorAnnotation */
            $constructorAnnotation = $this->annotationReader->getMethodAnnotation($reflMethod, Constructor::clazz());
            if (!$constructorAnnotation) {
                continue;
            } else if ($isConstructorFound) {
                // TODO throw a proper exception
                throw new \Exception(sprintf('Multiple constructors defined in class %s', $reflClass->getName()));
            }

            $isConstructorFound = true;
            $serviceDef->setFactoryMethod($reflMethod->getName());
            $serviceDef->setConstructorArguments(
                $this->dereferenceConstructorArgs($constructorAnnotation->args)
            );
        }

        return $serviceDef;
    }

    protected function processProperties(\ReflectionClass $reflClass)
    {
        $result = $serviceClasses = array();
        foreach ($reflClass->getProperties() as $reflProp) {
            $reflDeclaredClass = $reflProp->getDeclaringClass();
            if (!in_array($reflDeclaredClass->getName(), $serviceClasses)) {
                $serviceAnnotation = $this->annotationReader->getClassAnnotation($reflDeclaredClass, Service::clazz());
                if ($serviceAnnotation) {
                    $serviceClasses[] = $reflDeclaredClass->getName();
                }
            }

            // if a declared class doesn't have Service annotation skipping its properties
            if (!in_array($reflDeclaredClass->getName(), $serviceClasses)) {
                continue;
            }

            /* @var Autowired $autowiredAnnotation */
            $autowiredAnnotation = $this->annotationReader->getPropertyAnnotation($reflProp, Autowired::clazz());
            if (!$autowiredAnnotation) {
                continue;
            }

            $refDef = null;
            if ($autowiredAnnotation->qualifier !== null) { // qualifier has priority
                $refDef = new QualifiedReference($autowiredAnnotation->qualifier);
            } else {
                $refServiceId = $autowiredAnnotation->id === null ? $reflProp->getName() : $autowiredAnnotation->id;
                $refDef = new ServiceReference($refServiceId);
            }

            $result[$reflProp->getName()] = $refDef;
        }
        
        return $result;
    }


    /**
     * TODO: consider options of adding support of some external handlers of annotations
     *
     * Override this method if you want to introduce some more annotations
     *
     * @param array $constructorArgs
     * @return array
     */
    protected function dereferenceConstructorArgs(array $constructorArgs)
    {
        $dereferencedConstructorArgs = array();
        foreach ($constructorArgs as $arg) {
            if ($arg instanceof Qualifier) {
                $dereferencedConstructorArgs[$arg] = new QualifiedReference($arg);
            } else {
                $dereferencedConstructorArgs[$arg] = new Reference($arg);
            }
        }

        return $dereferencedConstructorArgs;
    }
}
