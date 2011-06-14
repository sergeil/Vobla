<?php
 
/**
 * @copyright 2011 Modera Foundation
 * @author Sergei Lissovski <sergei.lissovski@modera.net>
 */ 
interface ContextScopeHandler
{
    public function isRegisterResponsible($id, $obj);

    public function isDispenseResponsible($id);
}
