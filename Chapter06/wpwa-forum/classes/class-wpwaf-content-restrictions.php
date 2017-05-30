<?php

class WPWAF_Content_Restrictions{

	public function __construct(){
		add_action( 'add_meta_boxes', array($this,'add_topic_restriction_box' ));
        add_action( 'save_post', array($this,'save_topic_restrictions' ));

		add_shortcode('wpwaf_private_content', array($this,'private_content_block'));
		add_action('template_redirect', array($this, 'validate_restrictions'), 1);
		add_action('template_redirect', array($this, 'validate_site_lockdown_restrictions'));
        
	}

	public function add_topic_restriction_box(){
        global $wpwaf;
        if( current_user_can('manage_options')  ){        
            add_meta_box(
                'wpwaf-forum-restrictions',
                __( 'Restriction Settings', 'wpwaf' ),
                array($this,'add_topic_restrictions'),
                $wpwaf->topic->post_type,
                'normal',
                'low'
            );
        }
    }

    public function add_topic_restrictions($post){
        global $wpwaf,$topic_restriction_params,$wpwa_template_loader;

        $topic_restriction_params['post'] = $post;

        ob_start();
        $wpwa_template_loader->get_template_part('topic-restriction-meta');    
        $display = ob_get_clean();
        echo $display;

    }

    public function save_topic_restrictions($post_id){

        if ( ! isset( $_POST['wpwaf_restriction_settings_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['wpwaf_restriction_settings_nonce'], 'wpwaf_restriction_settings' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_posts', $post_id ) ) {
            return;
        }

        $visibility = isset( $_POST['wpwaf_topic_visibility'] ) ? $_POST['wpwaf_topic_visibility'] : 'none';
        $redirection_url = isset( $_POST['wpwaf_topic_redirection_url'] ) ? $_POST['wpwaf_topic_redirection_url'] : '';
        $visible_roles = isset( $_POST['wpwaf_topic_roles'] ) ? $_POST['wpwaf_topic_roles'] : array();
        
        update_post_meta( $post_id, '_wpwaf_topic_visibility', $visibility );
        update_post_meta( $post_id, '_wpwaf_topic_redirection_url', $redirection_url );
        update_post_meta( $post_id, '_wpwaf_topic_roles', $visible_roles );

    }

	public function private_content_block($atts,$content){
        global $wpwaf,$wpdb;
        
        // Provide permission for admin to view any content
        if(current_user_can('manage_options') ){
        	return do_shortcode($content);
        }

        if (!is_user_logged_in())
			return __('Login to access this content','wpwaf');		
        
        // Filter conditions
        foreach ($atts as $sh_attr => $sh_value) {
        	switch ($sh_attr) {
	        	case 'allowed_roles':
	        		$this->status = $this->allowed_roles_filter($atts,$sh_value);
	        		break;             
            }

            if(!$this->status){
                break;
            }
        }
        
        if(!$this->status){
            return __('You don\'t have permission to access this content','wpwaf');    		
        }else{
        	return do_shortcode($content);
        }
    }

    public function allowed_roles_filter($atts,$sh_value){
        global $wpwaf;
        extract($atts);

		$user_roles = $this->get_user_roles_by_id(get_current_user_id());
        $roles = explode(',',$sh_value);

        if(is_array($roles) && count($roles) > 1){            
            foreach ($roles as $role) {
                if(in_array($role, $user_roles)){                        
                    return true;
                }                               
            }
        }       
     
		return false;
    }

    public function get_user_roles_by_id($user_id) {
        $user = new WP_User($user_id);
        if (!empty($user->roles) && is_array($user->roles)) {
            $this->user_roles = $user->roles;
            return $user->roles;
        } else {
            $this->user_roles = array();
            return array();
        }
    }

    public function validate_restrictions(){
        global $wpwaf,$wp_query;

        $this->current_user = wp_get_current_user();

        if(current_user_can('manage_options')){
            return;
        }

        if (! isset($wp_query->post->ID) ) {
            return;
        }

        if(is_page() || is_single()){
            $post_id = $wp_query->post->ID;

            $protection_status = $this->protection_status($post_id);

            if(!$protection_status){
                $post_redirection_url = get_post_meta( $post_id, '_wpwaf_topic_redirection_url', true );
                if(trim($post_redirection_url) == ''){
                    $post_redirection_url = get_home_url();
                }
                wp_redirect($post_redirection_url);exit;
            }

        }
       
        if(is_archive() || is_feed() || is_search() || is_home() ){            
            if(isset($wp_query->posts) && is_array($wp_query->posts)){
                foreach ($wp_query->posts as $key => $post_obj) {
                    $protection_status = $this->protection_status($post_obj->ID);
                    if(!$protection_status){
                        $wp_query->posts[$key]->post_content = __('You don\'t have permission to view the content','wpwaf');
                    }
                }
            }
        }
        return;
    }

    public function protection_status($post_id){
        global $wpwaf;

        $visibility = get_post_meta( $post_id, '_wpwaf_topic_visibility', true );
        $visible_roles = get_post_meta( $post_id, '_wpwaf_topic_roles', true );
        if(!is_array($visible_roles)){
            $visible_roles = array();
        }

        switch ($visibility) {
            case 'all':
                return TRUE;
                break;
            
            case 'guest':
                if(is_user_logged_in()){
                    return FALSE;
                }else{
                    return TRUE;
                }
                break;

            case 'member':
                if(is_user_logged_in()){
                    return TRUE;
                }else{
                    return FALSE;
                }
                break;

            // case 'role':
            //     if(is_user_logged_in()){
            //         if(count($visible_roles) == 0){
            //             return FALSE;
            //         }else{
            //             $user_roles = $this->get_user_roles_by_id($this->current_user);
            //             foreach ($visible_roles as  $visible_role ) {
            //                 if(in_array($visible_role, $user_roles)){
            //                     return TRUE;
            //                 }
            //             }
            //             return FALSE;
            //         }
            //     }else{
            //         return FALSE;
            //     }
                
            //     break;



            default:
                return apply_filters('wpwaf_verify_custom_restrictions', TRUE , $visibility); 
                break;
        }

        return TRUE;
    }

    function validate_site_lockdown_restrictions(){
        global $wpwaf,$pagenow;

        if(is_feed()){
            return;
        }

        $this->user_id = get_current_user_id();

        // Skip restrictions for admin users and return the page
        if(current_user_can('manage_options')){
            return;
        }

        $redirect_url = 'http://localhost/wp-cookbook/login2';

        // Add globally skipped URL's, pages and posts
        $skipped_urls = array( $redirect_url , wp_login_url(), wp_registration_url(), wp_lostpassword_url());
        $skipped_custom_urls = array();
        foreach ($skipped_custom_urls as $url) {
            if($url != ''){
                array_push($skipped_urls, $url);
            }
        }

        $url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
      	$url .= $_SERVER["REQUEST_URI"];
        $current_page_url = $url;

        $parsed_url = parse_url($current_page_url);
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
 
        $current_page_url = $scheme.$user.$pass.$host.$port.$path;

        if(in_array($current_page_url, $skipped_urls)){
            return;
        }else{
            if($this->user_id == 0){
                wp_redirect($redirect_url);             
                exit;
            }
            
        }        

    }

    
}