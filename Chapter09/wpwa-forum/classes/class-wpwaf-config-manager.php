<?php

class WPWAF_Config_Manager{

	public function __construct(){

		add_action( 'init', array( $this, 'manage_user_routes' ) );

		add_filter( 'query_vars', array( $this, 'manage_user_routes_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'front_controller' ) );
	}

	public function activation_handler(){
		$this->add_application_user_roles();
		$this->remove_application_user_roles();
		$this->add_application_user_capabilities();
		$this->flush_application_rewrite_rules();
        $this->create_custom_tables();
	}

	public function add_application_user_roles(){
		add_role( 'wpwaf_premium_member', __('Premium Member','wpwaf'), array( 'read' => true ) );
        add_role( 'wpwaf_free_member', __('Free Member','wpwaf'), array( 'read' => true ) );
        add_role( 'wpwaf_moderator', __('Moderator','wpwaf'), array( 'read' => true ) );
	}

	public function remove_application_user_roles(){
		// remove_role( 'author' );
        // remove_role( 'editor' );
        // remove_role( 'contributor' );
        // remove_role( 'subscriber' );
	}

	public function add_application_user_capabilities(){
        $custom_member_capabilities = array(
          'edit_wpwaf_topics','publish_wpwaf_topics','delete_wpwaf_topics', 'edit_published_wpwaf_topics', 
          'create_wpwaf_topics' , 'assign_wpwaf_topic_tag');

		$premium_member_role = get_role( 'wpwaf_premium_member' );
        $premium_member_role->add_cap( 'follow_forum_activities' );        

        $free_member_role = get_role( 'wpwaf_free_member' );
        $free_member_role->add_cap( 'follow_forum_activities' );

        foreach ($custom_member_capabilities as $capability) {
            $premium_member_role->add_cap($capability);
            $free_member_role->add_cap($capability);
        }

        $custom_admin_capabilities = array(
          'edit_wpwaf_topics','publish_wpwaf_topics','delete_wpwaf_topics', 'edit_published_wpwaf_topics', 
          'create_wpwaf_topics','delete_published_wpwaf_topics','edit_others_wpwaf_topics'
          ,'delete_others_wpwaf_topics' , 'assign_wpwaf_topic_tag', 'delete_wpwaf_topic_tag', 
          'edit_wpwaf_topic_tag', 'manage_wpwaf_topic_tag' );

        $moderator_role = get_role( 'wpwaf_moderator' ); 
        $admin_role = get_role( 'administrator' );    

        foreach ($custom_admin_capabilities as $capability) {
            $moderator_role->add_cap($capability);
            $admin_role->add_cap($capability);
        }
	}

	public function flush_application_rewrite_rules(){
		$this->manage_user_routes();
        flush_rewrite_rules();
	}

	public function manage_user_routes() {
        add_rewrite_rule('^user/([^/]+)/([^/]+)/?', 'index.php?control_action=$matches[1]&record_id=$matches[2]', 'top');
        add_rewrite_rule( '^user/([^/]+)/?', 'index.php?control_action=$matches[1]', 'top' );
    }

    public function manage_user_routes_query_vars( $query_vars ) {
        $query_vars[] = 'control_action';
        $query_vars[] = 'record_id';
        return $query_vars;
    }

    public function front_controller() {
        global $wp_query,$wpwaf;
        $control_action = isset ( $wp_query->query_vars['control_action'] ) ? $wp_query->query_vars['control_action'] : '';

        switch ( $control_action ) {
            case 'register':
                do_action( 'wpwaf_before_registeration_form' );
                $wpwaf->registration->display_registration_form();
                break;

            case 'login':                
                do_action( 'wpwaf_before_login_form' );
                $wpwaf->login->display_login_form();
                break;

            case 'activate':
                do_action( 'wpwaf_before_activate_user' );
                $wpwaf->registration->activate_user();
                do_action( 'wpwaf_after_activate_user' );
                break;

            case 'profile':

                do_action( 'wpwaf_before_create_profile' );
                $record_id = isset($wp_query->query_vars['record_id']) ? $wp_query->query_vars['record_id'] : '' ;
                $forum_member_id = $record_id;
                echo $forum_member_id;
                $wpwaf->user->create_forum_member_profile($forum_member_id);
                do_action( 'wpwaf_after_create_profile' );
                break;

            default:
                break;
        }
    }

    public function create_custom_tables(){
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $topic_subscriptions_table = $wpdb->prefix.'topic_subscriptions';       
        if($wpdb->get_var("show tables like '$topic_subscriptions_table'") != $topic_subscriptions_table) {
            $sql = "CREATE TABLE $topic_subscriptions_table (
              id mediumint(9) NOT NULL AUTO_INCREMENT,
              time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
              user_id mediumint(9) NOT NULL,
              topic_id  mediumint(9) NOT NULL,
              UNIQUE KEY id (id)
            );";
            dbDelta( $sql );
        }
    }
}