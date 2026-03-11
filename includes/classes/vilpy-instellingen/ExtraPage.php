<?php

namespace Vilpy;

class ExtraPage
{
    //Wordpress settings API
    public function extraOptions()
    {
        register_setting('vilpy-extra', 'enable-scroll-snap-support');
        register_setting('vilpy-extra', 'enable-showcase-mode');
        register_setting('vilpy-extra', 'showcase-mode-distance');
        register_setting('vilpy-extra', 'showcase-mode-duration');
        register_setting('vilpy-extra', 'showcase-mode-delay');

        //Add section
        add_settings_section('vilpyextra', __('Extra', themeTextDomain()), [$this, 'extraSection'], 'vilpy-extra-section', 'vilpyextra');

        //Add fields in section
        add_settings_field('enable-scroll-snap-support', __('Enable scroll snap', themeTextDomain()), [$this, 'enableScrollSnap'], 'vilpy-extra-section', 'vilpyextra');
        add_settings_field('enable-showcase-mode', __('Showcase mode', themeTextDomain()), [$this, 'enableShowcaseMode'], 'vilpy-extra-section', 'vilpyextra');        
        add_settings_field('showcase-mode-distance', '', [$this, 'showcaseModeDistance'], 'vilpy-extra-section', 'vilpyextra');
        add_settings_field('showcase-mode-duration', '', [$this, 'showcaseModeDuration'], 'vilpy-extra-section', 'vilpyextra');
        add_settings_field('showcase-mode-delay', '', [$this, 'showcaseModeDelay'], 'vilpy-extra-section', 'vilpyextra');
    }

    public function extraSection()
    {
        _e('Random settings for very specific use cases', themeTextDomain());
    }

    public function enableScrollSnap()
    {
        $check = get_option('enable-scroll-snap-support');
        ob_start();
        ?>
        <input id="enable-scroll-snap-support" type="checkbox" name="enable-scroll-snap-support" <?php echo $check ? 'checked' : '' ?>>
        <label for="enable-scroll-snap-support">Scroll snap aanzetten <br>
            <span style="font-size: .8em; max-width: 270px; display: block; padding-top: 5px;">
                Zorgt ervoor dat de elementen naast elkaar blijven op mobiel. Voeg de .telefoonscrollsnap class toe aan het Posts element om dit te gebruiken.
            </span>
        </label>
        <?php
        echo ob_get_clean();
    }

    public function enableShowcaseMode()
    {
        $check = get_option('enable-showcase-mode');
        ob_start();
        ?>
        <input id="enable-showcase-mode" type="checkbox" name="enable-showcase-mode" <?php echo $check ? 'checked' : '' ?>>
        <label for="enable-showcase-mode">Showcase modus aanzetten <br>
            <span style="font-size: .8em; max-width: 270px; display: block; padding-top: 5px;">
                Hiermee wordt er automatisch gescrolled naar de onderkant van de pagina. Hiermee kun je een schermopname maken voor de socials.
            </span>
        </label>
        <?php
        echo ob_get_clean();
    }

    public function showcaseModeDistance()
    {
        $check = get_option('showcase-mode-distance');
        ob_start();
        ?>
        <label style="font-size: 13px" for="showcase-mode-distance">Scroll afstand<br>
        <input style="margin-top: 5px" id="showcase-mode-distance" type="number" name="showcase-mode-distance" value="<?php echo $check ? $check : 2300 ?>">
            <span style="font-size: .8em; max-width: 270px; display: block; padding-top: 5px;">
                Hiermee kun je de afstand van de scroll instellen. Standaard is dit 2300px.
            </span>
        </label>
        <?php
        echo ob_get_clean();
    }

    public function showcaseModeDuration()
    {
        $check = get_option('showcase-mode-duration');
        ob_start();
        ?>
        <label style="font-size: 13px" for="showcase-mode-duration">Scroll duur<br>
        <input style="margin-top: 5px" id="showcase-mode-duration" type="number" name="showcase-mode-duration" value="<?php echo $check ? $check : 3000 ?>">
            <span style="font-size: .8em; max-width: 270px; display: block; padding-top: 5px;">
                Hiermee kun je de duur van de scroll instellen. Standaard is dit 3000ms.
            </span>
        </label>
        <?php
        echo ob_get_clean();
    }

    public function showcaseModeDelay()
    {
        $check = get_option('showcase-mode-delay');
        ob_start();
        ?>
        <label style="font-size: 13px" for="showcase-mode-delay">Scroll vertraging<br>
        <input style="margin-top: 5px" id="showcase-mode-delay" type="number" name="showcase-mode-delay" value="<?php echo $check ? $check : 4000 ?>">
            <span style="font-size: .8em; max-width: 270px; display: block; padding-top: 5px;">
                Hiermee kun je de vertraging van de scroll instellen. Standaard is dit 4000ms.
            </span>
        </label>
        <?php
        echo ob_get_clean();
    }
}
