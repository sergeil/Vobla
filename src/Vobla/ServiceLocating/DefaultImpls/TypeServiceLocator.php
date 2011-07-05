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

namespace Vobla\ServiceLocating\DefaultImpls;

use Vobla\ServiceLocating\AbstractServiceLocator,
    Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class TypeServiceLocator extends AbstractServiceLocator
{
    static public function createCriteria($type)
    {
        return "byType:$type";
    }

    /**
     * @var array
     */
    protected $lookup = array();

    /**
     * {@inheritdoc}
     */
    public function analyze($id, ServiceDefinition $serviceDefinition)
    {
        $reflClass = new \ReflectionClass($serviceDefinition->getClassName());

        $types = $this->collectTypes($reflClass);

        foreach ($types as $type) {
            if (!isset($this->lookup[$type])) {
                $this->lookup[$type] = array();
            }
            $this->lookup[$type][] = $id;
        }
    }

    protected function collectTypes(\ReflectionClass $reflClass)
    {
        $result = $reflClass->getInterfaceNames();
        $result[] = $reflClass->getName();

        if ($reflClass->getParentClass()) {
            $result = array_merge($result, $this->collectTypes($reflClass->getParentClass()));
        }

        return $result;
    }

   /**
     * {@inheritdoc}
     */
    public function locate($criteria)
    {
        $matches = array();
        if (preg_match('/^byType:(.*)$/', $criteria, $matches)) {
            $type = $matches[1];
            return isset($this->lookup[$type]) ? $this->lookup[$type] : array();
        }

        return array();
    }

}
