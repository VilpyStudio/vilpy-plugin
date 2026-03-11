<?php

namespace Vilpy;

class HhCache
{

    public function clearCfOnSave()
    {
        $posttypes = get_option('clear-cloudflare-on-save');
        
        //comma separated list of post types
        $posttypes = explode(',', $posttypes);

        //Add hook for each post type
        foreach ($posttypes as $posttype) {
            add_action('save_post_' . $posttype, array($this, 'clearCfCache'));
        }
    }

    public function clearLsOnSave()
    {
        $posttypes = get_option('clear-litespeed-on-save');
        
        //comma separated list of post types
        $posttypes = explode(',', $posttypes);

        //Add hook for each post type
        foreach ($posttypes as $posttype) {
            add_action('save_post_' . $posttype, array($this, 'clearLsCache'));
        }
    }

    public function clearCfCache($postid)
    {

        //If postid is not set use the filter_input
        if (!$postid) {
            $urlToClear = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);
        } else {
            $urlToClear = get_permalink($postid);
        }

        // Url must be a string and include the current domain
        if (!is_string($urlToClear) || !str_contains($urlToClear, $_SERVER['HTTP_HOST'])) {
            return;
        }

        $zoneId = get_option('client-cloudflare-zone');
        $url = "https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            "Authorization: Bearer " .  CLOUDFLARE_API_KEY,
            "Content-Type: application/json",
        ];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $fields = json_encode(['files' => [$urlToClear]]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        // If response is not 200, handle error
        if ($httpcode != 200) {
            print_r('Er is iets misgegaan bij het legen van de cache, probeer het later nog eens. RES: ' . $httpcode . ' ' . $response);
            die;
        }

        if (!$postid) {
            wp_redirect($urlToClear);
        }
    }

    public function clearCache()
    {
        $urlToClear = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);
        $postid = url_to_postid($urlToClear);
        $this->clearLsCache($postid);
        $this->clearCfCache($postid);
        wp_redirect($urlToClear);
    }

    public function clearLsCache($postid)
    {
        //If postid is not set use the filter_input
        if (!$postid) {
            $url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL);
        } else {
            $url = get_permalink($postid);
        }

        //Url must be a string and include the current domain
        if (!is_string($url) || !str_contains($url, $_SERVER['HTTP_HOST'])) {
            return;
        }

        do_action('litespeed_purge_url', $url);
        //redirect back to the page
        if (!$postid) {
            wp_redirect($url);
        }
    }
}
