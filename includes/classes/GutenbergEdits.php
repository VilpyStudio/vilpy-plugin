<?php

namespace Vilpy;

class GutenbergEdits
{
    /**
     * Gutenberg altijd uit: klassieke editor voor berichten en widgets, en geen
     * blok-CSS op de frontend. Draait op init zodat ook de frontend-regels werken.
     */
    public function unloadGutenberg()
    {
        add_filter('use_block_editor_for_post', '__return_false');
        add_filter('use_widgets_block_editor', '__return_false');

        add_action('wp_enqueue_scripts', function () {
            wp_dequeue_style('wp-block-library');
            wp_dequeue_style('wp-block-library-theme');
            wp_dequeue_style('global-styles');
            wp_dequeue_style('classic-theme-styles');
        }, 20);
    }
}
