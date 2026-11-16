<?php

namespace Vilpy;

class ElementorOptimization
{
    /**
     * Elementor-iconfont (eicons) niet laden voor bezoekers als dat aanstaat.
     */
    public function disableIcons()
    {
        if (!get_option('remove-elementor-icons')) {
            return;
        }
        if (is_admin() || current_user_can('manage_options')) {
            return;
        }
        wp_deregister_style('elementor-icons');
    }
}
