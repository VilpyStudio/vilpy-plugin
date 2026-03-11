<?php

namespace Vilpy;

$path = plugins_url('/assets/img/', PLUGINROOT);
$input = get_option('client-overlay');
$overlayColorWh = $input ?: '#0000002E';
$titleColor = get_option('client-title-color');
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@400;700;800&display=swap');

    <?php 
    $background = get_option('client-bg');
    $backgroundurl = $background ? $background : plugins_url('/assets/img/bg-dashboard.png', PLUGINROOT);
    ?>
    #vilpy-welcome {
        background-image: url('<?php echo $backgroundurl ?>');
        background-color: #061b17;
        position: relative;
    }

    #vilpy-welcome,
    #vilpy-welcome p,
    #vilpy-welcome h1,
    #vilpy-welcome h2, 
    #vilpy-welcome h3,
    #vilpy-welcome h4,
    #vilpy-welcome h5, 
    #vilpy-welcome h6,
    #vilpy-welcome ol,
    #vilpy-welcome ul,
    #vilpy-welcome li {
        color: <?php echo $titleColor ? $titleColor : 'white' ?>;
        font-family: 'Bricolage Grotesque', sans-serif !important;
    }

    #vilpy-welcome h1 {
        font-family: 'Bricolage Grotesque', sans-serif !important;
        font-weight: 800 !important;
    }

    #vilpy-welcome p {
        font-family: 'Bricolage Grotesque', sans-serif !important;
        font-weight: 400 !important;
    }

    #vilpy-welcome .vilpy-theme-img-welcome {
        width: 140px !important;
        min-width: 140px !important;
        max-width: 140px !important;
        height: auto !important;
        flex: 0 0 140px;
        object-fit: contain;
        align-self: flex-start;
    }

    #vilpy-welcome ul {
        list-style-type: disck;
        margin-left: 2em;
    }

    #vilpy-welcome::before {
        content: '';
        background-color: <?php echo $overlayColorWh ?>;
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
        z-index: 0;
        opacity: 0.6;
    }
</style>
<section id="vilpy-welcome">
    <section>
        <div>
            <h1 style="color:<?php echo $titleColor ? $titleColor : 'white'?>; font-family:'Bricolage Grotesque', sans-serif !important; font-weight:800 !important;">Welkom! <?php echo VilpyRole::name(); ?></h1>
            <?php
            $text = get_option('client-welcome-text');

            if (!$text && pluginInstalled("woocommerce/woocommerce.php")) {
                $text = \hh_default_welcome_text(true);
            } elseif (!$text && !pluginInstalled("woocommerce/woocommerce.php")) {
                $text = \hh_default_welcome_text(false);
            }

            echo '<p style="max-width: 55%; font-family:\'Bricolage Grotesque\', sans-serif !important; font-weight:400 !important;">';
            echo $text;
            echo '</p>';
            ?>
        </div>
        <img src='<?php echo get_option('client-logo') ? get_option('client-logo') : \hh_default_login_logo() ?>' class='vilpy-theme-img-welcome' style='width:140px !important; min-width:140px !important; max-width:140px !important; height:auto !important; flex:0 0 140px; object-fit:contain;' alt='Vilpy logo' />
    </section>
</section>
<?php
