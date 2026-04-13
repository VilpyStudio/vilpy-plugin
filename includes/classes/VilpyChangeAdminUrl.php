<?php

namespace Vilpy;

class VilpyChangeAdminUrl
{
    private $reservedSlugs = [
        'wp-admin',
        'wp-login',
        'wp-login.php',
        'admin',
        'login',
        'dashboard',
        'xmlrpc.php',
        'wp-json',
    ];
    private $bypassSiteUrlFilter = false;

    private function startsWith($haystack, $needle)
    {
        if ($needle === '') {
            return true;
        }

        return substr((string) $haystack, 0, strlen((string) $needle)) === (string) $needle;
    }

    private function getCustomSlug()
    {
        $slug = get_option('admin-url-override', 'vilpy');
        $slug = sanitize_title($slug);
        if ($slug === '' || in_array($slug, $this->reservedSlugs, true)) {
            return 'vilpy';
        }
        return $slug ?: 'vilpy';
    }

    private function getRequestPath()
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = (string) wp_parse_url($requestUri, PHP_URL_PATH);

        $homePath = (string) wp_parse_url(home_url('/'), PHP_URL_PATH);
        $homePath = trim($homePath, '/');

        if ($homePath !== '' && $this->startsWith($path, '/' . $homePath)) {
            $path = substr($path, strlen('/' . $homePath));
        }

        $path = '/' . ltrim($path, '/');

        return untrailingslashit($path) ?: '/';
    }

    private function getCustomLoginPath()
    {
        return '/' . $this->getCustomSlug();
    }

    private function shouldBypassLoginRewrite()
    {
        return $this->bypassSiteUrlFilter || !empty($_GET['vilpy-login']);
    }

    private function getRawLoginUrl(array $args = [])
    {
        $this->bypassSiteUrlFilter = true;
        $url = site_url('wp-login.php', 'login');
        $this->bypassSiteUrlFilter = false;

        if (!empty($args)) {
            $url = add_query_arg($args, $url);
        }

        return $url;
    }

    private function isCustomLoginRequest()
    {
        return $this->getRequestPath() === $this->getCustomLoginPath();
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
            if ($this->shouldBypassLoginRewrite()) {
                return $login_url;
            }

            $url = home_url('/' . $custom . '/');
            if (!empty($redirect)) {
                $url = add_query_arg('redirect_to', $redirect, $url);
            }
            return $url;
        }, 10, 3);

        // Alle site_url('wp-login.php') verwijzen naar onze slug
        add_filter('site_url', function ($url, $path, $scheme) use ($custom) {
            if ($this->shouldBypassLoginRewrite()) {
                return $url;
            }

            $normalizedPath = ltrim((string) $path, '/');
            $urlPath = (string) wp_parse_url($url, PHP_URL_PATH);
            $urlBasename = basename($urlPath);

            if ($normalizedPath === 'wp-login.php' || $urlBasename === 'wp-login.php') {
                $customUrl = home_url('/' . $custom . '/');
                $query = wp_parse_url($url, PHP_URL_QUERY);
                if (!empty($query)) {
                    $customUrl .= '?' . $query;
                }
                return $customUrl;
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

        if ($this->isCustomLoginRequest()) {
            $args = [];

            if (!empty($_GET)) {
                $args = wp_unslash($_GET);
            }

            $args['vilpy-login'] = '1';

            wp_safe_redirect($this->getRawLoginUrl($args));
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

        if (!empty($_GET['vilpy-login'])) {
            return true;
        }

        // Admin-ajax en auth-check nooit blokkeren
        if (wp_doing_ajax()) {
            return true;
        }

        $path = $this->getRequestPath();
        $post = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

        if ($path !== '/wp-login.php') {
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

        $path = $this->getRequestPath();

        // Altijd toestaan:
        if ($path === '/wp-admin/admin-ajax.php' || $path === '/wp-admin/admin-post.php') {
            return;
        }

        // Als iemand al op onze custom slug zit: niet redirecten
        if ($this->isCustomLoginRequest()) {
            return;
        }

        // /wp-login.php: alleen redirecten als het GEEN pass-through is
        if ($path === '/wp-login.php') {
            if ($this->isLoginPassThrough()) {
                // Laat WordPress z'n normale login-flow lopen
                return;
            }

            // Blok direct toegang tot wp-login.php
            wp_redirect(home_url('/'));
            exit;
        }

        // /wp-admin: als je niet ingelogd bent, redirect naar custom login met redirect_to terug naar de huidige admin-URL
        if ($path === '/wp-admin' || $this->startsWith($path, '/wp-admin/')) {
            if (!is_user_logged_in()) {
                $requestUri = $_SERVER['REQUEST_URI'] ?? '/wp-admin/';
                $target = home_url($requestUri);
                wp_safe_redirect(wp_login_url($target));
                exit;
            }
        }
    }
}
