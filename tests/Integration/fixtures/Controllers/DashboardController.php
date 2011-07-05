<?php

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations as Vobla;

/**
 * @Vobla\Service(id="dashboardController")
 */
class DashboardController implements Controller
{
    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
