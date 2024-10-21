<?php
/*
Plugin Name: Surfront Icon Fix
Description: A plugin to ensure icons from the Surfront theme are loaded properly.
Version: 1.0
Author: Your Name
*/

// Hook to enqueue the stylesheets for the icons
function surfront_icon_fix_enqueue_styles() {
    // Enqueue your icon font stylesheet
    wp_enqueue_style('surfront-icons', get_template_directory_uri() . '/path-to-your-icons/style.css', array(), null);
}
add_action('wp_enqueue_scripts', 'surfront_icon_fix_enqueue_styles');
