<?php

/*
  Plugin Name: WPWAF REST API
  Plugin URI:
  Description: 
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com/
  License: GPLv2 or later
 */
 
 define( 'WPWAF_REST_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// add_filter( 'rest_authentication_errors', 'wpwaf_disable_rest_api' );
function wpwaf_disable_rest_api( $access ) {
	if( ! is_user_logged_in() || !current_user_can('manage_options')) {
        return new WP_Error( 'rest_cannot_access', __( 'Only authenticated users can access the REST API.', 'wpwaf' ), array( 'status' => rest_authorization_required_code() ) );
    }
    return $access;	
}


function wpwaf_prepare_read_topics() {
	global $wpwaf_user_status;
	$topics_query = new WP_Query(array('post_type' => 'wpwaf_topic','post_status' =>'publish',
                  'order' => 'desc', 'orderby' => 'date', 'meta_key'     => '_wpwaf_topic_sticky_status',
				  'meta_value'   => 'normal',	'meta_compare' => '=' ));

	$data = array();
	if($topics_query->have_posts()){
	    while($topics_query->have_posts()) : $topics_query->the_post();
	    	$sticky_status = get_post_meta(get_the_ID(),'_wpwaf_topic_sticky_status', true);
	        array_push($data, array("ID" => get_the_ID(), "title" => get_the_title(), "sticky_status"=> $sticky_status ));
	    endwhile;
	}


    // rest_ensure_response() wraps the data we want to return into a WP_REST_Response, and ensures it will be properly returned.
    return rest_ensure_response(($data));
}


function wpwaf_prepare_read_forum_topics($data){
	global $wpdb;

	$post_data = json_decode($data->get_body());
	$data = $data->get_params();
	$api_token = isset($post_data->api_token) ? $post_data->api_token : '';
	$sql_token  = $wpdb->prepare( "SELECT * from $wpdb->usermeta where meta_key = 'api_token' and 
		meta_value = '%s'", $api_token);
    $result_token = $wpdb->get_results($sql_token);
    if(!$result_token){
    	return new WP_Error( 'rest_cannot_access', __( 'Only authenticated users can access the REST API.', 'wpwaf' ), array( 'status' => rest_authorization_required_code() ) );
    }


	$forum_id = isset($data['id']) ? $data['id'] : 0;

    $topics_to_forums = $wpdb->prefix.'p2p'; 
    $sql  = $wpdb->prepare( "SELECT wppost.* from $wpdb->posts as wppost inner join $topics_to_forums as wpp2p
        on wppost.ID = wpp2p.p2p_from where wppost.post_type = '%s' and wppost.post_status = 'publish'
        and p2p_type= 'topics_to_forums' and p2p_to = %d ", 'wpwaf_topic', $forum_id);

    $result = $wpdb->get_results($sql);

    $topics = array();
    if($result){
        foreach ($result as $key => $value) {
        	$topics[] = array('ID'=> $value->ID, 'post_title' => $value->post_title, 
        		'post_content' => $value->post_content);
        }
    }

    return rest_ensure_response(($topics));
} 

function wpwaf_topics_route() {
	global $wpwaf_user_status;

	$wpwaf_user_status = true;
	if(!is_user_logged_in()){
		$wpwaf_user_status = false;
	}

    register_rest_route( 'wpwaf/v1', '/read_topics', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'wpwaf_prepare_read_topics',
        'permission_callback' => function () {
	      return current_user_can( 'edit_others_posts' );
	    }
    ) );

    register_rest_route( 'wpwaf/v1', '/read_forum_topics/(?P<id>\d+)', array(
	    'methods' => 'POST',
	    'callback' => 'wpwaf_prepare_read_forum_topics',
	    'args' => array(
	      'id' => array(
	        'validate_callback' => function($param, $request, $key) {
	          return ($param != 5 );
	        }
	      ),
	    ),
	 ) );
}

add_action( 'rest_api_init', 'wpwaf_topics_route' );

add_action('wp_enqueue_scripts','add_rest_api_scripts');
function add_rest_api_scripts(){

  wp_register_script('WPWAF_REST', WPWAF_REST_PLUGIN_URL."rest.js", array( 'wp-api' ) );
  wp_enqueue_script('WPWAF_REST');

}

