<?php

namespace Vilpy;

?>

<nav style='margin-top: 10px' class="nav-tab-wrapper">
    <button data-tab="default" class="nav-tab">Algemeen</button>
    <button data-tab="optimalisatie" class="nav-tab">Optimalisatie</button>
    <button data-tab="PluginsInstall" class="nav-tab">Plugins/thema's installeren</button>
    <button data-tab="setupEnvironment" class="nav-tab">Testomgeving</button>
    <button data-tab="vilpyKlant" class="nav-tab">Klantrol</button>
    <button data-tab="extra" class="nav-tab">Extra</button>
    <button data-tab="system" class="nav-tab">System</button>
    <button data-tab="vilpyTracking" class="nav-tab">Tracking</button>
</nav>
<style>
.antibas {
    position: fixed;
    inset: 0;
    background-color: rgba(255, 255, 255, 0.7);
    display: flex;
    justify-content: center;
}

.popup {
    position: absolute;
    top: 40%;
    text-align: center;
}
</style>

<div id="default" class="hh-tab-content">
    <form method="post" action="options.php">
        <?php
            settings_fields('vilpy-settings');
            do_settings_sections('vilpy');
            submit_button();
        ?>
    </form>
</div>

<div id="Updates" class="hh-tab-content">
    <h2>Plugin Updates</h2>
    <p>Hier komen de Plugin Updates settings</p>
</div>

<div id="PluginsInstall" class="hh-tab-content next-to-eachother">
    <section style='margin-right: 100px; margin-bottom: 30px;'>
        <?php
        $plugins = pluginsToAutoInstall(); //Found in helpers
        ?>
        <h2 style="display: inline-block; margin-right: 10px">Plugins/thema's installeren</h2>
        <a class="select-all" href="#" style="display: inline"><?php _e('Select all', themeTextDomain()); ?></a><br>
        <?php
        foreach ($plugins as $plugin => $data) {
            if (pluginInstalled("{$data['folder']}/{$data['filename']}.php")) {
                ?>
        <label style="pointer-events: none; opacity: .5;" for="<?php echo $data['slug'] ?>">
            <input class="plugin-checkbox plugin-installed" name="<?php echo $data['slug'] ?>" type="checkbox"
                id="<?php echo $data['slug'] ?>" value="<?php echo $data['slug'] ?>">
                <?php echo $plugin ?> <i><?php _e(' | Already installed', themeTextDomain()) ?></i>
        </label>
                <?php
            } else {
                ?>
        <label for="<?php echo $data['slug'] ?>" class="<?php echo $data['slug'] ?>">
            <input class="plugin-checkbox" name="<?php echo $data['slug'] ?>" type="checkbox"
                id="<?php echo $data['slug'] ?>" value="<?php echo $data['slug'] ?>">
                <?php echo $plugin ?>
        </label>
        <img class="smallloader" style="display: none; margin-bottom: -5px; margin-left: 5px;"
            src="<?php echo esc_url(get_admin_url() . 'images/loading.gif'); ?>" />
                <?php
            }
            echo '<br>';
            echo '<br>';
        }
        ?>
        <button id="installPlugins" class="button button-primary"><?php _e('Install plugins', themeTextDomain()); ?></button>
        <button id="activateInstalledPlugins" class="button button-secondary"><?php _e('Activate installed plugins', themeTextDomain()); ?></button>
        <img class="smallloader-activate" style="display: none; margin-bottom: -10px; margin-left: 5px;" src="<?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?>" />
    </section>
    <section>
        <h2 style="display: inline-block; margin-right: 5px">Thema's installeren</h2>
        <?php  $themes = wp_get_themes();
        $vilpychild = array_key_exists('vilpy-child', $themes);
        $vilpytheme = array_key_exists('vilpy-theme', $themes);
        $shoptimizer = array_key_exists('shoptimizer', $themes);
        $shoptimizerChild = array_key_exists('shoptimizer-child-theme', $themes);
        ?>
        <br>

        <!-- Remove webhop theme from the install page -->
        <!-- <label <?php //echo $vilpytheme ? 'style="pointer-events: none; opacity: .5;"' : ''; ?> for="webshop-theme" class="theme webshop-theme">
            <input id='webshop-theme' name="webshop-theme" type="checkbox" value="webshop-theme" class="theme-checkbox">
            Vilpy Webshop
            <i><?php //echo $vilpytheme ? __(' | Already installed', themeTextDomain()) : '' ?></i>
        </label> -->

        <img class="smallloader-activate" style="display: none; margin-bottom: -6px; margin-left: 5px;" src="<?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?>" />
        <br>
        <br>
        <label <?php echo $vilpytheme ? 'style="pointer-events: none; opacity: .5;"' : ''; ?> for="website-theme" class="theme website-theme">
            <input id="website-theme" name="website-theme" type="checkbox" value="website-theme" class="theme-checkbox">
            Vilpy Website
            <i><?php echo $vilpytheme ? __(' | Already installed', themeTextDomain()) : '' ?></i>
        </label>
        <img class="smallloader-activate" style="display: none; margin-bottom: -6px; margin-left: 5px;" src="<?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?>" />
        <br>
        <br>
        <label <?php echo $vilpychild ? 'style="pointer-events: none; opacity: .5;"' : ''; ?> for="vilpy-child" class="vilpy-child theme">
            <input id="vilpy-child" name="vilpy-child" type="checkbox" value="vilpy-child" class="theme-checkbox">
            Vilpy Child
            <i><?php echo $vilpychild ? __(' | Already installed', themeTextDomain()) : '' ?></i>
        </label>
        <img class="smallloader-activate" style="display: none; margin-bottom: -6px; margin-left: 5px;" src="<?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?>" />
        <br>
        <br>
        <label <?php echo $shoptimizer ? 'style="pointer-events: none; opacity: .5;"' : ''; ?> for="vilpy-shoptimizer" class="theme vilpy-shoptimizer">
            <input id='vilpy-shoptimizer' name="vilpy-shoptimizer" type="checkbox" value="vilpy-shoptimizer" class="theme-checkbox">
            Shoptimizer
            <i><?php echo $shoptimizer ? __(' | Already installed', themeTextDomain()) : '' ?></i>
        </label>
        <img class="smallloader-activate" style="display: none; margin-bottom: -6px; margin-left: 5px;" src="<?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?>" />
        <br>
        <br>
        <label <?php echo $shoptimizerChild ? 'style="pointer-events: none; opacity: .5;"' : ''; ?> for="vilpy-shoptimizerchild" class="theme vilpy-shoptimizerchild">
            <input id='vilpy-shoptimizerchild' name="vilpy-shoptimizerchild" type="checkbox" value="vilpy-shoptimizerchild" class="theme-checkbox">
            Shoptimizer Child
            <i><?php echo $shoptimizerChild ? __(' | Already installed', themeTextDomain()) : '' ?></i>
        </label>
        <img class="smallloader-activate" style="display: none; margin-bottom: -6px; margin-left: 5px;" src="<?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?>" />
        <br>
        <br>
        <img class="smallloader" style="display: none; margin-bottom: -5px; margin-left: 5px;" src="<?php echo esc_url(get_admin_url() . 'images/loading.gif'); ?>" />

        <button id="installThemes" class="button button-primary"><?php _e('Install themes', themeTextDomain()); ?></button>
        <img class="smallloader-activate" style="display: none; margin-bottom: -10px; margin-left: 5px;" src="<?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?>" />
        <div id='output'>

        </div>
    </section>
</div>

<div id='setupEnvironment' class='hh-tab-content'>
        <h2>Website instellen</h2>
    <?php _e('Setup the website based on what you want to do', themeTextDomain()); ?>
    <div id="response"></div>
    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row">Website of webshop <span style="padding-top: 5px;">Deze optie heeft invloed in het
                        opzetten van een testomgeving.</span></th>
                <td>
                    <p>
                        <label>
                            <input name="shoporsite" type="radio" value="shop" class="shoporsite">
                            Webshop
                        </label>
                    </p>
                    <p>
                        <label>
                            <input name="shoporsite" type="radio" value="site" class="shoporsite" checked>
                            Website
                        </label>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">Testomgeving opzetten <span style="padding-top: 5px;">Deze optie verwijderd standaard
                        plugins, <b>alle comments</b>, installeert het standaard thema én zet alle standaard WordPress
                        instellingen goed.</span></th>
                <td>
                    <input type="button" id="setupTestEnvironment" class="button button-primary"
                        value="Testomgeving opzetten">
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div id="optimalisatie" class="hh-tab-content">
    <form method="post" action="options.php">
        <?php
            settings_fields('vilpy-optimize');
            do_settings_sections('vilpy-optimizations-section');
            submit_button();
        ?>
    </form>
</div>

<div id="vilpyKlant" class="hh-tab-content">
    <h2>Instellingen voor klantrol</h2>
    <p>Hier kun je het menu van de klantrol instellen, vink aan welke menu items je wilt verbergen.</p>
    <script>
    function toggle(source) {
        const container = source.parentElement.parentElement;
        checkboxes = container.querySelectorAll('input[type="checkbox"]:not(.fake-submenu-label input[type="checkbox"])');
        for (var i = 0, n = checkboxes.length; i < n; i++) {
            checkboxes[i].checked = source.checked;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {

        const isChecked = (el) => {
            const els = el.parentElement.parentElement.querySelector('.sub-wrapper').children;
            for (var i = 0, n = els.length; i < n; i++) {
                if (els[i].nodeName !== 'LABEL') {
                    continue;
                }
                if (!els[i].children[0].checked) {
                    return false;
                }
            }
            return true;
        };

        const subChecks = document.querySelectorAll('.submenu-label');
        for (var i = 0, n = subChecks.length; i < n; i++) {
            subChecks[i].addEventListener('change', function(e) {
                const parentCheck = this.parentElement.parentElement.querySelector('input[type="checkbox"]:not(.fake-submenu-label input[type="checkbox"])');
                if (isChecked(this)) {
                    parentCheck.checked = true;
                } else {
                    parentCheck.checked = false;
                }
            });
        }
    })
    </script>
    <?php
    require 'adminmenu.php';
    ?>
    <?php
    //Todo implement topbar customization
    // require 'topbar.php';
    ?>
</div>

<div id="vilpyTracking" class="hh-tab-content">
    <form method="post" action="options.php">
        <?php
            settings_fields('vilpy-tracking');
            do_settings_sections('vilpy-tracking-section');
            submit_button();
        ?>
    </form>
</div>

<!-- Remove the extra tab from the options page -->
<!-- <div id="extra" class="hh-tab-content">
    <form method="post" action="options.php">
    <?php
        //settings_fields('vilpy-extra');
        //do_settings_sections('vilpy-extra-section');
        //submit_button();
    ?>
    </form>
</div> -->

<div id="system" class="hh-tab-content">
    <?php
        settings_fields('vilpy-system');
        do_settings_sections('vilpy-system-section');
    ?>
</div>
