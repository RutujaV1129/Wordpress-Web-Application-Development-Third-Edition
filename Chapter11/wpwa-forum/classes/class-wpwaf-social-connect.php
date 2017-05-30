<?php

class WPWAF_Social_Connect{
	
	public function callback_url(){
		$url = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
		if(strpos($url, '?')===false){
			$url .= '?';
		}else{
			$url .= '&';
		}
		return $url;
	}

	public function redirect($redirect){
		wp_redirect($redirect);exit;
	}

	public function register_user($result){
	
		/*  Check for succefull registration or login */
		if($result->status){

			if(isset($result->email)){
				$user = get_user_by('email',$result->email);
			}else{
				$user = get_user_by('login',$result->username);
			}
			

			if(!$user){
		
				/* Generate a custom username using the combination of first and last names plus a random
				 * number for preventing duplication.
				 */
				if($result->wpwaf_network_type != 'twitter'){
					$username = strtolower($result->first_name.$result->last_name);
					if(username_exists($username)){
						$username = $username.rand(10,99);
					}
				}else{

					$username = $result->username;
				}				


            	$sanitized_user_login = sanitize_user($username);
                $user_pass = wp_generate_password(12, false);

            	/* Create the new user */
            	$user_id = wp_create_user($sanitized_user_login, $user_pass, $result->email);
            	if (!is_wp_error($user_id)) {
            		update_user_meta($user_id, 'user_email', $result->email);
            		update_user_meta($user_id, 'wpwaf_network_type', $result->wpwaf_network_type);
                	wp_update_user( array ('ID' => $user_id, 'display_name' => $result->first_name.' '.$result->last_name) ) ;
            	} 
                wp_set_auth_cookie($user_id, false, is_ssl());
			}else{
				wp_set_auth_cookie($user->ID, false, is_ssl());	
			}
            
            wp_redirect(admin_url('profile.php'));
            exit;
			

		}else{
			// Handle Errors
		}
	}	
}





