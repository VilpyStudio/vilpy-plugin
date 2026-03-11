<?php

namespace Vilpy;

class AutoPluginInstaller
{
    public function installThemes()
    {
        require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        require_once(ABSPATH . 'wp-admin/includes/class-theme-upgrader.php');

        $Upgrader = new \Theme_Upgrader();

        $theme = $_POST['theme'];

        $themeName = 'VILPY Website';
        $themeSlug = 'websites';
        if ($theme === 'webshop-theme') {
            $themeName = 'VILPY Webshop';
            $themeSlug = 'webshop';
        }

        if ($theme === 'vilpy-child') {
            $Upgrader->install("https://vilpywebsites.nl/wp-content/vilpy/vilpy-child.zip");
        } elseif ($theme === 'vilpy-shoptimizer') {
            $Upgrader->install('https://vilpywebsites.nl/wp-content/vilpy/shoptimizer.zip');
        } elseif ($theme === 'vilpy-shoptimizerchild') {
            $Upgrader->install('https://vilpywebsites.nl/wp-content/vilpy/shoptimizer-child-theme.zip');
        } //else {
        // Remove webshop theme
        //       $Upgrader->install('https://vilpywebsites.nl/wp-content/vilpy/vilpy-' . $themeSlug . '-theme.zip');
        // }

        //Rename folders after installation
        $themeRoot = get_theme_root($themeName);
        rename($themeRoot . "/vilpy-theme-main", $themeRoot . "/vilpy-theme");

        $childRoot = get_theme_root('Vilpy Child');
        rename($childRoot . "/vilpy-child-main", $childRoot . "/vilpy-child");

        wp_die();
    }

    public function installPlugins()
    {
        //Post data comes from admin post hook
        $data = $_POST;
        $this->registerPlugins($data['plugins']);
        wp_die();
    }

    private function registerPlugins($data)
    {
        // Include required libs for installation
        require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
        require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        require_once(ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php');
        require_once(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php');


        $api = plugins_api(
            'plugin_information',
            [
                'slug' => $data,
                'fields' => [
                    'short_description' => false,
                    'sections' => false,
                    'requires' => false,
                    'rating' => false,
                    'ratings' => false,
                    'downloaded' => false,
                    'last_updated' => false,
                    'added' => false,
                    'tags' => false,
                    'compatibility' => false,
                    'homepage' => false,
                    'donate_link' => false
                ],
            ]
        );
        if (!is_wp_error($api)) {
            $instalURL = $api->download_link;
        } else {
            $pluginName = $data;
            $fileURL = 'https://vilpywebsites.nl/wp-content/vilpy/' . $pluginName . '.zip';
            $instalURL = $fileURL;
        }
        write_log($instalURL);
        $skin = new \WP_Ajax_Upgrader_Skin();
        $upgrader = new \Plugin_Upgrader($skin);
        $installed = $upgrader->install($instalURL);
        if ($installed) {
            echo 'success';
        } else {
            write_log($installed);
            write_log('ERROR VILPY PLUGIN INSTALLATION: Plugin cannot be found');
            echo 'error';
        }
    }

    public function activatePlugins()
    {
        //We don't gather the selected checkboxes here, because only the plugins that are installed can be activated
        require_once(ABSPATH . 'wp-load.php');
        require_once(ABSPATH . 'wp-admin/includes/admin.php');
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $plugins = pluginsToAutoInstall(); //Found in helpers

        $urls = [];
        foreach ($plugins as $name => $data) {
            activate_plugin("{$data['folder']}/{$data['filename']}.php");
        }
        write_log('INSTALLED');
        wp_die();
    }
}
