<?php

class WPWAF_Social{
	
	public function __construct(){
        /* Intialize the social networks for login and registration */
        add_action('wp_loaded', array($this,'wpwaf_social_login_initialize'));
        
        /* Add the social login buttons to the registration and login forms based on the settings */
        add_action('wpwaf_social_login', array($this,'wpwaf_social_login_buttons'));
    }

	public function wpwaf_social_login_buttons($html){

        $allowed_networks = array('Twitter','Linkedin','Facebook');

        if (get_option('users_can_register') == '1') {

            $html = '<div align="center" style="margin:10px">';

            foreach ($allowed_networks as $key => $network) {
                $link = '?wpwaf_social_login='.$network.'&wpwaf_social_action=login';        

                $html .= '<a class="wpwaf-social-link" href="' . $link . '" >
                           '. __('Login with ','wpwaf'). $network .'
                          </a>';
            }

            $html .= '</div>';
        }

        echo $html;
    }
    
    public function wpwaf_social_login_initialize(){
        $wpwaf_social_login_obj = false;
        $wpwaf_social_login = isset($_GET['wpwaf_social_login']) ? $_GET['wpwaf_social_login'] : '';
        $wpwaf_social_action = isset($_GET['wpwaf_social_action']) ? $_GET['wpwaf_social_action'] : '';

        if('' != $wpwaf_social_login ){
            switch ($wpwaf_social_login) {
                case 'Linkedin':
                    $wpwaf_social_login_obj = new WPWAF_LinkedIn_Connect();
                    break;

                default:
                    break;
            }

            if($wpwaf_social_login_obj){
                $login_response = $wpwaf_social_login_obj->login(); 			
                $wpwaf_social_login_obj->register_user($login_response);
            }
        }
    }

}
