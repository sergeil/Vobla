<?php
 
/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
class Constructor 
{
    public $isAbstract = false;

    public $args = array();

    static public function clazz()
    {
        return get_called_class();
    }
}
