<?php

/**
 * Draait bij activeren van de plugin.
 */

namespace Vilpy;

class VilpyActivator
{
    public static function activate()
    {
        require_once 'classes/vilpy-instellingen/OptimizationsPage.php';
        OptimizationsPage::setDefaults();
    }
}
