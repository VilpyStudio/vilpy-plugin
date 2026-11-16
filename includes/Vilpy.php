<?php

/**
 * Laadt alle hooks en filters van de plugin.
 */

namespace Vilpy;

class Vilpy
{
    protected $loader;

    public function __construct()
    {
        require_once 'VilpyLoader.php';
        require_once 'includes.php';
        $this->loader = new VilpyLoader();

        VilpyUpgrade::maybeRun();
        $this->defineHooks();
    }

    private function defineHooks()
    {
        // Instellingenpagina
        $optionsPage = new OptionsPage();
        $this->loader->addAction('admin_menu', $optionsPage, 'create', 999);
        $this->loader->addAction('admin_init', $optionsPage, 'createOptions');
        $this->loader->addAction('admin_enqueue_scripts', $optionsPage, 'loadJS');

        // Admin-uiterlijk
        $vilpyColors = new VilpyColors();
        $this->loader->addAction('admin_init', $vilpyColors, 'registerVilpyColors');
        $this->loader->addFilter('get_user_option_admin_color', $vilpyColors, 'setDefault', 5);

        $dashboardWidget = new DashboardWidget();
        $this->loader->addAction('admin_notices', $dashboardWidget, 'vilpyWelcomeWidget');
        $this->loader->addAction('admin_enqueue_scripts', $dashboardWidget, 'loadCSS');
        $this->loader->addAction('admin_init', $dashboardWidget, 'removeWelcomePanel');
        $this->loader->addAction('wp_dashboard_setup', $dashboardWidget, 'removeDefaultWidgets', 999);

        $adminText = new AdminText();
        $this->loader->addFilter('admin_menu', $adminText, 'removeVersionNum');
        $this->loader->addAction('admin_footer_text', $adminText, 'removeWPText');

        // Login
        $customLoginPage = new CustomLoginPage();
        $this->loader->addAction('login_enqueue_scripts', $customLoginPage, 'changeLogo');

        $changer = new VilpyChangeAdminUrl();
        $this->loader->addAction('plugins_loaded', $changer, 'registerLoginFilters', 0);
        $this->loader->addAction('init', $changer, 'changeAdminUrl', 0);
        $this->loader->addAction('init', $changer, 'blockDefaultLogin', 20);

        // Frontend
        $frontend = new LoadFrontendStyleScript();
        $this->loader->addAction('wp_enqueue_scripts', $frontend, 'enqueueMainStyle');
        $this->loader->addAction('wp_enqueue_scripts', $frontend, 'enqueueElementorStyle');

        $gutenberg = new GutenbergEdits();
        $this->loader->addAction('init', $gutenberg, 'unloadGutenberg', 100);

        $removeEmoji = new RemoveEmoji();
        $this->loader->addAction('init', $removeEmoji, 'removeEmoji', 999);

        // Elementor
        if (did_action('elementor/loaded')) {
            $elementor = new ElementorOptimization();
            $this->loader->addAction('wp_enqueue_scripts', $elementor, 'disableIcons', 20);
        }
    }

    public function run()
    {
        $this->loader->run();
    }
}
