<?php

/**
 * Vilpy plugin
 *
 * Main plugin bootstrap
 *
 * @link              https://github.com/VilpyStudio/vilpy-plugin
 * @since             0.0.14
 * @package           Vilpy
 *
 * @wordpress-plugin
 * Plugin Name:       Vilpy
 * Plugin URI:        https://github.com/VilpyStudio/vilpy-plugin
 * Description:       Vilpy plugin voor beheer van WordPress websites.
 * Version:           0.0.63
 * Author:            Vilpy
 * Author URI:        https://example.com
 * License:           Proprietary
 * License URI:       https://example.com
 * Text Domain:       vilpy
 * Domain Path:       /languages
 *
 * Created by:        Rowan van Zijl
 */

if (!defined('WPINC')) {
    die;
}

define('Vilpy' . '\PLUGINROOT', __FILE__);
define('Vilpy' . '\PLUGINDIR', __DIR__);

if (!defined('CLOUDFLARE_API_KEY')) {
    define('CLOUDFLARE_API_KEY', '');
}


require_once 'includes/Helpers.php'; //Helper functions needed everywhere

function BHinvokeUpdater()
{
    //UPDATER
    require 'includes/vendor/plugin-update-checker/plugin-update-checker.php';
    $myUpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/VilpyStudio/vilpy-plugin',
        __FILE__,
        'vilpy'
    );

    $myUpdateChecker->setBranch('main');
}
BHinvokeUpdater();



function ActivateVilpy()
{
    require_once plugin_dir_path(__FILE__) . 'includes/VilpyActivator.php';
    \Vilpy\VilpyActivator::activate();
}

function DeactivateVilpy()
{
    require_once plugin_dir_path(__FILE__) . 'includes/VilpyDeactivator.php';
    \Vilpy\VilpyDeactivator::deactivate();
}

register_activation_hook(__FILE__, 'ActivateVilpy');
register_deactivation_hook(__FILE__, 'DeactivateVilpy');

//Main plugin class for loading everything
require 'includes/Vilpy.php';

function runVilpy()
{
    $vilpy = new \Vilpy\Vilpy();
    $vilpy->run();
}

//Run entire plugin on muplugins_loaded action to prevent functions not existing
add_action('plugins_loaded', 'runVilpy');
