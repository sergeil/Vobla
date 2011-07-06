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

namespace Vobla;

/**
 * Class is responsible of holding domain-related configuration, most of the time
 * it is going to be related to your business-logic rather than to the container's
 * settings.
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class ConfigHolder 
{
    /**
     * @var array
     */
    protected $storage = array();

    /**
     * @throws Vobla\Exception  When $value is an object
     * @param string $name
     * @param scalar $value
     */
    public function set($name, $value)
    {
        if (is_object($value)) {
            $msg = 'Configuration properties should be only scalars or arrays, but';
            $msg .= ' it was tried to initialize a property "%s" with an object.';
            $msg = sprintf($msg, $name);
            throw new Exception($msg);
        }

        $this->storage[$name] = $value;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->storage);
    }

    public function get($name)
    {
        return $this->has($name) ? $this->storage[$name] : null;
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
