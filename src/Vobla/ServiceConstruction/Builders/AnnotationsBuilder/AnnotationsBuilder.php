<?php

use Doctrine\Common\Annotations\AnnotationReader;

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
     * @param ReflectionClass $reflClass
     * @return bool|ServiceDefinition
     */
    public function processClass(Container $container, \ReflectionClass $reflClass)
    {
        $serviceDef = new ServiceDefinition();
        $args = $constructorArgs = array();

        /* @var Service $serviceAnnotation */
        $serviceAnnotation = $this->annotationReader->getClassAnnotation($reflClass, Service::clazz());
        if (!$serviceAnnotation) { // not a service, skipping
            return false;
        }

        foreach ($reflClass->getProperties() as $reflProp) {
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
                $refDef = new Reference($refServiceId);
            }

            $args[$reflProp->getName()] = $refDef;
        }

        $isConstructorFound = false;
        foreach ($reflClass->getMethods() as $reflMethod) {
            /* @var Constructor $constructorAnnotation */
            $constructorAnnotation = $this->annotationReader->getMethodAnnotation($reflMethod, Constructor::clazz());
            if (!$constructorAnnotation) {
                continue;
            } else if ($isConstructorFound) {
                // TODO throw proper exception
                throw new \Exception(sprintf('Multiple constructors defined in class %s', $reflClass->getName()));
            }

            $isConstructorFound = true;
            $serviceDef->setFactoryMethod($reflMethod->getName());
            $serviceDef->setConstructorArguments(
                $this->dereferenceConstructorArgs($constructorAnnotation->args)
            );
        }

        $serviceDef->setArguments($args);

        return $serviceDef;
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
