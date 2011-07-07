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

namespace Vobla\ServiceConstruction\Builders;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class InjectorsOrderResolver 
{
    protected $byIdCallback;
    protected $byQualifierCallback;
    protected $byTagCallback;
    protected $byTypeCallback;
    
    public function setByIdCallback($byIdCallback)
    {
        $this->byIdCallback = $byIdCallback;
    }

    public function getByIdCallback()
    {
        return $this->byIdCallback;
    }

    public function setByQualifierCallback($byQualifierCallback)
    {
        $this->byQualifierCallback = $byQualifierCallback;
    }

    public function getByQualifierCallback()
    {
        return $this->byQualifierCallback;
    }

    public function setByTagCallback($byTagsCallback)
    {
        $this->byTagCallback = $byTagsCallback;
    }

    public function getByTagCallback()
    {
        return $this->byTagCallback;
    }

    public function setByTypeCallback($byTypeCallback)
    {
        $this->byTypeCallback = $byTypeCallback;
    }

    public function getByTypeCallback()
    {
        return $this->byTypeCallback;
    }

    final public function getPriorityPolicy() // TODO make extensible
    {
        return array_merge(array(
            'qualifier', 'tag', 'type', 'id'
        ));
    }

    /**
     * Override this method if you need to a dd your custom types to be
     * resolved.
     */
    public function getAdditionalPriorityPolicy()
    {
        return array();
    }

    /**
     * @param array $availableParams
     * @return mixed
     */
    public function resolve()
    {
        foreach ($this->getPriorityPolicy() as $type) {
            /* @var \Closure $clb */
            $methodName = 'getBy'.ucfirst($type).'Callback';
            $clb = $this->{$methodName}();

            if (!($clb instanceof \Closure)) {
                continue;
            }

            $result = $clb();
            if (null !== $result) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}