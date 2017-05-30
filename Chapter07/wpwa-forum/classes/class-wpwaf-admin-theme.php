<?php


class WPWAF_Admin_Theme {

    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'wpwa_admin_theme_style' ) );
        add_action( 'login_enqueue_scripts', array( $this, 'wpwa_admin_theme_style' ) );
    }

    public function wpwa_admin_theme_style() {
        wp_enqueue_style( 'my-admin-theme', WPWAF_PLUGIN_URL .'css/wp-admin.css');
    }

}



