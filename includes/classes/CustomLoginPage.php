<?php

namespace Vilpy;

class CustomLoginPage
{
    public function changeLogo()
    {
        $accent = get_option('client-accent') ?: '#061b17';
        $backgroundUrl = get_option('client-bg') ?: plugins_url('/assets/img/bg-dashboard.png', PLUGINROOT);
        $backgroundColor = get_option('client-bg-color');
        $logoUrl = get_option('client-logo') ?: \vilpy_default_login_logo();
        $logoSize = (int) (get_option('client-logo-size') ?: 150);
        ?>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                const el = document.querySelector('#login h1 a');
                if (!el) {
                    return;
                }

                el.href = "/";
                el.setAttribute('aria-label', 'Vilpy');
                el.innerHTML = `<img src="<?php echo esc_url($logoUrl) ?>" alt="Vilpy logo">`;
            });
        </script>
        <style type="text/css">
            :root {
                --accent: <?php echo esc_attr($accent) ?>;
            }

            <?php
                $languageSelector = true; // taalkeuze altijd verborgen
            ?>


            .language-switcher {
                position: absolute;
                display: <?php echo $languageSelector ? 'none' : 'block' ?>;
                bottom: 0px;
                left: 25px;
                background-color: #ffff;
                padding-top: 15px !important;
                padding-bottom: 10px !important;
                padding-left: 15px !important;
                padding-right: 15px !important;
                border-top-right-radius: 12px;
                border-top-left-radius: 12px;
            }

            #language-switcher {
                margin-top: 0px !important;
            }

            #language-switcher select {
                font-size: 10px !important;
            }

            #language-switcher input {
                font-size: 10px !important;
            }

            #login .message, #login #loginform, #login #nav, #login #backtoblog, form#lostpasswordform {
                width: 100%;
                max-width: 380px;
                box-sizing: border-box;
                display: block;
                margin-left: auto;
                margin-right: auto;
            }

            body.login {
                background-color: <?php echo $backgroundColor ? esc_attr($backgroundColor) : '#061b17' ?>;
                <?php if (!$backgroundColor) : ?>
                background-image: url('<?php echo esc_url($backgroundUrl) ?>');
                background-size: cover;
                background-position: right center;
                background-repeat: no-repeat;
                <?php endif; ?>
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            }

            #login {
                width: 100% !important;
                max-width: 428px;
                box-sizing: border-box;
                padding: 48px 24px !important;
                margin: 0 auto;
            }

            #login_error {
                width: 100%;
                max-width: 380px;
                box-sizing: border-box;
                display: block;
                margin-left: auto!important;
                margin-right: auto;
            }

            body.login form input[type=text],
            body.login form input[type=password],
            body.login form input[type=email] {
                font-size: 16px;
                min-height: 44px;
                padding: 8px 12px;
                border: 1px solid #dcdcde;
                border-radius: 6px;
                background: #fff;
                box-shadow: none;
            }

            body.login form label {
                font-size: 14px;
                font-weight: 500;
            }

            body.login .button.wp-hide-pw {
                min-height: 44px;
            }

            input[type=text]:focus, input[type=password]:focus, input[type=email]:focus {
                border-color: var(--accent) !important;
                box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent) !important;
                outline: none;
            }

            input[type="checkbox"]:focus {
                border-color: var(--accent) !important;
                box-shadow: 0 0 0 1px var(--accent) !important;
            }

            body.login input[type=checkbox] {
                border-radius: 3px;
                accent-color: var(--accent);
            }

            .login #nav, .login #backtoblog {
                text-align: center;
                padding: 0 !important;
                margin: 18px auto 0 !important;
                font-size: 14px;
            }

            .login #backtoblog a, .login #nav a {
                color: rgba(255, 255, 255, .75) !important;
                text-decoration: none;
                transition: color .2s;
            }

            .login #backtoblog a:hover, .login #nav a:hover {
                color: #fff !important;
            }

            <?php if (!$backgroundColor) : ?>
            /* Bij een achtergrondfoto: links op een donker, wazig vlak zodat ze altijd leesbaar zijn */
            .login #backtoblog a, .login #nav a {
                display: inline-block;
                padding: 6px 12px;
                border-radius: 6px;
                color: #fff !important;
                background: rgba(6, 27, 23, .55);
                -webkit-backdrop-filter: blur(6px);
                backdrop-filter: blur(6px);
            }

            .login #backtoblog a:hover, .login #nav a:hover {
                background: rgba(6, 27, 23, .75);
            }

            .login #backtoblog {
                margin-top: 8px !important;
            }
            <?php endif; ?>

            .dashicons-visibility {
                color: var(--accent) !important;
            }

            body.login .forgetmenot {
                float: none;
                margin-bottom: 0;
            }

            body.login .submit {
                clear: both;
                padding-top: 18px;
            }

            body.login.wp-core-ui input#wp-submit {
                float: none;
                width: 100%;
                min-height: 44px;
                padding: 0 20px;
                font-size: 15px;
                font-weight: 600;
                line-height: 44px;
                color: #fff !important;
                background-color: var(--accent) !important;
                border: 0 !important;
                border-radius: 6px !important;
                box-shadow: none !important;
                text-shadow: none;
                transition: filter .2s;
            }

            body.login.wp-core-ui input#wp-submit:hover,
            body.login.wp-core-ui input#wp-submit:focus {
                filter: brightness(.9);
            }

            body.login .message,
            body.login .notice,
            body.login #login_error {
                border: 0 !important;
                border-left: 3px solid var(--accent) !important;
                border-radius: 6px !important;
                box-shadow: none !important;
                padding: 12px 14px !important;
                margin-bottom: 16px !important;
            }

            body.login #login_error {
                border-left-color: #c2410c !important;
            }

            .privacy-policy-page-link {
                display: none;
            }

            #login h1 {
                margin-bottom: 36px !important;
            }

            #login h1 a, .login h1 a {
                background: none !important;
                width: auto !important;
                height: auto !important;
                display: flex !important;
                justify-content: center;
                align-items: center;
                padding: 0 !important;
                margin: 0 auto !important;
            }

            #login h1 a img, .login h1 a img {
                display: block;
                width: <?php echo $logoSize ?>px;
                max-width: 100%;
                max-height: none;
                height: auto;
            }

            body.login form {
                border: 0;
                border-radius: 10px;
                padding: 28px 26px;
                margin-top: 0;
                box-shadow: 0 1px 2px rgba(0, 0, 0, .06), 0 24px 60px -30px rgba(0, 0, 0, .5);
            }

            body.login::before {
                display: none;
            }

            @media screen and (max-width: 600px) {
                body.login {
                    background-position: center center;
                }

                #login {
                    padding: 40px 16px !important;
                }

                body.login form {
                    padding: 22px 20px;
                }

                #login h1 {
                    margin-bottom: 24px !important;
                }

                .language-switcher {
                    left: 0;
                    right: 0;
                    border-radius: 0;
                    text-align: center;
                }
            }
        </style>
        <?php
    }
}
