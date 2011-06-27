<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Doctrine\Common\Annotations\AnnotationReader,
    Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class GeneralAttributesProcessor extends AbstractProcessor
{
    /**
     * {@inheritdoc}
     */
    public function handle(AnnotationReader $annotationReader, \ReflectionClass $reflClass, ServiceDefinition $sd)
    {
        $serviceAnnotation = $annotationReader->getClassAnnotation($reflClass, Service::clazz());

        $aib = $serviceAnnotation->isAbstract;
        $isAbstract = is_bool($aib) ? $aib : $aib == 'true';

        $sd->setAbstract($isAbstract);
        $sd->setScope($serviceAnnotation->scope);
        $sd->setClassName($reflClass->getName());
        $sd->setQualifier($serviceAnnotation->qualifier);
    }
}
