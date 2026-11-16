<?php

/**
 * Algemene helpers van de Vilpy-plugin.
 */

if (!function_exists('vilpy_version')) {
    function vilpy_version()
    {
        static $version = null;
        if ($version === null) {
            $data = get_file_data(Vilpy\PLUGINROOT, ['Version' => 'Version']);
            $version = $data['Version'] ?: '0';
        }
        return $version;
    }
}

if (!function_exists('vilpy_brand_name')) {
    function vilpy_brand_name()
    {
        return 'Vilpy';
    }
}

if (!function_exists('vilpy_default_logo')) {
    function vilpy_default_logo()
    {
        return plugins_url('/assets/img/vilpy-logo-dark.svg', Vilpy\PLUGINROOT);
    }
}

if (!function_exists('vilpy_default_login_logo')) {
    function vilpy_default_login_logo()
    {
        return plugins_url('/assets/img/vilpy-logo-login.svg', Vilpy\PLUGINROOT);
    }
}

if (!function_exists('vilpy_default_welcome_text')) {
    function vilpy_default_welcome_text()
    {
        $subject = class_exists('WooCommerce') ? 'webshop' : 'website';
        return 'Welkom in het dashboard van je ' . $subject . '. Hier kun je de belangrijkste inhoud beheren.';
    }
}

/**
 * Is de huidige gebruiker een Vilpy-medewerker? Standaard: e-mailadres op
 * vilpy.nl of een gebruikersnaam met "vilpy". Aan te passen met de filter 'vilpy_is_staff'.
 */
if (!function_exists('vilpy_is_staff')) {
    function vilpy_is_staff($user = null)
    {
        $user = $user ?: wp_get_current_user();
        $staff = $user && $user->exists() && (
            preg_match('/@vilpy\.nl$/i', (string) $user->user_email)
            || stripos((string) $user->user_login, 'vilpy') !== false
        );
        return (bool) apply_filters('vilpy_is_staff', $staff, $user);
    }
}

/**
 * Versie voor een plugin-bestand op basis van de wijzigingstijd (cache-busting).
 */
if (!function_exists('vilpy_asset_ver')) {
    function vilpy_asset_ver($relative)
    {
        $file = Vilpy\PLUGINDIR . '/' . ltrim($relative, '/');
        return file_exists($file) ? (string) filemtime($file) : vilpy_version();
    }
}
