<?php

/**
 * Class for loading all the hooks, filters and other functionality for the whole plugin.
 */

namespace Vilpy;

class Vilpy
{
    protected $loader; //For loading all the hooks and filters

    public function __construct()
    {
        $this->loadPluginDependencies();
        $this->setLocale();
        $this->defineHooks();

        if (VilpyRole::isVilpyUser()) {
            $this->defineRoleHooks();
            $this->runSpecificRoleFunctions();
        }
    }

    private function loadPluginDependencies()
    {
        require_once 'VilpyI18n.php';
        require_once 'VilpyLoader.php';
        require_once 'includes.php';
        $this->loader = new VilpyLoader();
    }

    private function setLocale()
    {
        $vilpyI18n = new VilpyI18n();
        $this->loader->addAction('init', $vilpyI18n, 'loadDomain');
    }

    //These are all the hooks registered for everyone
    private function defineHooks()
    {
        //$vilpyRole = new VilpyRole();
        //$this->loader->addAction('plugins_loaded', $vilpyRole, 'update');

        $optionsPage =  new OptionsPage();
        $this->loader->addAction('admin_menu', $optionsPage, 'create', 999);
        $this->loader->addAction('admin_init', $optionsPage, 'createOptions');
        $this->loader->addAction('admin_enqueue_scripts', $optionsPage, 'loadJS');

        $addVilpyColors = new VilpyColors();
        $this->loader->addAction('admin_init', $addVilpyColors, 'registerVilpyColors');
        $this->loader->addFilter('get_user_option_admin_color', $addVilpyColors, 'setDefault', 5);

        $dashboardWidget = new DashboardWidget();
        $this->loader->addAction('admin_notices', $dashboardWidget, 'vilpyWelcomeWidget');
        $this->loader->addAction('admin_head', $dashboardWidget, 'loadCSS');
        $this->loader->addAction('admin_init', $dashboardWidget, 'removeWelcomePanel');

        $customLoginPage =  new CustomLoginPage();
        $this->loader->addAction('login_enqueue_scripts', $customLoginPage, 'changeLogo');

        $loadFrontendStyle =  new LoadFrontendStyleScript();
        $this->loader->addAction('wp_enqueue_scripts', $loadFrontendStyle, 'enqueueMainStyle');
        $this->loader->addAction('wp_enqueue_scripts', $loadFrontendStyle, 'enqueueElementorStyle');
        $this->loader->addAction('wp_enqueue_scripts', $loadFrontendStyle, 'fixArrowsForMegaMenu');
        $this->loader->addAction('wp_head', $loadFrontendStyle, 'scrollSnapSupport');
        $this->loader->addAction('wp_head', $loadFrontendStyle, 'enableShowcaseMode');

        $AddToHead = new AddToHead();
        $this->loader->addAction('wp_head', $AddToHead, 'addToHead');

        $menuItems = new MenuItems();
        $this->loader->addAction('admin_post_removeSidebarItems', $menuItems, 'removeSidebarItems');
        $this->loader->addAction('admin_bar_menu', $menuItems, 'createTopMenuItem', 100, 1);

        $autoPluginInstaller = new AutoPluginInstaller();
        $this->loader->addAction('wp_ajax_installplugins', $autoPluginInstaller, 'installPlugins');
        $this->loader->addAction('wp_ajax_installthemes', $autoPluginInstaller, 'installThemes');
        $this->loader->addAction('wp_ajax_activateplugins', $autoPluginInstaller, 'activatePlugins');

        $optimizationsPage = new OptimizationsPage();
        $this->loader->addAction('admin_init', $optimizationsPage, 'optimizeOptions');

        $optimizationsPage = new ExtraPage();
        $this->loader->addAction('admin_init', $optimizationsPage, 'extraOptions');

        $systemPage = new SystemPage();
        $this->loader->addAction('admin_init', $systemPage, 'systemOptions');

        $vilpyTracking = new VilpyTracking();
        $this->loader->addAction('admin_init', $vilpyTracking, 'VilpyTracking');

        $vilpyHandleTracking = new VilpyHandleTracking();
        $this->loader->addAction('init', $vilpyHandleTracking, 'handleTracking');

        $wcDataLayer = new WooCommerceDataLayer();
        $wcDataLayer->registerHooks($this->loader);

        $gutenbergEdits = new GutenbergEdits();
        $this->loader->addAction('admin_init', $gutenbergEdits, 'unloadGutenberg', 100);

        $changer = new \Vilpy\VilpyChangeAdminUrl();
        $this->loader->addAction('plugins_loaded', $changer, 'registerLoginFilters', 0);
        $this->loader->addAction('init', $changer, 'changeAdminUrl', 0);
        $this->loader->addAction('init', $changer, 'blockDefaultLogin', 20);

        //If elementor loaded, do the elementor actions
        if (did_action('elementor/loaded')) {
            $elementorOptimizations = new ElementorOptimization();

            // $disableFont = get_option('remove-elementor-google-fonts');
            // if ($disableFont) {
            //     $this->loader->addFilter('elementor/frontend/print_google_fonts', $elementorOptimizations, 'disableFont');
            // }

            $this->loader->addAction('elementor/frontend/after_register_styles', $elementorOptimizations, 'disableFontAwesome', 20);
            $this->loader->addAction('wp_enqueue_scripts', $elementorOptimizations, 'disableIcons', 20);

            $InstallVilpyIcons = new InstallVilpyIcons();
            $this->loader->addFilter('elementor/icons_manager/additional_tabs', $InstallVilpyIcons, 'installIconLib');
        }

        $removeAdminText = new AdminText();
        $this->loader->addFilter('admin_menu', $removeAdminText, 'removeVersionNum');
        $this->loader->addAction('admin_footer_text', $removeAdminText, 'removeWPText');

        $roleDashboardWidgets = new DashboardWidget();
        $this->loader->addAction('wp_dashboard_setup', $roleDashboardWidgets, 'removeDefaultCB');
        $this->loader->addAction('admin_head-dashboard', $roleDashboardWidgets, 'removeDash');

        $setupTestEnvironment = new SetupTestEnvironment();
        $this->loader->addAction('wp_ajax_testenvironmentsetup', $setupTestEnvironment, 'setupTestEnvironment');

        $removeEmojis = new RemoveEmoji();
        $this->loader->addAction('admin_init', $removeEmojis, 'removeEmoji', 999);
        $this->loader->addAction('init', $removeEmojis, 'removeEmoji', 999);

        $cache = new HhCache();
        $this->loader->addAction('admin_post_clearHhCfCache', $cache, 'clearCfCache', 999, 1);
        $this->loader->addAction('admin_init', $cache, 'clearCfOnSave', 999);
        $this->loader->addAction('admin_init', $cache, 'clearLsOnSave', 999);
        $this->loader->addAction('admin_post_clearHhLsCache', $cache, 'clearLsCache', 999, 1);
        $this->loader->addAction('admin_post_clearHhCache', $cache, 'clearCache', 999, 1);
    }

    //These are all the hooks registered for users with vilpy role
    private function defineRoleHooks()
    {
        $menuItems = new MenuItems();
        $this->loader->addAction('admin_init', $menuItems, 'removeMenuItems');
        $this->loader->addAction('wp_before_admin_bar_render', $menuItems, 'removeTopBarItems');
        $this->loader->addAction('admin_bar_menu', $menuItems, 'removeTopBarItems');
        $this->loader->addAction('admin_head', $menuItems, 'removeNotices');
        $this->loader->addAction('admin_head', $menuItems, 'removeScreenOptions');
        $this->loader->addAction('admin_head', $menuItems, 'litespeedDiamond');
        $this->loader->addAction('wp_head', $menuItems, 'removeFrontendItems');


        $gutenbergEdits = new GutenbergEdits();
        $this->loader->addAction('enqueue_block_editor_assets', $gutenbergEdits, 'doEdits');

        $vilpyRole = new VilpyRole();
        $this->loader->addAction('current_screen', $vilpyRole, 'disableProfileAccess');
    }

    //These are all the functions to do things for users with vilpy role
    private function runSpecificRoleFunctions()
    {
    }

    public function run()
    {
        $this->loader->run();
    }
}
