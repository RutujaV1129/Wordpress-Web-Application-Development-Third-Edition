<?php

class WPWAF_Model_Forum {

    public $post_type;

    public function __construct() {       
        $this->post_type = 'wpwaf_forum';
        add_action( 'init', array( $this, 'create_forums_post_type' ) );
    }

    public function create_forums_post_type() {
        global $wpwaf;        
        $params = array();        
        $params['post_type'] = $this->post_type;
        $params['singular_post_name'] = __('Forum','wpwaf');
        $params['plural_post_name'] = __('Forums','wpwaf');
        $params['description'] = __('Forums','wpwaf');
        $params['supported_fields'] = array('title', 'editor'); 
        
        $wpwaf->model_manager->create_post_type($params);        
    }   

    

}
?>
