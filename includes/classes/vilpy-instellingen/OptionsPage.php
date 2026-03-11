<?php

namespace Vilpy;

class OptionsPage
{
    public function create()
    {
        //Creating menu entry
        add_options_page(\hh_brand_name(), \hh_brand_name(), 'manage_options', 'vilpy', [$this, 'topContent']);
        //Creating plugin link
        add_filter('plugin_action_links_' . plugin_basename(PLUGINROOT), [$this, 'vilpySettingsLink'], 10, 5);
    }

    //Wordpress settings API
    public function createOptions()
    {
        //Register 3 database entries
        register_setting('vilpy-settings', 'client-logo');
        register_setting('vilpy-settings', 'client-logo-size', ['default' => '150']);
        register_setting('vilpy-settings', 'client-bg');
        register_setting('vilpy-settings', 'client-accent', ['default' => '#061b17']);
        register_setting('vilpy-settings', 'client-overlay', ['default' => '#061b17']);
        register_setting('vilpy-settings', 'client-title-color');
        register_setting('vilpy-settings', 'client-welcome-text');
        register_setting('vilpy-settings', 'admin-url-override');
        register_setting('vilpy-settings', 'client-cloudflare-zone');
        register_setting('vilpy-settings', 'client-cache-control');
        register_setting('vilpy-settings', 'clear-cloudflare-on-save');
        register_setting('vilpy-settings', 'clear-litespeed-on-save');

        //Add section
        add_settings_section('vilpy-options', __('Aanpassen voor klant', themeTextDomain()), [$this, 'vilpyOptionsSection'], 'vilpy');

        //Add fields in section
        add_settings_field('client-logo', __('Klantlogo', themeTextDomain()), [$this, 'vilpyLogoField'], 'vilpy', 'vilpy-options');
        add_settings_field('client-logo-size', __('Grootte klantlogo', themeTextDomain()), [$this, 'vilpyLogoSizeField'], 'vilpy', 'vilpy-options');
        add_settings_field('client-bg', __('Klantachtergrond', themeTextDomain()), [$this, 'vilpyBgField'], 'vilpy', 'vilpy-options');
        add_settings_field('client-accent', __('Accentkleur', themeTextDomain()), [$this, 'vilpyAccentField'], 'vilpy', 'vilpy-options');
        add_settings_field('client-overlay', __('Overlaykleur', themeTextDomain()), [$this, 'vilpyoverlayField'], 'vilpy', 'vilpy-options');
        add_settings_field('client-welcome-text', __('Welkomsttekst', themeTextDomain()), [$this, 'vilpyWelcomeText'], 'vilpy', 'vilpy-options');
        add_settings_field('admin-url-override', __('Aangepaste login URL', themeTextDomain()), [$this, 'vilpyAdminUrlOverride'], 'vilpy', 'vilpy-options');
        add_settings_field('client-title-color', __('Titelkleur', themeTextDomain()), [$this, 'vilpyTitleColor'], 'vilpy', 'vilpy-options');
        add_settings_field('client-cloudflare-zone', __('Cloudflare zone ID', themeTextDomain()), [$this, 'vilpyCloudflareZoneId'], 'vilpy', 'vilpy-options');
        add_settings_field('client-cache-control', __('Cache instelling in menu voor administrator', themeTextDomain()), [$this, 'vilpyCacheControl'], 'vilpy', 'vilpy-options');
        add_settings_field('clear-cloudflare-on-save', __('Cloudflare cache legen op update', themeTextDomain()), [$this, 'vilpyClearCloudflareCacheOnSave'], 'vilpy', 'vilpy-options');
        add_settings_field('clear-litespeed-on-save', __('Litespeed cache legen op update', themeTextDomain()), [$this, 'vilpyClearLitespeedCacheOnSave'], 'vilpy', 'vilpy-options');
    }

    public function vilpyWelcomeText()
    {
        $text = get_option('client-welcome-text');

        if (!$text && pluginInstalled("woocommerce/woocommerce.php")) {
            $text = \hh_default_welcome_text(true);
        } elseif (!$text && !pluginInstalled("woocommerce/woocommerce.php")) {
            $text = \hh_default_welcome_text(false);
        }

        echo '<div style="max-width: 800px">';
        wp_editor($text, 'client-welcome-text', [
            'textarea_name' => 'client-welcome-text',
            'media_buttons' => false,
            'tinymce' => [
                'content_style' => 'body { font-family: "Bricolage Grotesque", sans-serif; font-size: 16px; }',
            ],
        ]);
        echo '</div>';
    }

    public function vilpyOptionsSection()
    {
        _e('Pas de loginpagina aan met een eigen logo, achtergrond en teksten voor de klant.', themeTextDomain());
    }

    //Callback for Logo field
    public function vilpyLogoField()
    {
        $logo = get_option('client-logo');
        $image = $logo ? $logo : \hh_default_logo();

        ob_start();
        ?>
        <div style='width: 100%; margin-bottom: 10px'>
            <img id='imgLogo' src='<?php echo $image ?>' alt='klant-logo'>
        </div>
        
        <input type='hidden' id="imageurlplugin" value='<?php echo plugins_url('/assets/img/', PLUGINROOT) ?>' />

        <input id='vilpyLogoMedia' type='hidden' name='client-logo' value='<?php echo $logo ?>'/>
        <input id='defaultLogoUrl' type='hidden' value='<?php echo esc_attr(\hh_default_logo()) ?>'/>
        <input type='button' id='logoUploadButton' class='button button-primary' value='Upload logo'>
        <input style='margin-left: 10px;' type='button' id='logoDeleteButton' class='button button-secondary' value='Verwijder logo'>
        <?php
        echo ob_get_clean();
    }

    //Callback for Logo size field
    public function vilpyLogoSizeField()
    {
        $logo = get_option('client-logo-size');
        $value = $logo ? $logo : '150';

        ob_start();
        ?>
        <input type="number" id="clientLogoSize" name="client-logo-size" min="1" max="1000" value="<?php echo $value ?>"> 
        <?php
        echo ob_get_clean();
    }

    //Callback for Background field
    public function vilpyBgField()
    {
        $logo = get_option('client-bg');
        $image = $logo ? $logo : 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';

        ob_start();
        ?>
        <div style='width: 100%; margin-bottom: 10px'>
            <img id='imgBg' style='max-width: 200px;' src='<?php echo $image ?>' alt='klant-logo'>
        </div>
        <input id='vilpyBgMedia' type='hidden' name='client-bg' value='<?php echo $logo ?>'/>
        <input type='button' id='bgUploadButton' class='button button-primary' value='Upload achtergrond'>
        <input style='margin-left: 10px;' type='button' id='bgDeleteButton' class='button button-secondary' value='Verwijder achtergrond'>
        <?php
        echo ob_get_clean();
    }

    //Callback for accent field
    public function vilpyAccentField()
    {
        $accent = get_option('client-accent') ?: '#061b17';
        ob_start();
        ?>
        <input id='accentField' name='client-accent' type="text" class="wp-color-result-text" placeholder="Selecteer kleur" value="<?php echo $accent ?>">
        <?php
        echo ob_get_clean();
    }

    public function vilpyTitleColor()
    {
        $accent = get_option('client-title-color');
        ob_start();
        ?>
        <input id='titleColorField' name='client-title-color' type="text" class="wp-color-result-text" placeholder="Selecteer kleur" value="<?php echo $accent ?>">
        <?php
        echo ob_get_clean();
    }

    public function vilpyCloudflareZoneId()
    {
        $accent = get_option('client-cloudflare-zone');
        ob_start();
        ?>
        <input id='vilpyCloudflareZoneId' name='client-cloudflare-zone' type="text" placeholder="1111111" value="<?php echo $accent ?>">
        <?php
        echo ob_get_clean();
    }

    public function vilpyCacheControl()
    {
        $accent = get_option('client-cache-control');
        ob_start();
        ?>
        <input id='vilpyCacheControl' name='client-cache-control' type="checkbox" <?php echo $accent ? 'checked' : '' ?>>
        <?php
        echo ob_get_clean();
    }

    public function vilpyClearCloudflareCacheOnSave()
    {
        $accent = get_option('clear-cloudflare-on-save');
        $zone = get_option('client-cloudflare-zone');

        $readonly = $zone ? '' : 'readonly';

        ob_start();
        ?>
        <label for="clear-cloudflare-on-save">
        <input id='vilpyClearCloudflareCacheOnSave' placeholder="posttype1,posttype2" name='clear-cloudflare-on-save' <?php echo $readonly ?> type="text" value="<?php echo $accent ?>">
        <?php
        if (!$zone) {
            echo '<span style="font-size: .8em; max-width: 270px; display: block; padding-top: 5px;">*Vul eerst de Cloudflare zone id in</span>';
        }
        ?>
        <br>
        <span style="font-size: .8em; max-width: 270px; display: block; padding-top: 5px;">
                Wanneer de posts van deze post-types worden geupdate of gepubliceerd, legen we de cloudflare cache van hun URL.
        </span>
        </label>
        <?php
        echo ob_get_clean();
    }

    public function vilpyClearLitespeedCacheOnSave()
    {
        $accent = get_option('clear-litespeed-on-save');

        ob_start();
        ?>
        <label for="clear-litespeed-on-save">
        <input id='vilpyClearLitespeedCacheOnSave' placeholder="posttype1,posttype2" name='clear-litespeed-on-save' type="text" value="<?php echo $accent ?>">
        <br>
        <span style="font-size: .8em; max-width: 270px; display: block; padding-top: 5px;">
                Wanneer de posts van deze post-types worden geupdate of gepubliceerd, legen we de litespeed cache van hun URL.
        </span>
        </label>
        <?php
        echo ob_get_clean();
    }

    //Callback for overlay field
    public function vilpyOverlayField()
    {
        $accent = get_option('client-overlay') ?: '#061b17';
        ob_start();
        ?>
        <input id='overlayField' name='client-overlay' type="text" class="wp-color-result-text" placeholder="Selecteer kleur" value="<?php echo $accent ?>">
        <?php
        echo ob_get_clean();
    }

    //Javascript for media uploader and color picker
    public function loadJS($hook_suffix)
    {
        if ('settings_page_vilpy' === $hook_suffix) {
            $path = plugins_url('/assets/js', PLUGINROOT);
            wp_enqueue_script('vilpy-script', $path . '/vilpy.js', array( 'wp-color-picker', 'jquery' ), false, true);
            wp_enqueue_media();
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_style('vilpy-bricolage-font', 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@400;500;700;800&display=swap', [], null);
            wp_add_inline_style('vilpy-bricolage-font', '
                .settings_page_vilpy,
                .settings_page_vilpy .wrap,
                .settings_page_vilpy .wrap h1,
                .settings_page_vilpy .wrap h2,
                .settings_page_vilpy .wrap h3,
                .settings_page_vilpy .wrap p,
                .settings_page_vilpy .form-table th,
                .settings_page_vilpy .form-table td,
                .settings_page_vilpy .form-table label,
                .settings_page_vilpy input,
                .settings_page_vilpy textarea,
                .settings_page_vilpy select,
                .settings_page_vilpy button,
                .settings_page_vilpy .wp-editor-container,
                .settings_page_vilpy .wp-switch-editor,
                .settings_page_vilpy .mce-toolbar *,
                .settings_page_vilpy .mce-menubar *,
                .settings_page_vilpy .mce-statusbar * {
                    font-family: "Bricolage Grotesque", sans-serif !important;
                }
            ');
            wp_localize_script('vilpy-script', 'admin_url', array('ajax_url' => admin_url('admin-ajax.php')));
        }
    }

    //Settings link
    public function vilpySettingsLink($links)
    {
        $mylinks = array(
            '<a href="' . admin_url('options-general.php?page=vilpy') . '">Settings</a>',
        );
        return array_merge($links, $mylinks);
    }

    public function vilpyAdminUrlOverride()
    {
        $url = get_option('admin-url-override');

        ob_start();
        ?>
        <input id='adminUrlOverride' name='admin-url-override' type="text" placeholder="dashboard" value="<?php echo esc_attr($url) ?>">
        <br>
        <span style="font-size: .8em; max-width: 400px; display: block; padding-top: 5px;">
                Wanneer ingesteld, zal dit je WordPress login URL (slug) veranderen naar de verstrekte string en de wp-admin en wp-login endpoints blokkeren tegen directe toegang.
        </span>
        <?php
        echo ob_get_clean();
    }

    //Admin content
    public function topContent()
    {
        (new VilpyContent())
            ->setTitle('⚙ ' . \hh_brand_name())
            ->setSubtitle('<i style="margin-left: 10px;">' . esc_html(\hh_brand_subtitle()) . '</i>')
            ->addOptions()
            ->getContent(); //dont remove
    }
}
