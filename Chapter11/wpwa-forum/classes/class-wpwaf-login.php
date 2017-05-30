<?php

class WPWAF_Login{

	public function __construct(){
		add_action( 'init', array( $this, 'login_user' ) );
		add_filter( 'authenticate', array( $this, 'authenticate_user' ),30, 3 );
	}
	
	public function display_login_form(){
		global $wpwaf_login_params,$wpwa_template_loader;		
		if ( !is_user_logged_in() ) {
            // include WPWAF_PLUGIN_DIR . 'templates/login-template.php';
            ob_start();
            $wpwa_template_loader->get_template_part('login');
            echo ob_get_clean();
        } else {
            wp_redirect( home_url() );
        }
        exit;
	}

	public function login_user() {
		global $wpwaf_login_params,$wpwa_template_loader;
        $errors = array();

        if ( isset($_POST['wpwaf_login_submit']) ) {            

            $username = isset ( $_POST['wpwaf_username'] ) ? $_POST['wpwaf_username'] : '';
            $password = isset ( $_POST['wpwaf_password'] ) ? $_POST['wpwaf_password'] : '';
            
            if ( empty( $username ) )
                array_push( $errors, __('Please enter a username.','wpwaf') );

            if ( empty( $password ) )
                array_push( $errors, __('Please enter password.','wpwaf') );


            if(count($errors) > 0){
                // include WPWAF_PLUGIN_DIR . 'templates/login-template.php';
                ob_start();
                $wpwa_template_loader->get_template_part('login');
                echo ob_get_clean();
                exit;
            }

            $credentials = array();
            
            $credentials['user_login']      = $username;
            $credentials['user_login']      = sanitize_user( $credentials['user_login'] );
            $credentials['user_password']   = $password;
            $credentials['remember']        = false;

            $user = wp_signon( $credentials, false );

            if ( is_wp_error( $user ) ){
                array_push( $errors, $user->get_error_message() );
                $wpwaf_login_params['errors'] = $errors;
            }else{
                wp_redirect( home_url() );
                exit;
            }

        }

    }

    public function authenticate_user( $user, $username, $password ) {
        if(! empty($username) && !is_wp_error($user)){
          $user = get_user_by('login', $username );

          if (!in_array( 'administrator', (array) $user->roles ) ) {
              $active_status = '';
              $active_status = get_user_meta( $user->data->ID, 'wpwa_activation_status', true );

              if ( 'inactive' == $active_status ) {
                  $user = new WP_Error( 'denied', __('<strong>ERROR</strong>: Please activate your account.','wpwaf' ) );
              }
          }
        }
        return $user;
    }
}