<?php

namespace Vilpy;

class OptionsPage
{
    public function create()
    {
        add_options_page(\vilpy_brand_name(), \vilpy_brand_name(), 'manage_options', 'vilpy', [$this, 'topContent']);
        add_filter('plugin_action_links_' . plugin_basename(PLUGINROOT), [$this, 'vilpySettingsLink'], 10, 5);
    }

    public function createOptions()
    {
        $url   = ['sanitize_callback' => 'esc_url_raw'];
        $color = ['sanitize_callback' => [$this, 'sanitizeColor']];

        register_setting('vilpy-settings', 'client-logo', $url);
        register_setting('vilpy-settings', 'client-logo-size', ['default' => '150', 'sanitize_callback' => 'absint']);
        register_setting('vilpy-settings', 'client-bg', $url);
        register_setting('vilpy-settings', 'client-bg-color', $color);
        register_setting('vilpy-settings', 'client-accent', ['default' => '#061b17', 'sanitize_callback' => [$this, 'sanitizeColor']]);
        register_setting('vilpy-settings', 'client-overlay', ['default' => '#061b17', 'sanitize_callback' => [$this, 'sanitizeColor']]);
        register_setting('vilpy-settings', 'client-title-color', $color);
        register_setting('vilpy-settings', 'client-welcome-text', ['sanitize_callback' => [$this, 'sanitizeWelcomeText']]);
        register_setting('vilpy-settings', 'admin-url-override', ['sanitize_callback' => [$this, 'sanitizeAdminUrlOverride']]);

        add_settings_section('vilpy-options', __('Aanpassen voor klant', 'vilpy'), [$this, 'vilpyOptionsSection'], 'vilpy');

        add_settings_field('client-logo', __('Klantlogo', 'vilpy'), [$this, 'vilpyLogoField'], 'vilpy', 'vilpy-options');
        add_settings_field('client-logo-size', __('Grootte klantlogo', 'vilpy'), [$this, 'vilpyLogoSizeField'], 'vilpy', 'vilpy-options');
        add_settings_field('client-bg', __('Klantachtergrond', 'vilpy'), [$this, 'vilpyBgField'], 'vilpy', 'vilpy-options');
        add_settings_field('client-bg-color', __('Achtergrondkleur loginscherm', 'vilpy'), [$this, 'vilpyBgColorField'], 'vilpy', 'vilpy-options');
        add_settings_field('client-accent', __('Accentkleur', 'vilpy'), [$this, 'vilpyAccentField'], 'vilpy', 'vilpy-options');
        add_settings_field('client-overlay', __('Overlaykleur', 'vilpy'), [$this, 'vilpyOverlayField'], 'vilpy', 'vilpy-options');
        add_settings_field('client-welcome-text', __('Welkomsttekst', 'vilpy'), [$this, 'vilpyWelcomeText'], 'vilpy', 'vilpy-options');
        add_settings_field('admin-url-override', __('Aangepaste login URL', 'vilpy'), [$this, 'vilpyAdminUrlOverride'], 'vilpy', 'vilpy-options');
        add_settings_field('client-title-color', __('Titelkleur', 'vilpy'), [$this, 'vilpyTitleColor'], 'vilpy', 'vilpy-options');
    }

    public function sanitizeColor($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        return preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value) ? $value : '';
    }

    public function sanitizeWelcomeText($text)
    {
        if (!is_string($text)) {
            return '';
        }

        // TinyMCE kan bookmark-spans achterlaten bij het wisselen van tabs.
        $text = preg_replace('/<span[^>]*data-mce-type="bookmark"[^>]*>.*?<\/span>/is', '', $text);
        $text = preg_replace('/<span[^>]*class="mce_SELRES_start"[^>]*><\/span>/is', '', $text);

        return wp_kses_post($text);
    }

    public function sanitizeAdminUrlOverride($value)
    {
        $value = sanitize_title((string) $value);
        $reserved = ['wp-admin', 'wp-login', 'wp-login.php', 'admin', 'login', 'dashboard', 'xmlrpc.php', 'wp-json'];

        if ($value === '' || in_array($value, $reserved, true)) {
            add_settings_error(
                'admin-url-override',
                'invalid-admin-url-override',
                __('De aangepaste login URL was ongeldig of gereserveerd. De standaard slug "vilpy" wordt gebruikt.', 'vilpy'),
                'error'
            );
            return 'vilpy';
        }

        return $value;
    }

    public function vilpyOptionsSection()
    {
        echo esc_html__('Pas de loginpagina en het dashboard aan met een eigen logo, achtergrond en teksten voor de klant.', 'vilpy');
    }

    public function vilpyWelcomeText()
    {
        $text = $this->sanitizeWelcomeText(get_option('client-welcome-text')) ?: \vilpy_default_welcome_text();

        echo '<div style="max-width: 800px">';
        wp_editor($text, 'client-welcome-text', [
            'textarea_name' => 'client-welcome-text',
            'media_buttons' => false,
            'tinymce' => [
                'content_style' => 'body { font-family: Bricolage Grotesque, sans-serif; font-size: 16px; }',
            ],
        ]);
        echo '</div>';
    }

    public function vilpyLogoField()
    {
        $logo = get_option('client-logo');
        ?>
        <div style="width: 100%; margin-bottom: 10px">
            <img id="imgLogo" src="<?php echo esc_url($logo ?: \vilpy_default_logo()); ?>" alt="klant-logo">
        </div>
        <input id="vilpyLogoMedia" type="hidden" name="client-logo" value="<?php echo esc_attr($logo); ?>">
        <input id="defaultLogoUrl" type="hidden" value="<?php echo esc_attr(\vilpy_default_logo()); ?>">
        <input type="button" id="logoUploadButton" class="button button-primary" value="Upload logo">
        <input style="margin-left: 10px;" type="button" id="logoDeleteButton" class="button button-secondary" value="Verwijder logo">
        <?php
    }

    public function vilpyLogoSizeField()
    {
        $value = get_option('client-logo-size') ?: '150';
        printf('<input type="number" id="clientLogoSize" name="client-logo-size" min="1" max="1000" value="%s"> px', esc_attr($value));
    }

    public function vilpyBgField()
    {
        $bg = get_option('client-bg');
        ?>
        <div style="width: 100%; margin-bottom: 10px">
            <img id="imgBg" style="max-width: 200px;" src="<?php echo $bg ? esc_url($bg) : 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs='; ?>" alt="klant-achtergrond">
        </div>
        <input id="vilpyBgMedia" type="hidden" name="client-bg" value="<?php echo esc_attr($bg); ?>">
        <input type="button" id="bgUploadButton" class="button button-primary" value="Upload achtergrond">
        <input style="margin-left: 10px;" type="button" id="bgDeleteButton" class="button button-secondary" value="Verwijder achtergrond">
        <?php
    }

    private function colorInput($id, $name, $value, $help = '')
    {
        printf(
            '<input id="%1$s" name="%2$s" type="text" class="wp-color-result-text" placeholder="Selecteer kleur" value="%3$s">',
            esc_attr($id),
            esc_attr($name),
            esc_attr($value)
        );
        if ($help) {
            printf('<span style="font-size: .8em; max-width: 400px; display: block; padding-top: 5px;">%s</span>', esc_html($help));
        }
    }

    public function vilpyBgColorField()
    {
        $this->colorInput('bgColorField', 'client-bg-color', get_option('client-bg-color'), 'Wanneer een kleur is ingesteld, wordt deze als achtergrond van het loginscherm gebruikt en wordt de achtergrondafbeelding niet getoond. Laat leeg om de afbeelding te gebruiken.');
    }

    public function vilpyAccentField()
    {
        $this->colorInput('accentField', 'client-accent', get_option('client-accent') ?: '#061b17');
    }

    public function vilpyTitleColor()
    {
        $this->colorInput('titleColorField', 'client-title-color', get_option('client-title-color'));
    }

    public function vilpyOverlayField()
    {
        $this->colorInput('overlayField', 'client-overlay', get_option('client-overlay') ?: '#061b17');
    }

    public function vilpyAdminUrlOverride()
    {
        printf(
            '<input id="adminUrlOverride" name="admin-url-override" type="text" placeholder="vilpy" value="%s">',
            esc_attr(get_option('admin-url-override'))
        );
        echo '<span style="font-size: .8em; max-width: 400px; display: block; padding-top: 5px;">'
            . esc_html__('Je WordPress-inlogadres wordt /<slug>. wp-admin en wp-login.php zijn dan niet meer direct bereikbaar.', 'vilpy')
            . '</span>';
    }

    public function loadJS($hook_suffix)
    {
        if ('settings_page_vilpy' !== $hook_suffix) {
            return;
        }

        wp_enqueue_script('vilpy-script', plugins_url('/assets/js/vilpy.js', PLUGINROOT), ['wp-color-picker', 'jquery'], \vilpy_asset_ver('/assets/js/vilpy.js'), true);
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('vilpy-bricolage-font', 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@400;500;700;800&display=swap', [], null);
        wp_add_inline_style('vilpy-bricolage-font', '
            .settings_page_vilpy .wrap, .settings_page_vilpy .wrap h1, .settings_page_vilpy .wrap h2,
            .settings_page_vilpy .wrap p, .settings_page_vilpy .form-table th, .settings_page_vilpy .form-table td,
            .settings_page_vilpy .form-table label, .settings_page_vilpy input, .settings_page_vilpy button {
                font-family: "Bricolage Grotesque", sans-serif !important;
            }
        ');
    }

    public function vilpySettingsLink($links)
    {
        $links[] = '<a href="' . esc_url(admin_url('options-general.php?page=vilpy')) . '">Instellingen</a>';
        return $links;
    }

    public function topContent()
    {
        (new VilpyContent())
            ->setTitle('⚙ ' . \vilpy_brand_name())
            ->setSubtitle('Instellingen')
            ->addOptions()
            ->getContent();
    }
}
