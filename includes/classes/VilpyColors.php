<?php

namespace Vilpy;

class VilpyColors
{
    public function registerVilpyColors()
    {
        wp_admin_css_color(
            'vilpywp',
            __('Vilpy', 'vilpy'),
            add_query_arg('v', \vilpy_asset_ver('/assets/css/vilpytheme.css'), plugins_url('/assets/css/vilpytheme.css', PLUGINROOT)),
            ['#061B17', '#0C201B', '#1F6B4E', '#1B2B27']
        );
    }

    public function setDefault()
    {
        return 'vilpywp';
    }
}
