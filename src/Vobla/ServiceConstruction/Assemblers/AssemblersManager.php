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

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\Exception;

/**
 *
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */ 
class AssemblersManager 
{
    /**
     * @var array
     */
    protected $assemblers = array();

    /**
     * @var array
     */
    protected $counters = array();

    public function __construct(array $assemblers)
    {
        if (sizeof($assemblers) == 0) {
            throw new Exception('No assemblers were provided.');
        }

        $this->assemblers = $assemblers;
    }

    public function proceed(ServiceDefinition $definition, $obj = null)
    {
        if (!is_null($obj) && !is_object($obj)) {
            throw new Exception(
                sprintf(
                    'Second parameter of %s::$s must be either an object or null value!',
                    __CLASS__, __METHOD__
                )
            );
        }

        if (null === $obj) {
            return $this->assemblers[0]->execute($this, $definition);
        } else {
            $oid = spl_object_hash($obj);
            if (!isset($this->counters[$oid])) {
                $this->counters[$oid] = 0;
            }

            if (isset($this->assemblers[$this->counters[$oid]+1])) {
                $index = ++$this->counters[$oid];
                return $this->assemblers[$index]->execute($this, $definition, $obj);
            } else {
                $this->counters[$oid] = 0;
                return $obj;
            }
        }
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
