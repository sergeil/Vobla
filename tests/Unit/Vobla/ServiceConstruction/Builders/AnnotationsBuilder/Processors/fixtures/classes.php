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

namespace Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Processors;

/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service()
 */
class ClassWithLocalFactoryMethod
{
    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor(
     *     params={
     *         @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Parameter(name="aService", qualifier="fooQfr"),
     *         @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Parameter(name="cService", id="megaCService")
     *     }
     * )
     */
    public function fooFactory($aService, $bService, $cService)
    {

    }

    static public function clazz()
    {
        return get_called_class();
    }
}

/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service
 */
class ClassWithTwoConstructors
{
    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor
     */
    public function factory1()
    {

    }

    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Constructor
     */
    public function factory2()
    {

    }

    static public function clazz()
    {
        return get_called_class();
    }
}


// ---

/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service(
 *   id="someFooServiceId",
 *   qualifier="someQualifier",
 *   isAbstract="false",
 *   scope="fooScope"
 * )
 */
class ClassWithAllGeneralProperties
{
    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}

// ---



class SomeBarService
{
    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired
     */
    public $ref5x;
}

/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service(id="someFooServiceId")
 */
class SomeFooService extends SomeBarService
{
    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired
     */
    protected $ref4x;

    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired(id="someRef3xService")
     */
    protected $ref3x;
}

/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service(id="someDumbServiceId", scope="fooScope", qualifier="fooQualifier")
 */
class SomeDumbService extends SomeFooService
{
    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired
     */
    protected $ref1x;

    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired(id="barbaz")
     */
    protected $ref2x;

    /**
     * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Autowired(qualifier="booz", id="bazbar")
     */
    protected $ref3x;

    static public function clazz()
    {
        return get_called_class();
    }
}



/**
 * @Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations\Service
 */
class ClassWithNoId
{
    static public function clazz()
    {
        return get_called_class();
    }
}