<?php

namespace Vilpy;

class LoadFrontendStyleScript
{
    /**
     * Frontend-fixes en adminbalk-kleuren. Elementor-pijlen alleen waar die optie aanstaat.
     */
    public function enqueueMainStyle()
    {
        // Fixes voor iconenlijsten en selectvelden: voor iedereen
        wp_enqueue_style('vilpy-front-end-style', plugins_url('/templates/assets/css/frontend.css', PLUGINROOT), [], \vilpy_asset_ver('/templates/assets/css/frontend.css'));

        // Adminbalk in Vilpy-kleuren: alleen als die zichtbaar is
        if (is_admin_bar_showing()) {
            wp_enqueue_style('vilpy-adminbar', plugins_url('/templates/assets/css/adminbar.css', PLUGINROOT), [], \vilpy_asset_ver('/templates/assets/css/adminbar.css'));
        }
    }

    public function enqueueElementorStyle()
    {
        if (get_option('elementor-arrows')) {
            wp_enqueue_style('vilpy-elementor-fixes', plugins_url('/templates/assets/css/frontendfixes.css', PLUGINROOT), [], \vilpy_asset_ver('/templates/assets/css/frontendfixes.css'));
        }
    }
}
