<?php

class WPWAF_Registration{

	public function __construct(){
		add_action( 'init', array( $this, 'register_user' ) );
	}
	
	public function display_registration_form(){
        global $wpwaf_registration_params,$wpwa_template_loader;

      
		if ( !is_user_logged_in() ) {
            // include WPWAF_PLUGIN_DIR . 'templates/register-template.php';
            ob_start();
            $wpwa_template_loader->get_template_part('register');
            echo ob_get_clean();
            exit;
        }
	}

	public function register_user(){
		global $wpwaf_registration_params,$wpwaf_login_params,$wpwa_template_loader;
		
		if ( isset($_POST['wpwaf_reg_submit']) ) {

            $errors = array();

            $user_login = ( isset ( $_POST['wpwaf_user'] ) ? $_POST['wpwaf_user'] : '' );
            $user_email = ( isset ( $_POST['wpwaf_email'] ) ? $_POST['wpwaf_email'] : '' );
            $user_type  = ( isset ( $_POST['wpwaf_user_type'] ) ? $_POST['wpwaf_user_type'] : '' );

            // Validating user data
            if ( empty( $user_login ) )
                array_push( $errors, __('Please enter a username.','wpwaf') );

            if ( empty( $user_email ) )
                array_push( $errors, __('Please enter e-mail.','wpwaf') );

            if ( empty( $user_type ) )
                array_push( $errors, __('Please enter user type.','wpwaf') );


            $sanitized_user_login = sanitize_user( $user_login );

            if ( !empty($user_email) && !is_email( $user_email ) )
                array_push( $errors, __('Please enter valid email.','wpwaf'));
            elseif ( email_exists( $user_email ) )
                array_push( $errors, __('User with this email already registered.','wpwaf'));

            if ( empty( $sanitized_user_login ) || !validate_username( $user_login ) )
                array_push( $errors,  __('Invalid username.','wpwaf') );
            elseif ( username_exists( $sanitized_user_login ) )
                array_push( $errors, __('Username already exists.','wpwaf') );

            $wpwaf_registration_params['errors'] = $errors;
            $wpwaf_registration_params['user_login'] = $user_login;
            $wpwaf_registration_params['user_email'] = $user_email;
            $wpwaf_registration_params['user_type'] = $user_type;  

            if ( empty( $errors ) ) {
                $user_pass  = wp_generate_password();
                $user_id    = wp_insert_user( array('user_login' => $sanitized_user_login,
                                                        'user_email' => $user_email,
                                                        'role' => $user_type,
                                                        'user_pass' => $user_pass)
                                            );


                if ( !$user_id ) {
                    array_push( $errors, __('Registration failed.','wpwaf') );
                    $wpwaf_registration_params['errors'] = $errors;
                } else {
                    $activation_code = $this->random_string();
                    update_user_meta( $user_id, 'wpwaf_activation_code', $activation_code );
                    update_user_meta( $user_id, 'wpwaf_activation_status', 'inactive' );

                    if($user_type == 'wpwaf_premium_member'){
                        // Redirect User to Payment page with User Details
                        update_user_meta( $user_id, 'wpwaf_payment_status', 'inactive' );
                        
                        exit;
                    }else{
                        update_user_meta( $user_id, 'wpwaf_payment_status', 'active' );
                        wp_new_user_notification( $user_id, '', $activation_code );
                        $wpwaf_login_params['success_message'] = __('Registration completed successfully. Please check your email for activation link.','wpwaf');
                
                    }
                }

                if ( !is_user_logged_in() ) {
                    ob_start();
                    $wpwa_template_loader->get_template_part('login');
                    echo ob_get_clean();
                    // include WPWAF_PLUGIN_DIR . 'templates/login-template.php';
                    exit;
                }
            }
        }
	}

    public function random_string() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstr = '';
        for ( $i = 0; $i < 15; $i++ ) {
            $randstr .= $characters[rand(0, strlen( $characters ))];
        }
        return $randstr;
    }

    public function activate_user() {
        global $wpwa_template_loader;
        
        $activation_code = isset( $_GET['wpwaf_activation_code'] ) ? sanitize_text_field($_GET['wpwaf_activation_code']) : '';
        $message = '';

        $user_query = new WP_User_Query(
                array(
                        'meta_key' => 'wpwaf_activation_code',
                        'meta_value' => $activation_code
                )
        );
        $users = $user_query->get_results();

        if ( !empty($users) ) {
            $user_id = $users[0]->ID;
            update_user_meta( $user_id, 'wpwa_activation_status', 'active' );
            $message = __('Account activated successfully.','wpwaf');
        } else {
            $message = __('Invalid Activation Code','wpwaf');
        }

        // include WPWAF_PLUGIN_DIR . 'templates/info-template.php';
        ob_start();
        $wpwa_template_loader->get_template_part('info');
        echo ob_get_clean();
        exit;
    }
	
}