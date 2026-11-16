<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Vilpy
 * Plugin URI:        https://github.com/VilpyStudio/vilpy-plugin
 * Description:       Beheer-plugin van Studio Vilpy: inlogpagina en inlogadres, dashboard en admin-stijl.
 * Version:           0.1.0
 * Requires PHP:      7.4
 * Author:            Studio Vilpy
 * Author URI:        https://vilpy.nl
 * License:           Proprietary
 * Text Domain:       vilpy
 */

if (!defined('WPINC')) {
    die;
}

define('Vilpy' . '\PLUGINROOT', __FILE__);
define('Vilpy' . '\PLUGINDIR', __DIR__);

require_once __DIR__ . '/includes/Helpers.php';

// Updates via GitHub (update.json op main, zip uit de laatste release)
require __DIR__ . '/includes/vendor/plugin-update-checker/plugin-update-checker.php';
YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
    'https://raw.githubusercontent.com/VilpyStudio/vilpy-plugin/main/update.json',
    __FILE__,
    'vilpy'
);

register_activation_hook(__FILE__, function () {
    require_once __DIR__ . '/includes/VilpyActivator.php';
    \Vilpy\VilpyActivator::activate();
});

register_deactivation_hook(__FILE__, function () {
    require_once __DIR__ . '/includes/VilpyDeactivator.php';
    \Vilpy\VilpyDeactivator::deactivate();
});

require __DIR__ . '/includes/Vilpy.php';

add_action('plugins_loaded', function () {
    (new \Vilpy\Vilpy())->run();
});
