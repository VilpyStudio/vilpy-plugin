<?php

namespace Vilpy;

class OptimizationsPage
{
    //Wordpress settings API
    public function optimizeOptions()
    {

        //Register 3 database entries
        register_setting('vilpy-optimize', 'elementor-arrows');
        register_setting('vilpy-optimize', 'disable-gutenberg');
        // register_setting('vilpy-optimize', 'remove-elementor-google-fonts');
       // register_setting('vilpy-optimize', 'remove-fontawesome');
        register_setting('vilpy-optimize', 'remove-elementor-icons');
        register_setting('vilpy-optimize', 'elementor-mega-arrows');
        register_setting('vilpy-optimize', 'hide-language-selector');
        register_setting('vilpy-optimize', 'disable-icon-css');

        //Add section
        add_settings_section('vilpy', __('Optimizations', themeTextDomain()), [$this, 'optimizationSection'], 'vilpy-optimizations-section');

        //Add fields in section
        add_settings_field('elementor-arrows', __('Elementor arrows', themeTextDomain()), [$this, 'elementorArrows'], 'vilpy-optimizations-section', 'vilpy');
        add_settings_field('disable-gutenberg', __('Gutenberg', themeTextDomain()), [$this, 'disableGutenberg'], 'vilpy-optimizations-section', 'vilpy');
        // add_settings_field('remove-elementor-google-fonts', __('Elementor Google Fonts', themeTextDomain()), [$this, 'removeElementorGoogleFonts'], 'vilpy-optimizations-section', 'vilpy');
        //add_settings_field('remove-fontawesome', __('FontAwesome', themeTextDomain()), [$this, 'removeFontawesome'], 'vilpy-optimizations-section', 'vilpy');
        add_settings_field('remove-elementor-icons', __('Elementor icons', themeTextDomain()), [$this, 'removeElementorIcons'], 'vilpy-optimizations-section', 'vilpy');
        //houden 
        add_settings_field('elementor-mega-arrows', __('Elementor mega menu arrows', themeTextDomain()), [$this, 'elementorMegaArrows'], 'vilpy-optimizations-section', 'vilpy');
        add_settings_field('hide-language-selector', __('Language selector', themeTextDomain()), [$this, 'languageSelector'], 'vilpy-optimizations-section', 'vilpy');
        add_settings_field('disable-icon-css', __('Disable icon css', themeTextDomain()), [$this, 'iconCss'], 'vilpy-optimizations-section', 'vilpy');
    }

    public function optimizationSection()
    {
        _e('Get Google pagespeeds to one hundred with these settings', themeTextDomain());
    }

    //Callback for Logo field
    public function elementorArrows()
    {
        $check = get_option('elementor-arrows');
        ob_start();
        ?>
        <input id="elementor-arrows" type="checkbox" name="elementor-arrows" <?php echo $check ? 'checked' : '' ?>>
        <label for="elementor-arrows">Vervang elementor pijlen</label>
        <?php
        echo ob_get_clean();
    }

    public function elementorMegaArrows()
    {
        $check = get_option('elementor-mega-arrows');
        ob_start();
        ?>
        <input id="elementor-mega-arrows" type="checkbox" name="elementor-mega-arrows" <?php echo $check ? 'checked' : '' ?>>
        <label for="elementor-mega-arrows">Vervang <b>mega menu</b> elementor pijlen</label>
        <?php
        echo ob_get_clean();
    }

    public function disableGutenberg()
    {
        $check = get_option('disable-gutenberg');
        ob_start();
        ?>
        <input id="disable-gutenberg" type="checkbox" name="disable-gutenberg" <?php echo $check ? 'checked' : '' ?>>
        <label for="disable-gutenberg">Gutenberg uitzetten</label>
        <?php
        echo ob_get_clean();
    }

    /*
    public function removeElementorGoogleFonts()
    {
        $check = get_option('remove-elementor-google-fonts');
        ob_start();
        ?>
        <input id="remove-elementor-google-fonts" type="checkbox" name="remove-elementor-google-fonts" <?php echo $check ? 'checked' : '' ?> disabled>
        <label for="remove-elementor-google-fonts">Elementor google fonts uitzetten<br /><small>(sinds February 21, 2024 niet meer nodig, kan via Elementor - instellingen - geavanceerd - sectie: Google Fonts)</small></label>
        <?php
        echo ob_get_clean();
    }
    */

    public function removeFontawesome()
    {
        $check = get_option('remove-fontawesome');
        ob_start();
        ?>
        <input id="remove-fontawesome" type="checkbox" name="remove-fontawesome" <?php echo $check ? 'checked' : '' ?>>
        <label for="remove-fontawesome">Fontawesome uitzetten</label>
        <?php
        echo ob_get_clean();
    }

    public function removeElementorIcons()
    {
        $check = get_option('remove-elementor-icons');
        ob_start();
        ?>
        <input id="remove-elementor-icons" type="checkbox" name="remove-elementor-icons" <?php echo $check ? 'checked' : '' ?>>
        <label for="remove-elementor-icons">Elementor iconen weghalen</label>
        <?php
        echo ob_get_clean();
    }

    public function languageSelector()
    {
        $check = get_option('hide-language-selector');
        ob_start();
        ?>
        <input id="hide-language-selector" type="checkbox" name="hide-language-selector" <?php echo $check ? 'checked' : '' ?>>
        <label for="hide-language-selector">Taal selector weghalen</label>
        <?php
        echo ob_get_clean();
    }

    public function iconCss()
    {
        $check = get_option('disable-icon-css');
        ob_start();
        ?>
        <input id="disable-icon-css" type="checkbox" name="disable-icon-css" <?php echo $check ? 'checked' : '' ?>>
        <label for="disable-icon-css">Iconen CSS uitzetten</label>
        <?php
        echo ob_get_clean();
    }

    public static function setDefaults()
    {
        add_option('elementor-arrows', 'on');
        add_option('disable-gutenberg', 'on');
        // add_option('remove-elementor-google-fonts', 'on');
        //add_option('remove-fontawesome', 'on');
        add_option('remove-elementor-icons', 'on');
        add_option('hide-language-selector', 'on');
    }
}
