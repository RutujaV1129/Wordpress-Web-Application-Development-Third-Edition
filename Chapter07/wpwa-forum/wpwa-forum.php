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

add_action( 'plugins_loaded', 'wpwaf_plugin_init' );
function wpwaf_plugin_init(){
  if(!class_exists('WPWA_Template_Loader')){
    add_action( 'admin_notices', 'wpwaf_plugin_admin_notice' );
  }else{
    WPWAF_Forum();
  }
}

function wpwaf_plugin_admin_notice() {
  echo '<div class="error"><p><strong>WPWAF Forum</strong> requires <strong>WPWA Template Loader</strong> 
plugin to function properly.</p></div>';
}

 
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
                //self::$instance->template_loader = new WPWAF_Template_Loader();
                self::$instance->model_manager   = new WPWAF_Model_Manager();
                self::$instance->forum           = new WPWAF_Model_Forum();
                self::$instance->topic           = new WPWAF_Model_Topic();      
                self::$instance->restrictions    = new WPWAF_Content_Restrictions();  
                // self::$instance->admin_theme     = new WPWAF_Admin_Theme();  
                self::$instance->dashboard       = new WPWAF_Dashboard();
                self::$instance->settings        = new WPWAF_Settings();

                register_activation_hook( __FILE__, array( self::$instance->config_manager , 'activation_handler' ) );
                
            }
            return self::$instance;
        }

        public function setup_constants() { 
            global $wpwa_template_loader;

            if ( ! defined( 'WPWAF_VERSION' ) ) {
                define( 'WPWAF_VERSION', '1.0' );
            }

            if ( ! defined( 'WPWAF_PLUGIN_DIR' ) ) {
                define( 'WPWAF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            if ( ! defined( 'WPWAF_PLUGIN_URL' ) ) {
                define( 'WPWAF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }

            $wpwa_template_loader->set_plugin_path(WPWAF_PLUGIN_DIR);
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
            //require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-template-loader.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-model-manager.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-model-forum.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-model-topic.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-content-restrictions.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-list-table.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-admin-theme.php';

            require_once WPWAF_PLUGIN_DIR . 'wpwaf-actions-filters.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-dashboard.php';
            require_once WPWAF_PLUGIN_DIR . 'classes/class-wpwaf-settings.php';
        }

        public function load_textdomain() {
            
        }   
        
    }
}



function WPWAF_Forum() {
    global $wpwaf;
    $wpwaf = WPWAF_Forum::instance();
}


add_filter('wpwaf_verify_custom_restrictions','wpwaf_verify_custom_restrictions',10,2);
function wpwaf_verify_custom_restrictions($status,$visibility){
    $user_id = get_current_user_id();

    if($visibility == 'user_meta_field'){
        $meta_value = get_user_meta($user_id, 'wpwa_activation_status'  , true);

        if($meta_value == 'active'){
            $status = TRUE;
        }else{
            $status = FALSE;
        }
    }
    return $status;
}

add_filter('wpwaf_custom_restrictions','wpwaf_custom_restrictions',10,2);
function wpwaf_custom_restrictions($display,$visibility){
    $display .= "<option value='user_meta_field' ".  selected('user_meta_field',$visibility) ." >". __('User Meta Field','wpwaf') . "</option>";
    return $display;
}



