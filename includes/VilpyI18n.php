<?php

/**
 *  Define internationlization for plugin so it's ready
 */

namespace Vilpy;

class VilpyI18n
{
    public function loadDomain()
    {
        load_textdomain('vilpy', PLUGINDIR . '/languages/vilpy-nl_NL.mo');
    }
}
