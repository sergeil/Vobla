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

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations as Vobla;

/**
 * @Vobla\Service()
 */
class ClassWithLocalFactoryMethod
{
    /**
     * @Vobla\Constructor(
     *     params={
     *         @Vobla\Parameter(name="aService", as=@Vobla\Autowired(qualifier="fooQfr")),
     *         @Vobla\Parameter(name="cService", as=@Vobla\Autowired(id="megaCService")),
     *         @Vobla\Parameter(name="dService", as=@Vobla\AutowiredSet(tags={"fooTag"})),
     *         @Vobla\Parameter(name="eConfig", as=@Vobla\ConfigProperty("eConfigProperty")),
     *     }
     * )
     */
    public function fooFactory($aService, $bService, $cService, $dService, $eConfig)
    {
        
    }

    static public function clazz()
    {
        return get_called_class();
    }
}

/**
 * @Vobla\Service
 */
class ClassWithTwoConstructors
{
    /**
     * @Vobla\Constructor
     */
    public function factory1()
    {

    }

    /**
     * @Vobla\Constructor
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
 * @Vobla\Service(
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
     * @Vobla\Autowired
     */
    public $ref5x;
}

/**
 * @Vobla\Service(id="someFooServiceId")
 */
class SomeFooService extends SomeBarService
{
    /**
     * @Vobla\Autowired
     */
    protected $ref4x;

    /**
     * @Vobla\Autowired(id="someRef3xService")
     */
    protected $ref3x;
}

/**
 * @Vobla\Service(id="someDumbServiceId", scope="fooScope", qualifier="fooQualifier")
 */
class SomeDumbService extends SomeFooService
{
    /**
     * @Vobla\Autowired
     */
    protected $ref1x;

    /**
     * @Vobla\Autowired(id="barbaz")
     */
    protected $ref2x;

    /**
     * @Vobla\Autowired(qualifier="booz", id="bazbar")
     */
    protected $ref3x;

    static public function clazz()
    {
        return get_called_class();
    }
}



/**
 * @Vobla\Service
 */
class ClassWithNoId
{
    static public function clazz()
    {
        return get_called_class();
    }
}

/**
 * @Vobla\Service
 * @Vobla\Tag("fooTag")
 * @Vobla\Tag("barTag")
 */
class ClassWithTags
{
    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}

/**
 * @Vobla\Service
 * @Vobla\Tag("foo-fooTag")
 * @Vobla\Tag("bar-barTag")
 */
class AnotherClassWithTags extends ClassWithTags
{

}

/**
 * @Vobla\Qualifier("fooQualifier")
 */
class ClassWithQualifier
{
    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}

// ----

/**
 * @Vobla\Service
 */
class AutowiringClass
{
    /**
     * @Vobla\Autowired(
     *     id="fooId",
     *     qualifier="fooQualifier",
     *     type="fooType",
     *     tag="fooTag"
     * )
     */
    protected $foo;

    /**
     * @Vobla\AutowiredSet(
     *     type="fooType",
     *     tags={"fooTag1", "fooTag2"}
     * )
     */
    protected $fooSet;

    /**
     * @Vobla\AutowiredMap(
     *     type="fooType",
     *     tags={"fooTag1", "fooTag2"}
     * )
     */
    protected $fooMap;

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}

/**
 * @Vobla\Service
 */
class GeneralizedAutowiringClass extends AutowiringClass
{
    /**
     * @Vobla\Autowired(
     *     id="barId",
     *     qualifier="barQualifier",
     *     type="barType",
     *     tag="barTag"
     * )
     */
    protected $bar;

    /**
     * @Vobla\AutowiredSet(
     *     type="barType",
     *     tags={"barTag1", "barTag2"}
     * )
     */
    protected $barSet;

    /**
     * @Vobla\AutowiredMap(
     *     type="barType",
     *     tags={"barTag1", "barTag2"}
     * )
     */
    protected $barMap;

    /**
     * @Vobla\ConfigProperty(
     *     name="fooCfgProp"
     * )
     */
    protected $fooCfg;
}

/**
 * @Vobla\Service
 */
class ConfigPropertyAndAutowiredMixed
{
    /**
     * @Vobla\AutowiredSet(type="barType")
     * @Vobla\ConfigProperty(name="blah")
     */
    protected $fooProp;

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}

/**
 * @Vobla\NotByTypeWiringCandidate
 */
class ClassWithNotByTypeWiringCandidateAnnotation
{
    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}

class ClassWithoutNotByTypeWiringCandidate
{
    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}

// ----

/**
 * @Vobla\Service
 */
class ClassWithSomeConfig
{
    /**
     * @Vobla\ConfigProperty(name="fooProp")
     */
    protected $fooField;

    /**
     * @Vobla\ConfigProperty(name="barProp", isOptional=false)
     */
    protected $barField;

    protected $bazField;

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}

