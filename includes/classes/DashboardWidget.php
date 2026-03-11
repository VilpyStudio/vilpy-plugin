<?php

namespace Vilpy;

class DashboardWidget
{
    public function removeDefaultCB()
    {
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');   // Right Now
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // Recent Comments
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');  // Incoming Links
        remove_meta_box('dashboard_plugins', 'dashboard', 'normal');   // Plugins
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');  // Quick Press
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');  // Recent Drafts
        remove_meta_box('dashboard_primary', 'dashboard', 'side');   // WordPress blog
        remove_meta_box('dashboard_secondary', 'dashboard', 'side');   // Other WordPress News
        remove_meta_box('dashboard_activity', 'dashboard', 'side');
        remove_meta_box('wp_mail_smtp_reports_widget_lite', 'dashboard', 'normal');
        remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
    }

    public function removeWelcomePanel()
    {
        remove_action('welcome_panel', 'wp_welcome_panel');
    }

    public function removeDash()
    {
        ?>
            <style>
                .meta-box-sortables {
                    display: none !important;
                }

                .welcome-panel-close {
                    display: none !important;
                }
            </style>
        <?php
    }

    public function loadCSS()
    {
        $path = plugins_url('/assets/css/main.css', PLUGINROOT);
        wp_enqueue_style('vilpy-css', $path);
    }

    public function vilpyWelcomeWidget()
    {
        $screen = get_current_screen();
        if ($screen->base == 'dashboard') {
            require PLUGINDIR . '/templates/admin/vilpy-welcome-widget.php';
        }
    }
}
