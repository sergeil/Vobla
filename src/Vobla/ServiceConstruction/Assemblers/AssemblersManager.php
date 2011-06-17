<?php

namespace Vobla\ServiceConstruction\Assemblers;

use Vobla\ServiceConstruction\Definition\ServiceDefinition,
    Vobla\Exception;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
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
