<?php

namespace Vilpy;

/**
 * Eenmalige opschoning bij het updaten naar 0.1.0: verwijdert opties, de klantrol
 * en de oude tabel van functies die uit de plugin zijn gehaald.
 */
class VilpyUpgrade
{
    const VERSION = '0.1.0';

    public static function maybeRun()
    {
        if (version_compare((string) get_option('vilpy_db_version', '0'), self::VERSION, '>=')) {
            return;
        }

        $obsolete = [
            // Cache / Cloudflare
            'client-cloudflare-zone', 'client-cache-control', 'clear-cloudflare-on-save', 'clear-litespeed-on-save',
            // Extra-tab
            'enable-scroll-snap-support', 'enable-showcase-mode', 'showcase-mode-distance', 'showcase-mode-duration', 'showcase-mode-delay',
            // Klantrol
            'vilpy_sidebar_items_tohide',
            // WooCommerce dataLayer
            'wc-datalayer',
            // Oude optimalisaties zonder instelling
            'elementor-mega-arrows', 'remove-fontawesome', 'remove-elementor-google-fonts',
            // Tracking en iconen (nergens gebruikt) en opties die nu vast gedrag zijn
            'GTM-code', 'GA4-code', 'GSCode', 'disable-icon-css', 'disable-gutenberg', 'hide-language-selector',
            // Restanten
            'vilpy_google_accesstoken',
        ];
        foreach ($obsolete as $option) {
            delete_option($option);
        }

        // Klantrol weg (alleen als niemand hem nog heeft)
        if (get_role('vilpy_client')) {
            $users = get_users(['role' => 'vilpy_client', 'fields' => 'ID', 'number' => 1]);
            if (empty($users)) {
                remove_role('vilpy_client');
            }
        }

        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}vilpy_clientreporting");

        update_option('vilpy_db_version', self::VERSION, false);
    }
}
