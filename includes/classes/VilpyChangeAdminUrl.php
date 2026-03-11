<?php

namespace Vilpy;

class VilpyChangeAdminUrl
{
    private function getCustomSlug()
    {
        $slug = get_option('admin-url-override', 'vilpy');
        $slug = sanitize_title($slug);
        return $slug ?: 'vilpy';
    }
    
    private function perfmattersManagesLogin()
    {
        if (defined('PERFMATTERS_VERSION')) {
            $pm = get_option('perfmatters_options');

            if (isset($pm['login_url']) && !empty($pm['login_url'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Zorg dat alle login-links/form actions naar onze custom slug gaan.
     * Call dit vroeg (plugins_loaded of init priority 0).
     */
    public function registerLoginFilters()
    {
        if ($this->perfmattersManagesLogin()) return;

        $custom = $this->getCustomSlug();

        // login_url() -> /{custom}
        add_filter('login_url', function ($login_url, $redirect, $force_reauth) use ($custom) {
            $url = home_url('/' . $custom . '/');
            if (!empty($redirect)) {
                $url = add_query_arg('redirect_to', rawurlencode($redirect), $url);
            }
            return $url;
        }, 10, 3);

        // Alle site_url('wp-login.php') verwijzen naar onze slug
        add_filter('site_url', function ($url, $path, $scheme) use ($custom) {
            if ((string)$path === 'wp-login.php' || str_contains($url, 'wp-login.php')) {
                return home_url('/' . $custom . '/');
            }
            return $url;
        }, 10, 3);
    }

    /**
     * Render wp-login.php op /{custom}
     */
    public function changeAdminUrl()
    {
        if ($this->perfmattersManagesLogin()) return;

        $custom      = $this->getCustomSlug();
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';

        if (preg_match('#/' . preg_quote($custom, '#') . '(/|$|\?)#', $request_uri)) {
            global $error, $interim_login, $user_login, $pagenow;

            $error         = $error         ?? '';
            $interim_login = $interim_login ?? false;
            $user_login    = $user_login    ?? (isset($_POST['log']) ? sanitize_user(wp_unslash($_POST['log'])) : '');
            $pagenow       = 'wp-login.php'; // voor scripts/styles die hierop leunen

            // Belangrijk: NIET meer $_SERVER['REQUEST_URI'] faken.
            require_once ABSPATH . 'wp-login.php';
            exit;
        }
    }

    /**
     * Sta /wp-login.php toe als pass-through wanneer het een echte login flow is (bv POST),
     * maar block/redirect in alle andere gevallen.
     */
    private function isLoginPassThrough()
    {
        // Interim login is altijd toegestaan
        if (!empty($_GET['interim-login'])) {
            return true;
        }

        // Admin-ajax en auth-check nooit blokkeren
        if (wp_doing_ajax()) {
            return true;
        }

        $uri  = $_SERVER['REQUEST_URI'] ?? '';
        $post = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

        // let op, query strings zorgen voor een false
        $hits_wp_login = (strpos($uri, 'wp-login.php') !== false);

        if (!$hits_wp_login) {
            return false;
        }

        if ($post) {
            return true;
        }

        $action = $_GET['action'] ?? '';

        if (in_array($action, [
            'login',
            'logout',
            'lostpassword',
            'rp',
            'resetpass',
            'postpass',
            'register',
            'reauth'
        ], true)) {
            return true;
        }

        return false;
    }


    public function blockDefaultLogin()
    {
        if ($this->perfmattersManagesLogin()) return;

        $uri = $_SERVER['REQUEST_URI'] ?? '';

        // Altijd toestaan:
        if (strpos($uri, '/admin-ajax.php') !== false || strpos($uri, '/admin-post.php') !== false) {
            return;
        }

        // Als iemand al op onze custom slug zit: niet redirecten
        if (strpos($uri, '/' . $this->getCustomSlug() . '/') !== false) {
            return;
        }

        // /wp-login.php: alleen redirecten als het GEEN pass-through is
        if (strpos($uri, '/wp-login.php') !== false) {
            if ($this->isLoginPassThrough()) {
                // Laat WordPress z'n normale login-flow lopen
                return;
            }

            // Blok direct toegang tot wp-login.php
            wp_redirect(home_url('/'));
            exit;
        }

        // /wp-admin: als je niet ingelogd bent, redirect naar custom login met redirect_to terug naar de huidige admin-URL
        if (preg_match('#/wp-admin/?($|\?)#', $uri)) {
            if (!is_user_logged_in()) {
                $target = home_url($uri); // volledige URL voor redirect_to
                wp_redirect(wp_login_url($target)); // wp_login_url wordt al naar /{custom}/ gefilterd
                exit;
            }
        }
    }
}
