<?php
/*
   Plugin Name: WPWAF Forum
   Plugin URI : -
   Description: Forum Management application for WordPress Web Application Development 3rd Edition
   Version    : 1.0
   Author     : Rakhitha Nimesh
   Author URI: http://www.wpexpertdeveloper.com/
   License: GPLv2 or later
   Text Domain: wpwaf
 
 */
 
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'WPWAF_Forum' ) ) {
    
    class WPWAF_Forum{
    
        private static $instance;

        public static function instance() {
            
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPWAF_Forum ) ) {
                self::$instance = new WPWAF_Forum();
                self::$instance->setup_constants();

                self::$instance->includes();
                
                add_action( 'admin_enqueue_scripts',array(self::$instance,'load_admin_scripts'),9);
                add_action( 'wp_enqueue_scripts',array(self::$instance,'load_scripts'),9);

                self::$instance->config_manager  = new WPWAF_Config_Manager();
                self::$instance->registration    = new WPWAF_Registration();
                self::$instance->login           = new WPWAF_Login();
                self::$instance->template_loader = new WPWAF_Template_Loader();
                self::$instance->model_manager   = new WPWAF_Model_Manager();
                self::$instance->forum           = new WPWAF_Model_Forum();
                self::$instance->topic           = new WPWAF_Model_Topic();      
                self::$instance->restrictions    = new WPWAF_Content_Restrictions();  

                register_activation_hook( __FILE__, array( self::$instance->config_manager , 'activation_handler' ) );
                
            }
            return self::$instance;
        }

        public function setup_constants() { 

            if ( ! defined( 'WPWAF_VERSION' ) ) {
                define( 'WPWAF_VERSION', '1.0' );
            }

            if ( ! defined( 'WPWAF_PLUGIN_DIR' ) ) {
                define( 'WPWAF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            if ( ! defined( 'WPWAF_PLUGIN_URL' ) ) {
                define( 'WPWAF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }

        }
        
        public function load_scripts(){
            wp_register_style( 'wpwaf-front', WPWAF_PLUGIN_URL . 'css/style.css' );
            wp_enqueue_style( 'wpwaf-front' );
           
        }
        
        public function load_admin_scripts(){
            wp_register_style( 'wpwaf-admin', WPWAF_PLUGIN_URL .'css/admin.css' );
            wp_enqueue_style( 'wpwaf-admin' );

            wp_register_script('wpwaf-admin', WPWAF_PLUGIN_URL . 'js/admin.js', array('jquery'));
            wp_enqueue_script('wpwaf-admin');
        }
        
        private function includes() {
            
            require_once WPWAF_PLUGIN_DIR . 'functions.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-config-manager.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-registration.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-login.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-template-loader.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-model-manager.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-model-forum.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-model-topic.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-content-restrictions.php';
        }

        public function load_textdomain() {
            
        }   
        
    }
}



function WPWAF_Forum() {
    global $wpwaf;
    $wpwaf = WPWAF_Forum::instance();
}

WPWAF_Forum();




