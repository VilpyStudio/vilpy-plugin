<?php

namespace Vilpy;

class SetupTestEnvironment
{

    public function setupTestEnvironment()
    {
        //For using Wordpress theme installer
        require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        require_once(ABSPATH . 'wp-admin/includes/class-theme-upgrader.php');

        /* ==========
         * Delete plugins and default things
        ============= */

        delete_plugins(['akismet/akismet.php', 'hello.php']);

        //Pages
        $defaultPage = get_page_by_title('Sample Page');
        wp_delete_post($defaultPage->ID, $bypass_trash = true);

        $privacyPolicy = get_page_by_title('Privacy Policy');
        wp_delete_post($privacyPolicy->ID, $bypass_trash = true);

        //Blogs
        $defaultPost = get_posts(['title' => 'Hello World!']);
        wp_delete_post($defaultPost[0]->ID, $bypass_trash = true);

        //Comments
        $comments = get_comments(['fields' => 'ids']);
        foreach ($comments as $comment) {
            wp_delete_comment($comment, true);
        }

        $ShopInstalled = false;
        $SiteInstalled = false;
        $ChildInstalled = false;
        //Delete all themes except for our themes if they are installed
        $themes = wp_get_themes();
        write_log($themes);
        foreach ($themes as $theme) {
            $name = $theme->name;
            $folder = $theme->stylesheet;
            if ('VILPY Webshop' === $name) {
                $ShopInstalled = true;
            } elseif ('VILPY Website' === $name) {
                $SiteInstalled = true; 
            } elseif ('Vilpy Child' === $name) {
                $ChildInstalled = true;
            } else {
                delete_theme($folder);
            }
        }

        //Install our theme based on website/webshop choice
        $siteOrShop = $_POST['shoporweb'];
        $Upgrader = new \Theme_Upgrader();

        $theme = '';
        $themeName = '';
        if ($siteOrShop === 'shop') {
            $theme = 'webshop';
            $themeName = 'VILPY Webshop';
        } else {
            $theme = 'websites';
            $themeName = 'VILPY Website';
        }

        if (!$ShopInstalled && $siteOrShop === 'shop' || !$SiteInstalled && $siteOrShop === 'site' || !$ChildInstalled) {
            $Upgrader->install('https://vilpywebsites.nl/wp-content/vilpy/vilpy-' . $theme . '-theme.zip');

            //Always install child
            if (!$ChildInstalled) {
                $Upgrader->install("https://vilpywebsites.nl/wp-content/vilpy/vilpy-child.zip");
            }

            //Rename folders after installation
            $themeRoot = get_theme_root($themeName);
            rename($themeRoot . "/vilpy-theme-main", $themeRoot . "/vilpy-theme");

            $childRoot = get_theme_root('Vilpy Child');
            rename($childRoot . "/vilpy-child-main", $childRoot . "/vilpy-child");

            //activate theme
            switch_theme("vilpy-child");
        }

        //Set default options
        update_option('timezone_string', 'Europe/Amsterdam'); //Timezone
        update_option('blog_public', 0); //Search engine visibility
        update_option('default_pingback_flag', ''); //Probeer elk ander blog gelinkt in dit bericht een melding te sturen
        update_option('show_avatars', ''); //Avatars weergeven
        update_option('permalink_structure', '/%postname%/'); //Permalink structure
        update_option('default_comment_status', ''); //Permalink structure

        wp_die();
    }
}