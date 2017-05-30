<?php

class WPWAF_Model_Forum {

    public $post_type;

    public function __construct() {       
        $this->post_type = 'wpwaf_forum';
        add_action( 'init', array( $this, 'create_forums_post_type' ) );
        add_shortcode('wpwaf_forums_list', array( $this, 'display_forums_list' ) );
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

    public function display_forums_list($attr){
        global $wpdb,$wpwa_template_loader, $wpwaf_forum_list_params;

        $topics_to_forums = $wpdb->prefix.'p2p'; 
        $sql  = $wpdb->prepare( "SELECT wppost.*, count(wpp2p.p2p_to) as topics_count from $wpdb->posts as wppost inner join $topics_to_forums as wpp2p
            on wppost.ID = wpp2p.p2p_to where wppost.post_type = '%s' and wppost.post_status = 'publish'
            group by wpp2p.p2p_to", 'wpwaf_forum');

        $wpwaf_forum_list_params['forums'] =array();

        $result = $wpdb->get_results($sql);
        if($result){
            $wpwaf_forum_list_params['forums'] =$result;
        }

        ob_start();
        $wpwa_template_loader->get_template_part('forums','list');
        $display = ob_get_clean();

        return $display;
    }

    public function get_forum_list(){
        $forums = new WP_Query(array('post_type' => 'wpwaf_forum', 
                                       'post_status' => 'publish'));
        $data = array();


        if ($forums->have_posts()) : while ($forums->have_posts()) : $forums->the_post();
                array_push($data, array("ID" => get_the_ID(), "forum_title" => get_the_title()));

        endwhile;
        endif;
        return $data;
    }
}
?>
