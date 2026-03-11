<?php

namespace Vilpy;

class ElementorOptimization
{

    public function disableFont()
    {
        return false;
    }

    public function disableFontAwesome()
    {
        $removeFontAwesome = get_option('remove-fontawesome');
        if ($removeFontAwesome) {
            foreach (['solid', 'regular', 'brands'] as $style) {
                    wp_deregister_style('elementor-icons-fa-' . $style);
            }
        }
    }

    public function disableIcons()
    {
        $removeIcons =  get_option('remove-elementor-icons');
        if ($removeIcons) {
            if (is_admin() || current_user_can('manage_options')) {
                    return;
            }
            wp_deregister_style('elementor-icons');
        }
    }
}
