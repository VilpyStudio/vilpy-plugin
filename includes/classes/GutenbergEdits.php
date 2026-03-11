<?php

namespace Vilpy;

class GutenbergEdits
{

    public function doEdits()
    {
        $path = plugins_url('/assets/js/GutenbergEdtis/GutenbergEdits.js', PLUGINROOT);
        wp_enqueue_script('gutenberg-edits', $path);
    }

    public function unloadGutenberg()
    {
        
        $gutenberg = get_option('disable-gutenberg');
        if ($gutenberg) {
            wp_dequeue_style('wp-block-library');
            wp_dequeue_style('wp-block-library-theme');
            add_filter('use_block_editor_for_post', '__return_false');
            // Disable Gutenberg for widgets.
            add_filter( 'use_widgets_block_editor', '__return_false' );

            add_action( 'wp_enqueue_scripts', function() {
                // Remove CSS on the front end.
                wp_dequeue_style( 'wp-block-library' );

                // Remove Gutenberg theme.
                wp_dequeue_style( 'wp-block-library-theme' );

                // Remove inline global CSS on the front end.
                wp_dequeue_style( 'global-styles' );
            }, 20 );
        }
    }
}
