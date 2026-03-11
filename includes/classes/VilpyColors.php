<?php

namespace Vilpy;

class VilpyColors
{
    public function registerVilpyColors()
    {
        $path = plugins_url('/assets/css/vilpytheme.css', PLUGINROOT);
        wp_admin_css_color(
            'vilpywp',
            __('VilpyWP'),
            $path,
            ['#061b17', '#fff', '#f18412' , '#1d506d']
        );
    }

    public function setDefault()
    {
        return 'vilpywp';
    }
}
