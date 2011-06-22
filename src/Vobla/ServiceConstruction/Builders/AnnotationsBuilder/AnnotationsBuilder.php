<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder;

use Doctrine\Common\Annotations\AnnotationReader,
    Vobla\Container,
    Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Definition\ServiceReference,
    Vobla\ServiceConstruction\Definition\QualifiedReference,
    Vobla\Exception;

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Parameter,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\ConstructorParamQualifier,
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

    /**
     * @var array
     */
    protected $eligibleFileTypes = array('php');

    /**
     * @var array
     */
    protected $scanPathsProviders = array();

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

    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
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
                        $container->getDefinitionsHolder()->register(
                            $data[0],
                            $data[1] // don't get confused, we will be using a reference to the same object later
                        );
                    }
                }
            } catch (\Exception $e) {
                $skippedFiles[] = $file->getFilename();
            }
        }

        return $skippedFiles;
    }

    /**
     * TODO: add support for parents scanning
     *
     * @throws Exception
     * @param string|ReflectionClass $reflClass
     * @return bool|ServiceDefinition
     */
    public function processClass($clazz)
    {
        $reflClass = $clazz;
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
        $serviceDef->setScope($serviceAnnotation->scope);
         $serviceDef->setClassName($reflClass->getName());

        $isConstructorFound = false;
        foreach ($reflClass->getMethods() as $reflMethod) {
            /* @var Constructor $constructorAnnotation */
            $constructorAnnotation = $this->annotationReader->getMethodAnnotation($reflMethod, Constructor::clazz());
            if (!$constructorAnnotation) {
                continue;
            } else if ($isConstructorFound) {
                // TODO throw a proper exception
                throw new Exception(sprintf('Multiple constructors defined in class %s', $reflClass->getName()));
            }

            $isConstructorFound = true;
            $serviceDef->setFactoryMethod($reflMethod->getName());
            $serviceDef->setConstructorArguments(
                $this->dereferenceConstructorParams($reflMethod, $constructorAnnotation->params)
            );
        }
        
        return array(
            $serviceAnnotation->id,
            $serviceDef
        );
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

    protected function dereferenceConstructorParams(\ReflectionMethod $reflMethod, array $constructorParams)
    {
        try {
            return $this->doDereferenceConstructorParams($reflMethod, $constructorParams);
        } catch (Exception $e) {
            throw new Exception(
                sprintf(
                    'Failed to process annotations for constructor method %s::%s".',
                    $reflMethod->getDeclaringClass()->getName(),
                    $reflMethod->getName()
                )
            );
        }
    }

    /**
     * Override this method if you want to introduce some more annotations
     *
     * @param array $constructorParams
     * @return array
     */
    protected function doDereferenceConstructorParams(\ReflectionMethod $reflMethod, array $constructorParams)
    {
        $dereferencedParams = array();
        foreach ($reflMethod->getParameters() as $reflParam) {
            $dereferencedParams[$reflParam->getName()] = null;
        }
                
        /* @var \Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Parameter $param */
        foreach ($constructorParams as $param) {
            if ($param->name == null) {
                throw new Exception(
                    "Parameter 'name' is required."
                );
            }

            // TODO externalize
            $value = null;
            if ($param->qualifier != null) {
                $value = new QualifiedReference($param->qualifier);
            } else if ($param->id != null) {
                $value = new ServiceReference($param->id);
            } else {
                throw new Exception(
                    sprintf("No 'id' nor 'qualifier' is specified.", $param->name)
                );
            }
            $dereferencedParams[$param->name] = $value;
        }
        
        foreach ($dereferencedParams as $paramName=>$value) {
            if ($value === null) {
                $dereferencedParams[$paramName] = new ServiceReference($paramName);
            }
        }

        return array_values($dereferencedParams);
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
