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

namespace Vobla\ServiceConstruction\Definition\References;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class TagsCollectionReference implements OptionalReference
{
    /**
     * @var array
     */
    private $tags = array();

    /**
     * @var string
     */
    private $stereotype;

    /**
     * @var boolean
     */
    private $isOptional;
    
    /**
     * @param array $tags
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function setStereotype($stereotype)
    {
        $this->stereotype = $stereotype;
    }

    public function getStereotype()
    {
        return $this->stereotype;
    }

    /**
     * @param boolean $isOptional
     */
    public function setOptional($isOptional)
    {
        $this->isOptional = $isOptional;
    }

    /**
     * @return boolean
     */
    public function isOptional()
    {
        return $this->isOptional;
    }

    public function __construct(array $tags, $stereotype, $isOptional = null)
    {
        $this->tags = $tags;
        $this->setStereotype($stereotype);
        $this->setOptional($isOptional);
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
