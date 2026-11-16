<?php

namespace Vilpy;

class DashboardWidget
{
    /**
     * Standaard WordPress-widgets weg voor klanten. Vilpy-medewerkers
     * (zie vilpy_is_staff()) houden het volledige dashboard.
     */
    public function removeDefaultWidgets()
    {
        if (vilpy_is_staff()) {
            return;
        }

        foreach ([
            ['dashboard_right_now', 'normal'],
            ['dashboard_activity', 'normal'],
            ['dashboard_site_health', 'normal'],
            ['dashboard_quick_press', 'side'],
            ['dashboard_primary', 'side'],
        ] as [$id, $context]) {
            remove_meta_box($id, 'dashboard', $context);
        }
    }

    public function removeWelcomePanel()
    {
        remove_action('welcome_panel', 'wp_welcome_panel');
    }

    public function loadCSS($hook)
    {
        if (!in_array($hook, ['index.php', 'settings_page_vilpy'], true)) {
            return;
        }
        wp_enqueue_style('vilpy-admin', plugins_url('/assets/css/admin.css', PLUGINROOT), [], \vilpy_asset_ver('/assets/css/admin.css'));
    }

    public function vilpyWelcomeWidget()
    {
        $screen = get_current_screen();
        if ($screen && $screen->base === 'dashboard') {
            require PLUGINDIR . '/templates/admin/vilpy-welcome-widget.php';
        }
    }
}
