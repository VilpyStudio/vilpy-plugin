<?php

/**
 * The code that runs during deactivation
 */

namespace Vilpy;

class VilpyDeactivator
{
    public static function deactivate()
    {
        //Is not loaded yet, because it loads on init so we need to require it here
        require_once 'classes/VilpyRole.php';

        VilpyRole::remove();

        //Google api token
        delete_option('vilpy_google_accesstoken');

        //Menu items to hide
        //delete_option('vilpy_sidebar_items_tohide');
    }
}
