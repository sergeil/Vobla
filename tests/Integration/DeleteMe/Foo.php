<?php
 
require_once __DIR__.'/../../bootstrap.php';

require_once 'Stuff/MegaClass.php';
require_once 'Stuff/Service.php';
require_once 'Stuff/Orm.php';

$ar = new \Doctrine\Common\Annotations\AnnotationReader();
$an = $ar->getClassAnnotation(new ReflectionClass('Testing\MegaClass'), 'Testing\Service');

echo "<pre>";
print_r($an);
echo "</pre>";