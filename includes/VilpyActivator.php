<?php

/**
 * The code that runs during activation
 */

namespace Vilpy;

class VilpyActivator
{
    public static function activate()
    {
        //Is not loaded yet, because it loads on init so we need to require it here
        require_once 'classes/VilpyRole.php';
        require_once 'classes/vilpy-instellingen/OptimizationsPage.php';

        VilpyRole::add();
        OptimizationsPage::setDefaults();
    }
}
