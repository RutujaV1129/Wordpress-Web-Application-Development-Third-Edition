<?php

/*
  Plugin Name: WPWAF XML-RPC API
  Plugin URI: http://www.wpexpertdeveloper.com/
  Description: Creating API functions for forum management applications to understand the process of WordPress XML-RPC API.
  Author: Rakhitha Nimesh
  Version: 1.0
  Author URI: http://www.wpexpertdeveloper.com/
 */

define( 'WPWAF_XML_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );

class WPWAF_XML_RPC_API {

    public function __construct() {
        

        add_filter( 'xmlrpc_methods', array( $this, 'xml_rpc_api' ) );

        add_action( 'admin_menu', array( $this, 'api_settings' ) );
        
        add_filter('wpwa_template_loader_locations',array( $this, 'api_template_locations' ));
    }

    public function xml_rpc_api($methods) {
        $methods['wpwaf.subscribeToTopics']  = array( $this, 'topic_subscriptions' );
        $methods['wpwaf.getForumTopics']     = array( $this, 'forum_topics_list' );
        $methods['wpwaf.apiDoc']             = array( $this, 'api_doc' );
        return $methods;
    }

    public function api_settings() {

        add_menu_page(__('API Settings','wpwaf'), __('API Settings','wpwaf'), 'follow_forum_activities', 'wpwaf-api', array( $this, 'user_api_settings') );
    }

    public function user_api_settings() {
        global $wpwa_template_loader,$api_data;
        $user_id = get_current_user_id();

        if ( isset( $_POST['api_settings'] ) ) {
            $api_token = $this->generate_random_hash();
            update_user_meta( $user_id, "api_token", $api_token );
        } else {
            $api_token = (string) get_user_meta($user_id, "api_token", TRUE);
            if ( empty($api_token) ) {
                $api_token = $this->generate_random_hash();
                update_user_meta( $user_id, "api_token", $api_token );
            }
        }

        $api_data['api_token'] = $api_token;

        ob_start();
        $wpwa_template_loader->get_template_part('api-settings');
        $html = ob_get_clean();

        echo $html;
    }

    public function topic_subscriptions( $args ) {
        global $wpdb;

        $username = isset( $args['username'] ) ? $args['username'] : '';
        $password = isset( $args['password'] ) ? $args['password'] : '';

        $user = wp_authenticate( $username, $password );

        if (!$user || is_wp_error($user)) {
            return $user;
        }

        $follower_id = $user->ID;
        $api_token = (string) get_user_meta($follower_id, "api_token", TRUE);

        $token = isset( $args['token'] ) ? $args['token'] : '';
        if ( $args['token'] == $api_token) {


            $topic_id = isset( $args['topic_id'] ) ? $args['topic_id'] : 0 ;
            $topic_subscriptions_table = $wpdb->prefix.'topic_subscriptions'; 

            $sql  = $wpdb->prepare( "SELECT * FROM $topic_subscriptions_table WHERE 
                topic_id = %d AND user_id = %d ", $topic_id , $follower_id );
       
            $result = $wpdb->get_results($sql);
            if(!$result){
                $wpdb->insert(
                        $topic_subscriptions_table,
                        array(
                            'topic_id' => $topic_id,
                            'user_id' => $follower_id
                        ),
                        array(
                            '%d',
                            '%d'
                        )
                );

                return array("success" => __("Subsciption Completed.","wpwaf"));
            }else{
                return array("error" => __("Already subscribed to topic.","wpwaf"));
            }

        } else {
            return array("error" => __("Invalid Token.","wpwaf"));
        }

        return $args;
    }

    public function forum_topics_list( $args ) {
        global $wpdb;
        $forum_id = isset($args['forum_id']) ? $args['forum_id'] : 0;

        $topics_to_forums = $wpdb->prefix.'p2p'; 
        $sql  = $wpdb->prepare( "SELECT wppost.* from $wpdb->posts as wppost inner join $topics_to_forums as wpp2p
            on wppost.ID = wpp2p.p2p_from where wppost.post_type = '%s' and wppost.post_status = 'publish'
            and p2p_type= 'topics_to_forums' and p2p_to = %d ", 'wpwaf_topic', $forum_id);

        $result = $wpdb->get_results($sql);

        
        return $result;
    }

    public function api_doc() {

        $api_doc = array();

        $api_doc["wpwaf.subscribeToTopics"] = array("authentication" => "required",
            "api_token" => "required",
            "parameters" => array("Topic ID", "API Token"),
            "result" => __("Subscribing to Topics","wpwaf")
        );

        $api_doc["wpwaf.getForumTopics"] = array("authentication" => "optional",
            "api_token" => "optional",
            "parameters" => array("Topic ID"),
            "result" => __("Retrive List of Topics of given Forum","wpwaf")
        );

        return $api_doc;
    }

    public function generate_random_hash($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, strlen($characters) - 1)];
        }

        $random_hash = wp_hash($random_string);
        return $random_hash;
    }
    
    public function api_template_locations($locations){
        $location = trailingslashit( plugin_dir_path(__FILE__) ) . 'templates/';
        array_push($locations,$location);
        return $locations;
    }

}


add_action( 'plugins_loaded', 'wpwaf_xml_plugin_init' );
function wpwaf_xml_plugin_init(){
  if(!class_exists('WPWA_Template_Loader')){
    // add_action( 'admin_notices', 'wpwaf_plugin_admin_notice' );
  }else{
    new WPWAF_XML_RPC_API();
  }
}

?>
