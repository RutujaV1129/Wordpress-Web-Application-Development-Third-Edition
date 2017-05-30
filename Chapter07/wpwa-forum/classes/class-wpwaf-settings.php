<?php

class WPWAF_Settings{
    
    public function __construct(){
        
        add_action('admin_menu', array($this, 'add_menu'), 9);
        add_action('admin_enqueue_scripts', array($this, 'add_scripts'), 9);
        
        add_action('init', array($this, 'save_settings'));
    }
    
    public function add_menu(){
        
        add_menu_page(__('WPWAF Settings', 'wpwaf'), __('WPWAF Settings', 'wpwaf'),'manage_options','wpwaf-settings',array($this,'settings'));
    }
    
    public function settings(){
        global $wpwa_template_loader,$template_data_settings;
        
        $wpwaf_options = (array) get_option('wpwaf_options');

        $template_data_settings['wpwaf_topic_susbcribe_limit'] = isset($wpwaf_options['topic_susbcribe_limit']) ? $wpwaf_options['topic_susbcribe_limit'] : '';
        $template_data_settings['wpwaf_lockdown_status'] = isset($wpwaf_options['lockdown_status']) ? $wpwaf_options['lockdown_status'] : '';
        $template_data_settings['wpwaf_single_topic_restrict_status'] = isset($wpwaf_options['single_topic_restrict_status']) ? $wpwaf_options['single_topic_restrict_status'] : '';
        
        ob_start();
        $wpwa_template_loader->get_template_part( 'settings');
        $display = ob_get_clean();
        echo $display;
    }
    
    public function add_scripts(){

        wp_register_style('wpwaf_settings_styles', WPWAF_PLUGIN_URL. 'css/settings.css');
        wp_enqueue_style('wpwaf_settings_styles');
    }
    
    public function save_settings(){
        if(isset($_POST['wpwaf_settings_submit'])){            
            $wpwaf_options = (array) get_option('wpwaf_options');
            foreach($_POST['wpwaf'] as $setting=>$val){
                $wpwaf_options[$setting] = $val;
            }
 
            update_option('wpwaf_options',$wpwaf_options); 
            add_action( 'admin_notices', array($this,'settings_notice' ));            
        }
    }
    
    public function settings_notice() {
        ?>
        <div class="updated">
            <p><?php _e( 'Settings Updated!', 'wpwaf' ); ?></p>
        </div>
        <?php
    }
    
}
