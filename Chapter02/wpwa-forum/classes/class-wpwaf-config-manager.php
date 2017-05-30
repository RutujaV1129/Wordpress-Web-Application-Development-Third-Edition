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
		$role = get_role( 'wpwaf_premium_member' );
        $role->add_cap( 'follow_forum_activities' );

        $role = get_role( 'wpwaf_free_member' );
        $role->add_cap( 'follow_forum_activities' );
	}

	public function flush_application_rewrite_rules(){
		$this->manage_user_routes();
        flush_rewrite_rules();
	}

	public function manage_user_routes() {
        add_rewrite_rule( '^user/([^/]+)/?', 'index.php?control_action=$matches[1]', 'top' );
    }

    public function manage_user_routes_query_vars( $query_vars ) {
        $query_vars[] = 'control_action';
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

            default:
                break;
        }
    }
}