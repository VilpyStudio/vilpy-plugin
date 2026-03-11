<?php

if (!function_exists('themeTextDomain')) {
    function themeTextDomain()
    {
        return 'vilpy';
    }
}

if (!function_exists('hh_brand_name')) {
    function hh_brand_name()
    {
        return defined('HH_BRAND_NAME') ? HH_BRAND_NAME : 'Vilpy';
    }
}

if (!function_exists('hh_brand_subtitle')) {
    function hh_brand_subtitle()
    {
        return defined('HH_BRAND_SUBTITLE') ? HH_BRAND_SUBTITLE : 'Vilpy';
    }
}

if (!function_exists('hh_default_logo')) {
    function hh_default_logo()
    {
        return plugins_url('/assets/img/vilpy-logo-dark.svg', Vilpy\PLUGINROOT);
    }
}

if (!function_exists('hh_default_login_logo')) {
    function hh_default_login_logo()
    {
        return plugins_url('/assets/img/vilpy-logo-login.svg', Vilpy\PLUGINROOT);
    }
}

if (!function_exists('hh_support_phone')) {
    function hh_support_phone()
    {
        return defined('HH_SUPPORT_PHONE') ? HH_SUPPORT_PHONE : '';
    }
}

if (!function_exists('hh_support_email')) {
    function hh_support_email()
    {
        return defined('HH_SUPPORT_EMAIL') ? HH_SUPPORT_EMAIL : '';
    }
}

if (!function_exists('hh_default_welcome_text')) {
    function hh_default_welcome_text($isWooCommerce = false)
    {
        $subject = $isWooCommerce ? 'webshop' : 'website';
        $text = 'Welkom in het dashboard van je ' . $subject . '. Hier kun je de belangrijkste inhoud beheren.';

        $phone = hh_support_phone();
        $mail = hh_support_email();
        if ($phone && $mail) {
            $text .= ' Heb je vragen? <a href="tel:' . esc_attr($phone) . '">Bel</a> of <a href="mailto:' . esc_attr($mail) . '">mail</a> support.';
        }

        return $text;
    }
}

if (!function_exists('dd')) {
    function dd($var, $die = true)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        $die && die();
    }
}

if (!function_exists('callAPI')) {
    function callAPI($method, $url, $data = false, $headers = false, $statuscode = false)
    {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }
    // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        if (!$headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));
        } else {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // EXECUTE:
        $result = curl_exec($curl);
        if (!$result) {
            return false;
        }
        curl_close($curl);

        $alldata = [];
        if ($statuscode) {
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $alldata[] = $httpcode;
            $alldata[] = $result;
            return $alldata;
        }
        return $result;
    }

    if ( ! function_exists('write_log')) {
    function write_log ($log, $vardump = false)  {
        
            if ($vardump) {
                error_log(var_dump($log));
            }

        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
    }
}

if ( ! function_exists('pluginInstalled')) {
    function pluginInstalled( $plugin_slug ) {
        $installed_plugins = get_plugins();
        return array_key_exists( $plugin_slug, $installed_plugins ) || in_array( $plugin_slug, $installed_plugins, true );
    }
}

if ( ! function_exists('themesInstalled')) {
    function themesInstalled( $theme_slug ) {
        $installed_themes = get_themes();
        return array_key_exists( $theme_slug, $installed_themes ) || in_array( $theme_slug, $installed_themes, true );
    }
}

if (!function_exists('pluginsToAutoInstall')) {
    function pluginsToAutoInstall() {
        return [
        'Elementor Pro'
                => ['slug' => 'elementor-pro',
                    'folder' => 'elementor-pro',
                    'filename' => 'elementor-pro'],
        'Elementor'
                => ['slug' => 'elementor',
                    'folder' => 'elementor',
                    'filename' => 'elementor'],
        'Fluent SMTP'
                => ['slug' => 'fluent-smtp',
                    'folder' => 'fluent-smtp',
                    'filename' => 'fluent-smtp'],
        'Cleantalk Spam Protect'
                => ['slug' => 'cleantalk-spam-protect',
                    'folder' => 'cleantalk-spam-protect',
                    'filename' => 'cleantalk'],
        'Litespeed Cache'
                => ['slug' => 'litespeed-cache',
                    'folder' => 'litespeed-cache',
                    'filename' => 'litespeed-cache'],
        'ManageWP worker'
                => ['slug' => 'worker',
                    'folder' => 'worker',
                    'filename' => 'init'],
        'Perfmatters'
                => ['slug' => 'perfmatters',
                    'folder' => 'perfmatters',
                    'filename' => 'perfmatters'],
        'Rank Math'
                => ['slug' => 'seo-by-rank-math',
                    'folder' => 'seo-by-rank-math',
                    'filename' => 'rank-math'],
        'Rank Math Pro'
                => ['slug' => 'rank-math-pro',
                    'folder' => 'seo-by-rank-math-pro',
                    'filename' => 'rank-math-pro'],
        'Yoast Duplicate Post'
                => ['slug' => 'duplicate-post',
                    'folder' => 'duplicate-post',
                    'filename' => 'duplicate-post']
        ];
    }
}
