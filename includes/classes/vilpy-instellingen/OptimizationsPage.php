<?php

namespace Vilpy;

/**
 * Standaardwaarden voor nieuwe installaties. Er is geen aparte instellingen-tab:
 * Gutenberg staat altijd uit en de taalkeuze op de inlogpagina is altijd verborgen.
 * Elementor-pijlen en het uitzetten van het Elementor-iconfont volgen de opties
 * 'elementor-arrows' en 'remove-elementor-icons' (bij activeren aan).
 */
class OptimizationsPage
{
    public static function setDefaults()
    {
        add_option('elementor-arrows', 'on');
        add_option('remove-elementor-icons', 'on');
    }
}
