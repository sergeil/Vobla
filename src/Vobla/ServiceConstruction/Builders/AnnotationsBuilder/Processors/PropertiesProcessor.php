<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\ServiceConstruction\Definition\QualifiedReference,
    Vobla\ServiceConstruction\Definition\ServiceReference;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class PropertiesProcessor implements Processor
{
    public function handle(AnnotationReader $annotationReader, \ReflectionClass $reflClass, ServiceDefinition $serviceDefinition)
    {
        $result = $serviceClasses = array();
        foreach ($reflClass->getProperties() as $reflProp) {
            $reflDeclaredClass = $reflProp->getDeclaringClass();
            if (!in_array($reflDeclaredClass->getName(), $serviceClasses)) {
                $serviceAnnotation = $annotationReader->getClassAnnotation($reflDeclaredClass, Service::clazz());
                if ($serviceAnnotation) {
                    $serviceClasses[] = $reflDeclaredClass->getName();
                }
            }

            // if a declared class doesn't have Service annotation skipping its properties
            if (!in_array($reflDeclaredClass->getName(), $serviceClasses)) {
                continue;
            }

            /* @var Annotations\Autowired $autowiredAnnotation */
            $autowiredAnnotation = $annotationReader->getPropertyAnnotation($reflProp, Autowired::clazz());
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

        $serviceDefinition->setArguments($result);
    }
}
