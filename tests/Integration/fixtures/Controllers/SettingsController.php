<?php

use Vobla\ServiceConstruction\Builders\AnnotationsBuilder\Annotations as Vobla;

/**
 * @Vobla\Service(id="settingsController")
 */
class SettingsController implements Controller
{
    /**
     * @return string
     */
    static public function clazz()
    {
        return get_called_class();
    }
}
