<?php

class WPWAF_Model_Forum {

    public $post_type;

    public function __construct() {       
        $this->post_type = 'wpwaf_forum';
        add_action( 'init', array( $this, 'create_forums_post_type' ) );
        add_shortcode('wpwaf_forums_list', array( $this, 'display_forums_list' ) );
        add_filter('single_template', array( $this, 'display_forum_template'));

        add_filter('wpwaf_forum_header_buttons', array( $this, 'display_forum_join'),10,2);
        add_action('wp_ajax_wpwaf_forum_join', array($this, 'forum_join'));

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



    public function display_forum_template($single) {
        global $wp_query, $post;
        if ($post->post_type == "wpwaf_forum"){
            if(file_exists(WPWAF_PLUGIN_DIR . 'templates/single-forum.php'))
                return WPWAF_PLUGIN_DIR . 'templates/single-forum.php';
        }
        return $single;
    }

    public function display_forum_join($display, $post){
        global $wpdb;
        if(is_user_logged_in()){

            $forum_id = $post->ID;
            $user_id = get_current_user_id();
            if($this->is_forum_member($forum_id,$user_id) || current_user_can('manage_options')){
                $display.= "<span id='wpwaf_forum_member' data-forum-id='".$forum_id."' >". __('Member','wpwaf') . "</span>";
            }else{
                $display.= "<span id='wpwaf_forum_join' data-forum-id='".$forum_id."' >". __('+ Join','wpwaf') . "</span>";
            }
        }
        return $display;

    }

    public function forum_join(){
        global $wpdb;
        $forum_id = isset($_POST['forum_id']) ? (int) $_POST['forum_id'] : 0;
        $response = array();
 
        if(is_user_logged_in()){
            $user_id = get_current_user_id();
            if(!$this->is_forum_member($forum_id,$user_id)){
                $forum_users = $wpdb->prefix.'forum_users';    
                $wpdb->insert(
                        $forum_users,
                        array(
                            'user_id' => $user_id,
                            'forum_id' => $forum_id,
                            'join_time' => date("Y-m-d H:i:s"),
                        ),
                        array(
                            '%d',
                            '%d',
                            '%s'
                        )
                );

                $response['status'] = 'success';                
            }
        }

        echo json_encode($response);exit;
    }

    public function is_forum_member($forum_id,$user_id){
        global $wpdb;
        $forum_users = $wpdb->prefix.'forum_users';             
        $sql  = $wpdb->prepare( "SELECT * from $forum_users where forum_id = %d and user_id = %d", $forum_id, $user_id);
        $result = $wpdb->get_results($sql);
        if($result){
            return true;
        }else{
            return false;
        }
    }
}
?>
