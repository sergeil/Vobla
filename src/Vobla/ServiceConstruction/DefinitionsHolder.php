<?php

namespace Vobla\ServiceConstruction;

use Vobla\ServiceConstruction\Definition\ServiceDefinition;

/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class DefinitionsHolder 
{
    /**
     * @var array
     */
    protected $definitions = array();

    /**
     * @param  string $id
     * @param Definition\ServiceDefinition $serviceDefinition
     * @return void
     */
    public function register($id, ServiceDefinition $serviceDefinition)
    {
        $this->definitions[$id] = $serviceDefinition;
    }

    /**
     * @param  string $id
     * @return bool
     */
    public function contains($id)
    {
        return isset($this->definitions[$id]);
    }

    /**
     * @param  $id
     * @return Definition\ServiceDefinition
     */
    public function get($id)
    {
        return $this->contains($id) ? $this->definitions[$id] : nul;
    }

    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
