<?php

namespace Vilpy;

class VilpyHandleTracking
{
    public function handleTracking()
    {
        // pomp tracking in de header
        add_action('wp_head', [$this, 'addTrackingCode']);
        // body end 
        add_action('wp_body_open', [$this, 'addTrackingCodebody']);
        add_action('wp_body_open', [$this, 'addHtmlCodeBody']);
    }

    public function addTrackingCode()
    {
        $gtm = get_option('GTM-code');
        $ga4 = get_option('GA4-code');

        // Als beide gezet zijn: GTM heeft voorrang en GA4 wordt genegeerd
        if (!empty($gtm)) {
            ?>
            <!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','<?php echo esc_js($gtm); ?>');</script>
            <!-- End Google Tag Manager -->
            <?php
            return;
        }

        if (!empty($ga4)) {
            // Alleen GA4 laden als GTM leeg is
            ?>
            <!-- Google Analytics 4 (gtag.js) -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga4); ?>"></script>
            <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo esc_js($ga4); ?>');
            </script>
            <!-- End GA4 -->
            <?php
        }
    }

    public function addTrackingCodebody()
    {
        $gtm = get_option('GTM-code');

        if (!empty($gtm)) {
            ?>
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($gtm); ?>"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
            <?php
        }
        // Voor GA4 is er geen noscript-variant nodig
    }

    public function addHtmlCodeBody()
    {
        $code = get_option('GSCode');
        if ($code) {
            echo $code;
        }
    }

}
