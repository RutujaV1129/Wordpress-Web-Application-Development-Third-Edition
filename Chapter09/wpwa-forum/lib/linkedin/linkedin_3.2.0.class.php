<?php

/**
 * This file defines the 'LinkedIn' class. This class is designed to be a 
 * simple, stand-alone implementation of the LinkedIn API functions.
 * 
 * COPYRIGHT:
 *   
 * Copyright (C) 2011, fiftyMission Inc.
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a 
 * copy of this software and associated documentation files (the "Software"), 
 * to deal in the Software without restriction, including without limitation 
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.  
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS 
 * IN THE SOFTWARE.  
 *
 *    
 * REQUIREMENTS:
 * 
 * 1. You must have cURL installed on the server and available to PHP.
 * 2. You must be running PHP 5+.  
 *  
 * QUICK START:
 * 
 * There are two files needed to enable LinkedIn API functionality from PHP; the
 * stand-alone OAuth library, and this LinkedIn class. The latest version of 
 * the stand-alone OAuth library can be found on Google Code:
 * 
 * http://code.google.com/p/oauth/
 *   
 * Install these two files on your server in a location that is accessible to 
 * the scripts you wish to use them in. Make sure to change the file 
 * permissions such that your web server can read the files.
 * 
 * Next, make sure the path to the OAuth library is correct (you can change this 
 * as needed, depending on your file organization scheme, etc).
 * 
 * Finally, test the class by attempting to connect to LinkedIn using the 
 * associated demo.php page, also located at the Google Code location
 * referenced above.                   
 *   
 * RESOURCES:
 *    
 * REST API Documentation: http://developer.linkedin.com/rest
 *    
 * @version 3.2.0 - November 8, 2011
 * @author Paul Mennega <paul@fiftymission.net>
 * @copyright Copyright 2011, fiftyMission Inc. 
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License 
 */
 
/**
 * Source: http://code.google.com/p/oauth/
 * 
 * Rename and move as needed, changing the require_once() call to the correct 
 * name and path.
 */

/**
 * 'LinkedInException' class declaration.
 *  
 * This class extends the base 'Exception' class.
 * 
 * @access public
 * @package classpackage
 */
class LinkedInException extends Exception {}

/**
 * 'LinkedIn' class declaration.
 *  
 * This class provides generalized LinkedIn oauth functionality.
 * 
 * @access public
 * @package classpackage
 */
class LinkedIn {
	// api/oauth settings
	const _API_OAUTH_REALM             = 'http://api.linkedin.com';
	const _API_OAUTH_VERSION           = '1.0';
	
	// the default response format from LinkedIn
	const _DEFAULT_RESPONSE_FORMAT     = 'xml';
	
	// helper constants used to standardize LinkedIn <-> API communication.  See demo page for usage.
	const _GET_RESPONSE                = 'lResponse';
	const _GET_TYPE                    = 'lType';
	
	// Invitation API constants.
	const _INV_SUBJECT                 = 'Invitation to connect';
	const _INV_BODY_LENGTH             = 200;
	
	// API methods
	const _METHOD_TOKENS               = 'POST';
	
	// Network API constants.
	const _NETWORK_LENGTH              = 1000;
	const _NETWORK_HTML                = '<a>';
	
	// response format type constants, see http://developer.linkedin.com/docs/DOC-1203
	const _RESPONSE_JSON               = 'JSON';
	const _RESPONSE_JSONP              = 'JSONP';
	const _RESPONSE_XML                = 'XML';
	
	// Share API constants
	const _SHARE_COMMENT_LENGTH        = 700;
	const _SHARE_CONTENT_TITLE_LENGTH  = 200;
	const _SHARE_CONTENT_DESC_LENGTH   = 400;
  
	// LinkedIn API end-points
	const _URL_ACCESS                  = 'https://api.linkedin.com/uas/oauth/accessToken';
	const _URL_API                     = 'https://api.linkedin.com';
	const _URL_AUTH                    = 'https://www.linkedin.com/uas/oauth/authenticate?oauth_token=';
	const _URL_REQUEST                 = 'https://api.linkedin.com/uas/oauth/requestToken';
	const _URL_REVOKE                  = 'https://api.linkedin.com/uas/oauth/invalidateToken';
	
	// Library version
	const _VERSION                     = '3.2.0';
  
	// oauth properties
	protected $callback;
	protected $token                   = NULL;
	
	// application properties
	protected $application_key, 	$application_secret;
	
	// the format of the data to return
	protected $response_format         = self::_DEFAULT_RESPONSE_FORMAT;
	
	// last request fields
	public $last_request_headers,  $last_request_url;
	

	/**
	 * Create a LinkedIn object, used for OAuth-based authentication and 
	 * communication with the LinkedIn API.	 
	 * 
	 * @param arr $config
	 *    The 'start-up' object properties:
	 *           - appKey       => The application's API key
	 *           - appSecret    => The application's secret key
	 *           - callbackUrl  => [OPTIONAL] the callback URL
	 *                 	 
	 * @return obj
	 *    A new LinkedIn object.	 
	 */
	public function __construct($config) {
		if(!is_array($config)) {
			// bad data passed
			throw new LinkedInException('LinkedIn->__construct(): bad data passed, $config must be of type array.');
		}
		$this->setApplicationKey($config['appKey']);
		$this->setApplicationSecret($config['appSecret']);
		$this->setCallbackUrl($config['callbackUrl']);
	}
	
	/**
	* The class destructor.
	* 
	* Explicitly clears LinkedIn object from memory upon destruction.
	*/
	public function __destruct() {
		unset($this);
	}
	
	/**
	 * Set the application_key property.
	 * 
	 * @param str $key 
	 *    The application key.       	 
	 */
	public function setApplicationKey($key) {
	  	$this->application_key = $key;
	}
	/**
	 * Set the application_secret property.
	 * 
	 * @param str $secret 
	 *    The application secret.       	 
	 */
	public function setApplicationSecret($secret) {
	  	$this->application_secret = $secret;
	}
	
	/**
	 * Set the callback property.
	 * 
	 * @param str $url 
	 *    The callback url.       	 
	 */
	public function setCallbackUrl($url) {
	  	$this->callback = $url;
	}
	
	/**
	 * Set the response_format property.
	 * 
	 * @param str $format 
	 *    [OPTIONAL] The response format to specify to LinkedIn.       	 
	 */
	public function setResponseFormat($format = self::_DEFAULT_RESPONSE_FORMAT) {
	  	$this->response_format = $format;
	}
	
	/**
	 * Set the token property.
	 * 
	 * @return arr $token 
	 *    The LinkedIn OAuth token.
	 */
	public function setToken($token) {
		// check passed data
		if(!is_null($token) && !is_array($token)) {
			// bad data passed
			throw new LinkedInException('LinkedIn->setToken(): bad data passed, $token_access should be in array format.');
		}
		
		// set token
		$this->token = $token;
	}
	
	/**
	 * [DEPRECATED] Set the token_access property.
	 * 
	 * @return arr $token_access 
	 *    [OPTIONAL] The LinkedIn OAuth access token.
	 */
	public function setTokenAccess($token_access) {
    	$this->setToken($token_access);
	}
	
	/**
	 * Get the application_key property.
	 * 
	 * @return str 
	 *    The application key.       	 
	 */
	public function getApplicationKey() {
		return $this->application_key;
	}
	
	/**
	 * Get the application_secret property.
	 * 
	 * @return str 
	 *    The application secret.       	 
	 */
	public function getApplicationSecret() {
	  	return $this->application_secret;
	}
	
	/**
	 * Get the callback property.
	 * 
	 * @return str 
	 *    The callback url.       	 
	 */
	public function getCallbackUrl() {
	  	return $this->callback;
	}
	
  	/**
	 * Get the response_format property.
	 * 
	 * @return str 
	 *    The response format.       	 
	 */
	public function getResponseFormat() {
	  	return $this->response_format;
	}
	
	/**
	 * Get the token_access property.
	 * 
	 * @return arr 
	 *    The access token.       	 
	 */
	public function getToken() {
	  return $this->token;
	}
	
	/**
	 * [DEPRECATED] Get the token_access property.
	 * 
	 * @return arr 
	 *    The access token.       	 
	 */
	public function getTokenAccess() {
	  	return $this->getToken();
	}
	
	/**
	 * LinkedIn ID validation.
	 *	 
	 * Checks the passed string $id to see if it has a valid LinkedIn ID format, 
	 * which is, as of October 15th, 2010:
	 * 
	 *   10 alpha-numeric mixed-case characters, plus underscores and dashes.          	 
	 * 
	 * @param str $id 
	 *    A possible LinkedIn ID.         	 
	 * 
	 * @return bool 
	 *    TRUE/FALSE depending on valid ID format determination.                  
	 */
	public static function isId($id) {
		// check passed data
		if(!is_string($id)) {
			// bad data passed
			throw new LinkedInException('LinkedIn->isId(): bad data passed, $id must be of type string.');
		}
		
		$pattern = '/^[a-z0-9_\-]{10}$/i';
		if($match = preg_match($pattern, $id)) {
			// we have a match
			$return_data = TRUE;
		} else {
			// no match
			$return_data = FALSE;
		}
		return $return_data;
	}
	
	/**
	 * Throttling check.
	 * 
	 * Checks the passed LinkedIn response to see if we have hit a throttling 
	 * limit:
	 * 
	 * http://developer.linkedin.com/docs/DOC-1112
	 * 
	 * @param arr $response 
	 *    The LinkedIn response.
	 *                     	 
	 * @return bool
	 *    TRUE/FALSE depending on content of response.                  
	 */
	public static function isThrottled($response) {
		$return_data = FALSE;
		
		// check the variable
		if(!empty($response) && is_string($response)) {
			// we have an array and have a properly formatted LinkedIn response
			
			// store the response in a temp variable
			$temp_response = self::xmlToArray($response);
			if($temp_response !== FALSE) {
				// check to see if we have an error
				if(array_key_exists('error', $temp_response) && ($temp_response['error']['children']['status']['content'] == 403) && preg_match('/throttle/i', $temp_response['error']['children']['message']['content'])) {
					// we have an error, it is 403 and we have hit a throttle limit
					$return_data = TRUE;
				}
			}
		}
		return $return_data;
	}
	
	
	/**
	 * Returns the last request header from the previous call to the 
	 * LinkedIn API.
	 * 
	 * @returns str
	 *    The header, in string format.
	 */            	
	public function lastRequestHeader() {
	   	return $this->last_request_headers;
	}
	
	/**
	 * Returns the last request url from the previous call to the 
	 * LinkedIn API.
	 * 
	 * @returns str
	 *    The url, in string format.
	 */            	
	public function lastRequestUrl() {
	   	return $this->last_request_url;
	}
	
	
	/**
	 * General profile retrieval function.
	 * 
	 * Takes a string of parameters as input and requests profile data from the 
	 * Linkedin Profile API. See the official documentation for $options
	 * 'field selector' formatting:
	 * 
	 *   http://developer.linkedin.com/docs/DOC-1014
	 *   http://developer.linkedin.com/docs/DOC-1002    
	 * 
	 * @param str $options 
	 *    [OPTIONAL] Data retrieval options.
	 *            	 
	 * @return arr 
	 *    Array containing retrieval success, LinkedIn response.
	 */
	public function profile($options = '~') {
		// check passed data
		if(!is_string($options)) {
			// bad data passed
			throw new LinkedInException('LinkedIn->profile(): bad data passed, $options must be of type string.');
		}
		
		// construct and send the request
		$query    = self::_URL_API . '/v1/people/' . trim($options);
		$response = $this->fetch('GET', $query);
		
		/**
		* Check for successful request (a 200 response from LinkedIn server) 
		* per the documentation linked in method comments above.
		*/
		return $this->checkResponse(200, $response);
	}
	
	/**
	 * Manual API call method, allowing for support for un-implemented API
	 * functionality to be supported.
	 * 
	 * @param str $method 
	 *    The data communication method.	 
	 * @param str $url 
	 *    The Linkedin API endpoint to connect with - should NOT include the 
	 *    leading https://api.linkedin.com/v1.
	 * @param str $body
	 *    [OPTIONAL] The URL-encoded body data to send to LinkedIn with the request.
	 * 
	 * @return arr
	 * 		Array containing retrieval information, LinkedIn response. Note that you
	 * 		must manually check the return code and compare this to the expected 
	 * 		API response to determine  if the raw call was successful.
	 */
	public function raw($method, $url, $body = NULL) {
	  if(!is_string($method)) {
	    // bad data passed
		  throw new LinkedInException('LinkedIn->raw(): bad data passed, $method must be of string value.');
	  }
	  if(!is_string($url)) {
	    // bad data passed
		  throw new LinkedInException('LinkedIn->raw(): bad data passed, $url must be of string value.');
	  }
	  if(!is_null($body) && !is_string($url)) {
	    // bad data passed
		  throw new LinkedInException('LinkedIn->raw(): bad data passed, $body must be of string value.');
	  }
    
    // construct and send the request
	  $query = self::_URL_API . '/v1' . trim($url);
	  return $this->fetch($method, $query, $body);
	}
	
   /**
	 * Access token retrieval.
	 *
	 * Request the user's access token from the Linkedin API.
	 * 
	 * @param str $token
	 *    The token returned from the user authorization stage.
	 * @param str $secret
	 *    The secret returned from the request token stage.
	 * @param str $verifier
	 *    The verification value from LinkedIn.
	 *    	 
	 * @return arr 
	 *    The Linkedin OAuth/http response, in array format.      	 
	 */
	public function retrieveTokenAccess($token, $secret, $verifier) {
		// check passed data
		if(!is_string($token) || !is_string($secret) || !is_string($verifier)) {
			// nothing passed, raise an exception
			throw new LinkedInException('LinkedIn->retrieveTokenAccess(): bad data passed, string type is required for $token, $secret and $verifier.');
		}
		
		// start retrieval process
		$this->setToken(array('oauth_token' => $token, 'oauth_token_secret' => $secret));
		$parameters = array(
			'oauth_verifier' => $verifier
		);
		$response = $this->fetch(self::_METHOD_TOKENS, self::_URL_ACCESS, NULL, $parameters);
		parse_str($response['linkedin'], $response['linkedin']);
		
		/**
		* Check for successful request (a 200 response from LinkedIn server) 
		* per the documentation linked in method comments above.
		*/
		if($response['info']['http_code'] == 200) {
			// tokens retrieved
			$this->setToken($response['linkedin']);
			
			// set the response
			$return_data            = $response;
			$return_data['success'] = TRUE;
		} else {
			// error getting the request tokens
			$this->setToken(NULL);
			
			// set the response
			$return_data            = $response;
			$return_data['error']   = 'HTTP response from LinkedIn end-point was not code 200';
			$return_data['success'] = FALSE;
		}
		return $return_data;
	}
	
	/**
	 * Request token retrieval.
	 * 
	 * Get the request token from the Linkedin API.
	 * 
	 * @return arr
	 *    The Linkedin OAuth/http response, in array format.      	 
	 */
	public function retrieveTokenRequest($urlParams='') {
		$parameters = array(
			'oauth_callback' => $this->getCallbackUrl()
		);
		$url = self::_URL_REQUEST;
		if(is_array($urlParams) && count($urlParams)>0){
			$url .='?' ;
			$parts = array();
			foreach($urlParams as $name=>$value){
				$parts[] = "$name=$value";
			}
			$url .= implode('&', $parts);
		}
		$response = $this->fetch(self::_METHOD_TOKENS, $url, NULL, $parameters);
		parse_str($response['linkedin'], $response['linkedin']);
		
		/**
		* Check for successful request (a 200 response from LinkedIn server) 
		* per the documentation linked in method comments above.
		*/
		if(($response['info']['http_code'] == 200) && (array_key_exists('oauth_callback_confirmed', $response['linkedin'])) && ($response['linkedin']['oauth_callback_confirmed'] == 'true')) {
			// tokens retrieved
			$this->setToken($response['linkedin']);
			
			// set the response
			$return_data            = $response;
			$return_data['success'] = TRUE;        
		} else {
			// error getting the request tokens
			$this->setToken(NULL);
			
			// set the response
			$return_data = $response;
			if((array_key_exists('oauth_callback_confirmed', $response['linkedin'])) && ($response['linkedin']['oauth_callback_confirmed'] == 'true')) {
				$return_data['error'] = 'HTTP response from LinkedIn end-point was not code 200';
			} else {
				$return_data['error'] = 'OAuth callback URL was not confirmed by the LinkedIn end-point';
			}
			$return_data['success'] = FALSE;
		}
		return $return_data;
	}
	
	
	/**
	 * Converts passed XML data to an array.
	 * 
	 * @param str $xml 
	 *    The XML to convert to an array.
	 *            	 
	 * @return arr 
	 *    Array containing the XML data.     
	 * @return bool 
	 *    FALSE if passed data cannot be parsed to an array.     	 
	 */
	public static function xmlToArray($xml) {
		// check passed data
		if(!is_string($xml)) {
			// bad data possed
			throw new LinkedInException('LinkedIn->xmlToArray(): bad data passed, $xml must be a non-zero length string.');
		}
		
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		if(xml_parse_into_struct($parser, $xml, $tags)) {
			$elements = array();
			$stack    = array();
			foreach($tags as $tag) {
				$index = count($elements);
				if($tag['type'] == 'complete' || $tag['type'] == 'open') {
					$elements[$tag['tag']]               = array();
					$elements[$tag['tag']]['attributes'] = (array_key_exists('attributes', $tag)) ? $tag['attributes'] : NULL;
					$elements[$tag['tag']]['content']    = (array_key_exists('value', $tag)) ? $tag['value'] : NULL;
					if($tag['type'] == 'open') {
						$elements[$tag['tag']]['children'] = array();
						$stack[count($stack)] = &$elements;
						$elements = &$elements[$tag['tag']]['children'];
					}
				}
				if($tag['type'] == 'close') {
					$elements = &$stack[count($stack) - 1];
					unset($stack[count($stack) - 1]);
				}
			}
			$return_data = $elements;
		} else {
			// not valid xml data
			$return_data = FALSE;
		}
		xml_parser_free($parser);
		return $return_data;
  	}
	function checkResponseIsInt($value, $key) {
		if(!is_int($value)) {
			throw new LinkedInException('LinkedIn->checkResponse(): $http_code_required must be an integer or an array of integer values');
		}
	}
		
     /**
	 * Used to check whether a response LinkedIn object has the required http_code or not and 
	 * returns an appropriate LinkedIn object.
	 * 
	 * @param var $http_code_required
	 * 		The required http response from LinkedIn, passed in either as an integer, 
	 * 		or an array of integers representing the expected values.	 
	 * @param arr $response 
	 *    An array containing a LinkedIn response.
	 * 
	 * @return boolean
	 * 	  TRUE or FALSE depending on if the passed LinkedIn response matches the expected response.
	 */
	private function checkResponse($http_code_required, $response) {
		// check passed data
		if(is_array($http_code_required)) {
			  array_walk($http_code_required, array($this, 'checkResponseIsInt'));
		} else {
		  if(!is_int($http_code_required)) {
  			throw new LinkedInException('LinkedIn->checkResponse(): $http_code_required must be an integer or an array of integer values');
  		} else {
  		  $http_code_required = array($http_code_required);
  		}
		}
		if(!is_array($response)) {
			throw new LinkedInException('LinkedIn->checkResponse(): $response must be an array');
		}		
		
		// check for a match
		if(in_array($response['info']['http_code'], $http_code_required)) {
		  // response found
		  $response['success'] = TRUE;
		} else {
			// response not found
			$response['success'] = FALSE;
			$response['error']   = 'HTTP response from LinkedIn end-point was not code ' . implode(', ', $http_code_required);
		}
		return $response;
	}	
	/**
	 * General data send/request method.
	 * 
	 * @param str $method 
	 *    The data communication method.	 
	 * @param str $url 
	 *    The Linkedin API endpoint to connect with.
	 * @param str $data
	 *    [OPTIONAL] The data to send to LinkedIn.
	 * @param arr $parameters 
	 *    [OPTIONAL] Addition OAuth parameters to send to LinkedIn.
	 *        
	 * @return arr 
	 *    Array containing:
	 * 
	 *           array(
	 *             'info'      =>	Connection information,
	 *             'linkedin'  => LinkedIn response,  
	 *             'oauth'     => The OAuth request string that was sent to LinkedIn	 
	 *           )	 
	 */
	protected function fetch($method, $url, $data = NULL, $parameters = array()) {
		// check for cURL
		if(!extension_loaded('curl')) {
			// cURL not present
			throw new LinkedInException('LinkedIn->fetch(): PHP cURL extension does not appear to be loaded/present.');
		}
	  
		try {
			// generate OAuth values
			$oauth_consumer  = new OAuthConsumer($this->getApplicationKey(), $this->getApplicationSecret(), $this->getCallbackUrl());
			$oauth_token     = $this->getToken();
			$oauth_token     = (!is_null($oauth_token)) ? new OAuthToken($oauth_token['oauth_token'], $oauth_token['oauth_token_secret']) : NULL;
			$defaults        = array(
				'oauth_version' => self::_API_OAUTH_VERSION
			);
			$parameters    = array_merge($defaults, $parameters);
			
			// generate OAuth request
			$oauth_req = OAuthRequest::from_consumer_and_token($oauth_consumer, $oauth_token, $method, $url, $parameters);
			$oauth_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $oauth_consumer, $oauth_token);
			
			// start cURL, checking for a successful initiation
			if(!$handle = curl_init()) {
				// cURL failed to start
				throw new LinkedInException('LinkedIn->fetch(): cURL did not initialize properly.');
			}
			
			// set cURL options, based on parameters passed
			curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($handle, CURLOPT_URL, $url);
			curl_setopt($handle, CURLOPT_VERBOSE, FALSE);
			
			// configure the header we are sending to LinkedIn - http://developer.linkedin.com/docs/DOC-1203
			$header = array($oauth_req->to_header(self::_API_OAUTH_REALM));
			if(is_null($data)) {
				// not sending data, identify the content type
				$header[] = 'Content-Type: text/plain; charset=UTF-8';
				switch($this->getResponseFormat()) {
					case self::_RESPONSE_JSON:
						$header[] = 'x-li-format: json';
					break;
					case self::_RESPONSE_JSONP:
						$header[] = 'x-li-format: jsonp';
					break;
				}
			} else {
				$header[] = 'Content-Type: text/xml; charset=UTF-8';
				curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
			}
			curl_setopt($handle, CURLOPT_HTTPHEADER, $header);
			
			// set the last url, headers
			$this->last_request_url = $url;
			$this->last_request_headers = $header;
			
			// gather the response
			$return_data['linkedin']        = curl_exec($handle);
			$return_data['info']            = curl_getinfo($handle);
			$return_data['oauth']['header'] = $oauth_req->to_header(self::_API_OAUTH_REALM);
			$return_data['oauth']['string'] = $oauth_req->base_string;
			
			// check for throttling
			if(self::isThrottled($return_data['linkedin'])) {
				throw new LinkedInException('LinkedIn->fetch(): throttling limit for this user/application has been reached for LinkedIn resource - ' . $url);
			}
			
			//TODO - add check for NO response (http_code = 0) from cURL
			
			// close cURL connection
			curl_close($handle);
			
			// no exceptions thrown, return the data
			return $return_data;
		} catch(OAuthException $e) {
			// oauth exception raised
			throw new LinkedInException('OAuth exception caught: ' . $e->getMessage());
		}
	}
	
	/**
	 * User authorization revocation.
	 * 
	 * Revoke the current user's access token, clear the access token's from 
	 * current LinkedIn object. The current documentation for this feature is 
	 * found in a blog entry from April 29th, 2010:
	 * 
	 *   http://developer.linkedin.com/community/apis/blog/2010/04/29/oauth--now-for-authentication	 
	 * 
	 * @return arr 
	 *    Array containing retrieval success, LinkedIn response.   	 
	 */
	public function revoke() {
		// construct and send the request
		$response = $this->fetch('GET', self::_URL_REVOKE);
		
		/**
		* Check for successful request (a 200 response from LinkedIn server) 
		* per the documentation linked in method comments above.
		*/                	  
		return $this->checkResponse(200, $response);
	}
}

?>