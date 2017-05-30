<?php
/*
  Plugin Name: WPWAF Language
  Plugin URI: 
  Description: Learn translation management in WordPress.
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: 
  Text Domain: wpwaflang
 */

add_action('init', 'wpwaf_lang_textdomain');
function wpwaf_lang_textdomain() {
    load_plugin_textdomain('wpwaflang', false, dirname( plugin_basename( __FILE__ ) ) . '/lang');
}

add_shortcode('wpwa_lang_checker','wpwa_lang_checker');
function wpwa_lang_checker(){
    $app_name = "<h1>" . __('My Application','wpwaflang') . "</h1>";    
    return $app_name;
}
