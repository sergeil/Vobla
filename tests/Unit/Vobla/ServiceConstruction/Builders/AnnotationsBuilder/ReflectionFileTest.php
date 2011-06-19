<?php

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder;

require_once __DIR__.'/../../../../../bootstrap.php';

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class ReflectionFileTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClassNameAndGetNamespace()
    {
        $pathname = realpath(__DIR__.'/fixtures/GoodClass.php');
        $rf = new ReflectionFile(file_get_contents($pathname));

        $this->assertEquals(
            'GoodClass',
            $rf->getClassName(),
            "Unable to extract class-name from '$pathname' file."
        );
        $this->assertEquals(
            'Vobla\ServiceConstruction\Builders\AnnotationsBuilder\AnnotationsBuilder',
            $rf->getNamespace(),
            "Unable to extract namespace from '$pathname' file"
        );
    }
}
