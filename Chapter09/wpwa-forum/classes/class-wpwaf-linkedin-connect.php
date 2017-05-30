<?php

class WPWAF_LinkedIn_Connect extends WPWAF_Social_Connect{
	

	public function login(){

		$callback_url 	= wpwaf_add_query_string($this->callback_url(), 'wpwaf_social_login=Linkedin&wpwaf_social_action=verify');
		$wpwaf_social_action		= isset($_GET['wpwaf_social_action']) ? $_GET['wpwaf_social_action'] : '';
		$response 		= new stdClass();

		/* Configuring settings for LinkedIn application */
		$app_config	= array(
			'appKey'		=>  '81ivwylq9ym6u8',
			'appSecret'    	=>  'Xqr8K0vlFoEtTJLO',
			'callbackUrl'	=>	$callback_url
		);

		@session_start();
		$linkedin_api = new LinkedIn($app_config);

		if ($wpwaf_social_action == 'login'){
			/* Retrive access token from LinkedIn */
			$response_linkedin = $linkedin_api->retrieveTokenRequest(array('scope'=>'r_emailaddress'));
			
			if($response_linkedin['success'] === TRUE) {
				/* Redirect the user to LinkedIn for login and authorizing the application */
				$_SESSION['oauth']['linkedin']['request'] = $response_linkedin['linkedin'];				
			  	$this->redirect(LINKEDIN::_URL_AUTH . $response_linkedin['linkedin']['oauth_token']);

			}else{
				// Handle Errors
			}
		}elseif(isset($_GET['oauth_verifier'])){

			/* LinkedIn has sent a response, user has granted permission, take the temp access 
			   token, the user's secret and the verifier to request the user's real secret key */
			$response_linkedin = $linkedin_api->retrieveTokenAccess($_SESSION['oauth']['linkedin']['request']['oauth_token'], $_SESSION['oauth']['linkedin']['request']['oauth_token_secret'], $_GET['oauth_verifier']);
			
			if($response_linkedin['success'] === TRUE){

				$linkedin_api->setTokenAccess($response_linkedin['linkedin']);
          		$linkedin_api->setResponseFormat(LINKEDIN::_RESPONSE_JSON);

          		/* Get user profile information using the retrived access token */
				$user_result = $linkedin_api->profile('~:(email-address,id,first-name,last-name,picture-url)');

				if($user_result['success'] === TRUE) {

					/* setting the user data object from the response */
				  	$data = json_decode($user_result['linkedin']);
				 	$response->status 		= TRUE;
					$response->wpwaf_network_type = 'linkedin';
					$response->first_name	= $data->firstName;
					$response->last_name	= $data->lastName;
					$response->email		= $data->emailAddress;
					$response->error_message = '';

				}else{

					/* Handling LinkedIn specific errors */
//					$response->status 		= FALSE;
//					$response->error_code 	= 'req_profile_fail';
//					$response->error_message= __('Error retrieving profile information','wpwaf');

				}
			}else{
				/* Handling LinkedIn specific errors */
//				$response->status 		= FALSE;
//				$response->error_code 	= 'access_token_fail';
//				$response->error_message= __('Access token retrieval failed','wpwaf');

			}
		}else{
			/* Handling LinkedIn specific errors */
//			if( isset( $_GET['oauth_problem'] ) && $_GET['oauth_problem'] =='user_refused'){
//				$response->status 		= FALSE;
//				$response->error_code 	= 'user_refused';
//				$response->error_message= __('User refused by application.','wpwaf');

//			}else{
//				$response->status 		= FALSE;
//				$response->error_code 	= 'req_cancel';
//				$response->error_message= __('Request cancelled by user!','wpwaf');

//			}
		}
		return $response;
	}

}
