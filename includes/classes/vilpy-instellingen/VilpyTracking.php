<?php

namespace Vilpy;

class VilpyTracking
{
    //WordPress settings API
    public function VilpyTracking()
    {
        register_setting('vilpy-tracking', 'GTM-code');
        register_setting('vilpy-tracking', 'GA4-code');
        register_setting('vilpy-tracking', 'GSCode');
        register_setting('vilpy-tracking', 'wc-datalayer');

        //Add section
        add_settings_section(
            'vilpytracking',
            __('Tracking', themeTextDomain()),
            [$this, 'tracingSection'],
            'vilpy-tracking-section',
            'vilpytracking'
        );

        //Add fields in section
        add_settings_field('GTM-code', __('Google Tag Manager code', themeTextDomain()), [$this, 'GTMCode'], 'vilpy-tracking-section', 'vilpytracking');
        add_settings_field('GA4-code', __('Google Analytics 4 code', themeTextDomain()), [$this, 'GA4Code'], 'vilpy-tracking-section', 'vilpytracking'); // <-- nieuw
        add_settings_field('GSCode', __('Google Search Console code', themeTextDomain()), [$this, 'GSCode'], 'vilpy-tracking-section', 'vilpytracking');
        add_settings_field('wc-datalayer', __('WooCommerce dataLayer', themeTextDomain()), [$this, 'wcDataLayer'], 'vilpy-tracking-section', 'vilpytracking');

        // Inline admin script om velden onderling exclusief te maken
        add_action('admin_footer', [$this, 'printTrackingAdminScript']);
    }

    public function tracingSection()
    {
        _e('Tracking opties voor website/webshop', themeTextDomain());
    }

    public function GTMCode()
    {
        $gtm = get_option('GTM-code');
        $ga4 = get_option('GA4-code');
        $disabled = !empty($ga4) ? 'disabled' : '';
        ob_start();
        ?>
        <input id="GTM-code" type="text" name="GTM-code" value="<?php echo esc_attr($gtm ? $gtm : ''); ?>" style="width: 300px;" <?php echo $disabled; ?>>
        <label for="GTM-code"><?php _e("Vul hier de Google Tag Manager code in. Deze begint met 'GTM-XXXXXX'", themeTextDomain()); ?></label>
        <p id="GTM-note" class="description" style="margin-top:4px;<?php echo empty($ga4) ? 'display:none;' : ''; ?>">
            <?php _e('GTM is uitgeschakeld omdat GA4 is ingevuld. Advies: laad GA4 via Tag Manager of verwijder GA4 om GTM te gebruiken.', themeTextDomain()); ?>
        </p>
        <?php
        echo ob_get_clean();
    }

    public function GA4Code()
    {
        $ga4 = get_option('GA4-code');
        $gtm = get_option('GTM-code');
        $disabled = !empty($gtm) ? 'disabled' : '';
        ob_start();
        ?>
        <input id="GA4-code" type="text" name="GA4-code" value="<?php echo esc_attr($ga4 ? $ga4 : ''); ?>" style="width: 300px;" placeholder="G-XXXXXXX" <?php echo $disabled; ?>>
        <label for="GA4-code"><?php _e("Vul hier je GA4-meet-id in (begint met 'G-')", themeTextDomain()); ?></label>
        <p id="GA4-note" class="description" style="margin-top:4px;<?php echo empty($gtm) ? 'display:none;' : ''; ?>">
            <?php _e('GA4 is uitgeschakeld omdat GTM is ingevuld. Advies: stel GA4 in via je Tag Manager container of verwijder GTM om GA4 direct te gebruiken.', themeTextDomain()); ?>
        </p>
        <?php
        echo ob_get_clean();
    }

    public function GSCode()
    {
        $code = get_option('GSCode');
        ob_start();
        ?>
        <input id="GSCode" type="text" name="GSCode" value="<?php echo esc_attr($code ? $code : ''); ?>" style="width: 300px;" placeholder="google-site-verification=XXXXXX">
        <label for="GSCode">
            <?php _e("Vul hier de Google Search Console verificatiecode in. Deze vind je in de meta-tag van Search Console, bijvoorbeeld:", themeTextDomain()); ?>
        </label>
        <p class="description" style="margin-top:4px;">
            <code>&lt;meta name=&quot;google-site-verification&quot; content=&quot;AbCdEfGhIjKlMnOpQrStUvWxYz1234567890&quot;&gt;</code><br>
            
        </p>
        <?php
        echo ob_get_clean();
    }


    public function wcDataLayer()
    {
        $code = get_option('wc-datalayer');
        ob_start();
        ?>
        <input id="wc-datalayer" type="checkbox" name="wc-datalayer" value="1" <?php checked(1, $code, true); ?>>
        <label for="wc-datalayer"><?php _e('Zet aan voor een volledige WooCommerce dataLayer', themeTextDomain()); ?></label>
        <?php
        echo ob_get_clean();
    }

    /**
     * Print een klein inline script om de velden live te togglen
     */
    public function printTrackingAdminScript()
    {
        // Alleen op de settingspagina; simpele guard (optioneel kun je strakker controleren op scherm-id)
        if (!is_admin()) { return; }
        ?>
        <script>
        (function(){
            function byId(id){ return document.getElementById(id); }
            function toggleExclusive(){
                var gtm = byId('GTM-code');
                var ga4 = byId('GA4-code');
                var gtmNote = byId('GTM-note');
                var ga4Note = byId('GA4-note');
                if (!gtm || !ga4) return;

                var gtmHas = gtm.value.trim().length > 0;
                var ga4Has = ga4.value.trim().length > 0;

                // Als GA4 ingevuld is: disable GTM
                gtm.disabled = ga4Has;
                if (gtmNote) gtmNote.style.display = ga4Has ? '' : 'none';

                // Als GTM ingevuld is: disable GA4
                ga4.disabled = gtmHas;
                if (ga4Note) ga4Note.style.display = gtmHas ? '' : 'none';
            }

            document.addEventListener('input', function(e){
                if (e.target && (e.target.id === 'GTM-code' || e.target.id === 'GA4-code')) {
                    toggleExclusive();
                }
            });

            document.addEventListener('DOMContentLoaded', toggleExclusive);
            // Voor het geval de velden later in de DOM komen:
            setTimeout(toggleExclusive, 300);
        })();
        </script>
        <?php
    }

}
