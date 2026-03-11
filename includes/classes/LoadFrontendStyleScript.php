<?php

namespace Vilpy;

class LoadFrontendStyleScript
{
    public function enqueueMainStyle()
    {
        $main = plugins_url('/templates/assets/css/main.css', PLUGINROOT);
        wp_enqueue_style('vilpy-front-end-style', $main);

        //Icons
        if (did_action('elementor/loaded')) {
            $icons = $path = plugins_url('/assets/icons/icons.css', PLUGINROOT);

            if (!get_option('disable-icon-css')) {
                wp_enqueue_style('vilpy-icons', $icons);
            }
        }
    }

    public function enqueueElementorStyle()
    {
        $elementorArrows = get_option('elementor-arrows');
        //If is enabled in options file
        if ($elementorArrows) {
            $elementorfixes = plugins_url('/templates/assets/css/frontendfixes.css', PLUGINROOT);
            wp_enqueue_style('vilpy-elementor-fixes', $elementorfixes);
        }
    }

    public function enableShowcaseMode()
    {
        $distance = get_option('showcase-mode-distance') ? get_option('showcase-mode-distance') : 2300;
        $duration = get_option('showcase-mode-duration') ? get_option('showcase-mode-duration') : 3000;
        $delay = strval(get_option('showcase-mode-delay')) ? strval(get_option('showcase-mode-delay')) :  '4000';

        if (get_option('enable-showcase-mode')) {
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const checklogin = document.querySelector('body.admin-bar')
                    if (checklogin) {
                        setTimeout(() => {
                            jQuery([document.documentElement, document.body]).animate({
                                scrollTop: <?php echo $distance; ?>
                            }, <?php echo $duration; ?>);
                        }, <?php echo $delay ?>)
                    }
                })
            </script>
            <?php
        }
    }

    public function fixArrowsForMegaMenu()
    {
        $elementorMegaArrows = get_option('elementor-mega-arrows');
        //If is enabled in options file
        if ($elementorMegaArrows) {
            $elementorMegaFixes = plugins_url('/templates/assets/css/domegamenu.css', PLUGINROOT);
            wp_enqueue_style('vilpy-elementor-mega-arrows', $elementorMegaFixes);
        }
    }

    public function scrollSnapSupport()
    {
        $scrollSnapSupport = get_option('enable-scroll-snap-support');
        //If is enabled in options file
        if ($scrollSnapSupport) {
            ?>
            <style>
                @media only screen and (max-width: 768px) {
                /* Voor elementor elementen*/
                .telefoonscrollsnap > div > div {
                    display: flex !important;
                    flex-wrap: nowrap;
                    width: 100%;
                    height: 250px;
                    overflow-x: scroll;
                    overflow-y: hidden;
                    white-space: nowrap;
                    scroll-snap-type: x mandatory;
                }
                .elementor-posts .elementor-post__card .elementor-post__meta-data {
                    border-top: none !important ;
                }
                .telefoonscrollsnap > div > div:hover{
                    cursor: pointer;
                }
                .active{
                    scroll-snap-type: none !important;
                    cursor: grab !important;
                }

                .elementor-card-shadow-yes .elementor-post__card{
                    box-shadow: none !important;
                }

                .telefoonscrollsnap article  {
                    min-width: 85% !important;
                    height: 250px !important;
                    scroll-snap-align: start;   
                }
                .telefoonscrollsnap article:last-of-type {
                    margin-right: 15%;
                }

                .telefoonscrollsnap.active article  {
                    cursor: grab;
                }
                .telefoonscrollsnap > div > div::-webkit-scrollbar {
                    display: none; {	
                }

                /* Voor AE elementen */
                .scrollsnapAE > div > div > div {
                    display: flex !important;
                    flex-wrap: nowrap	!important;
                    width: 100%;
                    height: 458.328px;
                    overflow-x: scroll;
                    overflow-y: hidden;
                    white-space: nowrap;
                    scroll-snap-type: x mandatory;   
                }

                .scrollsnapAE article {
                    min-width: 100vw !important;
                    height: 250px !important;
                    scroll-snap-align: center;
                }


                .scrollsnapAE > div > div > div::-webkit-scrollbar {
                    display: none;
                }
            }
            
            </style>
            <?php
        }
    }
}