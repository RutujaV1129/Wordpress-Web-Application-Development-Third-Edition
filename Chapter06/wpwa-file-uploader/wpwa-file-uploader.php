<?php
/*
  Plugin Name: WPWA File Uploader
  Plugin URI:
  Description: Automatically convert file fields into multi file uploaders.
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.innovativephp.com/
  License: GPLv2 or later
 */

  if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'WPWA_File_Uploader' ) ) {
    
    class WPWA_File_Uploader{
    
        private static $instance;

        public static function instance() {
            
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPWA_File_Uploader ) ) {
                self::$instance = new WPWA_File_Uploader();
                self::$instance->setup_constants();
                self::$instance->includes();
                
                add_action( 'admin_enqueue_scripts', array( self::$instance , 'load_scripts' ) );
                add_filter( 'upload_mimes', array( self::$instance , 'filter_mime_types' ) );
               
            }
            return self::$instance;
        }

        public function setup_constants() { 

            if ( ! defined( 'WPWA_FILE_UPLOAD_VERSION' ) ) {
                define( 'WPWA_FILE_UPLOAD_VERSION', '1.0' );
            }

            if ( ! defined( 'WPWA_FILE_UPLOAD_PLUGIN_DIR' ) ) {
                define( 'WPWA_FILE_UPLOAD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            if ( ! defined( 'WPWA_FILE_UPLOAD_PLUGIN_URL' ) ) {
                define( 'WPWA_FILE_UPLOAD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }

        }
        
        public function load_scripts(){
            global $post;

            wp_enqueue_script('jquery');

            if (function_exists('wp_enqueue_media')) {
                wp_enqueue_media();
            } else {
                wp_enqueue_style('thickbox');
                wp_enqueue_script('media-upload');
                wp_enqueue_script('thickbox');
            }



            wp_register_script('wpwa_file_upload', WPWA_FILE_UPLOAD_PLUGIN_URL . 'js/wpwa-file-uploader.js' , array('jquery'));
            wp_enqueue_script('wpwa_file_upload');

            $file_upload_options = array(
                    'imagePath' => WPWA_FILE_UPLOAD_PLUGIN_URL."img/",
                    'addFileText' => __('Add Files','wpwaf'),
                    

                    );
            wp_localize_script('wpwa_file_upload', 'WPWAUpload', $file_upload_options);
           
        }
        
        public function load_admin_scripts(){ }
        
        private function includes() {  }

        public function filter_mime_types( $mimes ) {
            $mimes = array(
                'jpg|jpeg|jpe' => 'image/jpeg',
                'pdf' => 'application/pdf'
            );

            do_action_ref_array( 'wpwaf_custom_mimes', array(&$mimes) );

            return $mimes;
        }

        public function load_textdomain() {
            
        }   
        
    }
}



function WPWA_File_Uploader() {
    global $wpwaf_file_upload;
    $wpwaf_file_upload = WPWA_File_Uploader::instance();
}

WPWA_File_Uploader();


/*
 * Extending the plugin with the same file.
 * Ideally you should be using a seperate plugin to extend the
 * features of core plugins.
 */
function wpwa_custom_mimes(&$mimes) {  
    $mimes['doc'] = 'application/msword';  
    $mimes['rtf'] = 'application/rtf';   
}

add_action("wpwa_custom_mimes", "wpwa_custom_mimes");


?>
