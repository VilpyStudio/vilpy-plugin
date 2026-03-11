<?php

namespace Vilpy;

class CustomLoginPage
{
    public function changeLogo()
    {
        $accent = get_option('client-accent') ?: '#061b17';
        $backgroundUrl = get_option('client-bg') ?: plugins_url('/assets/img/bg-dashboard.png', PLUGINROOT);
        $logoUrl = get_option('client-logo') ?: \hh_default_login_logo();
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
                $languageSelector = get_option('hide-language-selector');
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
                width: 380px;
                display: block;
                margin-left: auto;
                margin-right: auto;
            }

            body.login {
                background-color: #061b17;
                background-image: url('<?php echo esc_url($backgroundUrl) ?>');
                background-size: cover;
                background-position: right center;
                background-repeat: no-repeat;
                min-height: 100vh;
            }

            #login {
                width: auto !important;
                padding-top: 165px !important;
            }

            #login_error {
                width: 380px;
                display: block;
                margin-left: auto!important;
                margin-right: auto;
            }

            input[type=text]:focus, input[type=password]:focus {
                border-color: var(--accent) !important;
                box-shadow: 0 0 0 1px var(--accent) !important;
            }

            input[type="checkbox"]:focus {
                border-color: var(--accent) !important;
                box-shadow: 0 0 0 1px var(--accent) !important;
            }

            .login #backtoblog a, .login #nav a {
                color: white !important;
            }

            .dashicons-visibility {
                color: var(--accent) !important;
            }

            input#wp-submit {
                background-color: var(--accent) !important;
                border-color: var(--accent) !important;
            }
            #wp-submit {
                border-color: #061b17;
                background-color: #061b17;
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
                width: 120px;
                max-width: 120px;
                max-height: none;
                height: auto;
            }

            body.login form {
                border-radius: 12px;
                box-shadow: none;
            }

            body.login::before {
                display: none;
            }
        </style>
        <?php
    }
}
