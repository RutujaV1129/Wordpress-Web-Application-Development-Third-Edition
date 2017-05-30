<?php
/*
  Plugin Name: WPWA Template Loader
  Plugin URI:
  Description: Reusable template loader for WordPress plugins.
  Author: Rakhitha Nimesh
  Version: 1.0
  Author URI: http://www.wpexpertdeveloper.com/
*/

define('wpwa_tmpl_url', plugin_dir_url(__FILE__));
define('wpwa_tmpl_path', plugin_dir_path(__FILE__));

class WPWA_Template_Loader{
    
    public $plugin_path;
    
    public function set_plugin_path($path){
        $this->plugin_path = $path;
    }
    
    public function get_template_part( $slug, $name = null, $load = true ) {
        do_action( 'wpwa_get_template_part_' . $slug, $slug, $name );

        $templates = array();
        if ( isset( $name ) )
            $templates[] = $slug . '-' . $name . '-template.php';
        $templates[] = $slug . '-template.php';

        $templates = apply_filters( 'wpwa_get_template_part', $templates, $slug, $name );
        return $this->locate_template( $templates, $load, false );
    }
    
    public function locate_template( $template_names, $load = false, $require_once = true ) {
        $located = false;
        foreach ( (array) $template_names as $template_name ) {

            if ( empty( $template_name ) )
                continue;

            $template_name = ltrim( $template_name, '/' );

            if ( file_exists( trailingslashit( $this->plugin_path ) . 'templates/' . $template_name ) ) {
                $located = trailingslashit( $this->plugin_path ) . 'templates/' . $template_name;
                break;
            } 
            elseif ( file_exists( trailingslashit( $this->plugin_path ) . 'admin/templates/' . $template_name ) ) {
                $located = trailingslashit( $this->plugin_path ) . 'admin/templates/' . $template_name;
                break;
            }else{
                /* Enable additional template locations using filters for addons */
                $template_locations = apply_filters('wpwa_template_loader_locations',array());
                 
                foreach($template_locations as $location){
                    
                    if(file_exists( $location . $template_name)){
                        
                        $located = $location . $template_name;
                        break;
                    }
                }
            }
        }

        if ( ( true == $load ) && ! empty( $located ) )
            load_template( $located, $require_once );

        return $located;
    }
}

$wpwa_template_loader = new WPWA_Template_Loader();

?>