<?php

class WPWAF_Model_Topic {

    public $post_type;
    public $topic_category_taxonomy;
    public $topic_tag_taxonomy;
    public $error_message;

    public function __construct() {
        global $wpwaf;
        
        $this->post_type                = 'wpwaf_topic';
        $this->topic_category_taxonomy  = 'wpwaf_topic_category';
        $this->topic_tag_taxonomy       = 'wpwaf_topic_tag';

        $this->error_message = '';
        add_action( 'init', array( $this, 'create_topics_post_type' ) );
        add_action( 'init', array( $this, 'create_topics_custom_taxonomies' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_topics_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_topic_meta_data' ) );
        add_filter( 'post_updated_messages', array( $this, 'generate_topic_messages' ) );
        add_action( 'admin_notices', array( $this, 'topic_admin_notices' ) );
        add_action( 'p2p_init', array( $this, 'join_topics_to_forums' ) );

        add_action('wp_ajax_nopriv_wpwaf_process_topics', array($this, 'process_topics'));
        add_action('wp_ajax_wpwaf_process_topics', array($this, 'process_topics'));
    }

    public function create_topics_post_type() {
        global $wpwaf;
        
        $params = array();
        
        $params['post_type'] = $this->post_type;
        $params['singular_post_name'] = __('Topic','wpwaf');
        $params['plural_post_name'] = __('Topics','wpwaf');
        $params['description'] = __('Topics','wpwaf');
        $params['supported_fields'] = array('title', 'editor');
        $params['capabilities'] = array(
            'edit_post'              => 'edit_wpwaf_topic',
            'read_post'              => 'read_wpwaf_topic',
            'delete_post'            => 'delete_wpwaf_topic',
            'create_posts'           => 'create_wpwaf_topics',
            'edit_posts'             => 'edit_wpwaf_topics',
            'edit_others_posts'      => 'edit_others_wpwaf_topics',
            'publish_posts'          => 'publish_wpwaf_topics',
            'read_private_posts'     => 'read',
            'read'                   => 'read',
            'delete_posts'           => 'delete_wpwaf_topics',
            'delete_private_posts'   => 'delete_private_wpwaf_topics',
            'delete_published_posts' => 'delete_published_wpwaf_topics',
            'delete_others_posts'    => 'delete_others_wpwaf_topics',
            'edit_private_posts'     => 'edit_private_wpwaf_topics',
            'edit_published_posts'   => 'edit_published_wpwaf_topics'
            );
 
        
        $wpwaf->model_manager->create_post_type($params);
    }

    public function create_topics_custom_taxonomies() {        
        global $wpwaf;
        
        $params = array();
        
        $params['category_taxonomy']    = $this->topic_category_taxonomy;
        $params['post_type']            = $this->post_type;
        
        $params['singular_name']        = __('Topic Category','wpwaf');
        $params['plural_name']          = __('Topic Category','wpwaf');        
        $params['hierarchical']         = true;
        $wpwaf->model_manager->create_custom_taxonomies($params);     
        
        
        $params['category_taxonomy']    = $this->topic_tag_taxonomy;
        $params['post_type']            = $this->post_type;
        
        $params['singular_name']        = __('Topic Tag','wpwaf');
        $params['plural_name']          = __('Topic Tag','wpwaf');   
        $params['capabilities']         = array(
                                                    'manage_terms'      => 'manage_wpwaf_topic_tag',
                                                    'edit_terms'        => 'edit_wpwaf_topic_tag',
                                                    'delete_terms'      => 'delete_wpwaf_topic_tag',
                                                    'assign_terms'      => 'assign_wpwaf_topic_tag'
                                            ); 
        $params['hierarchical'] = false;

        $wpwaf->model_manager->create_custom_taxonomies($params);
        
    }

    public function add_topics_meta_boxes() {
        add_meta_box( 'wpwaf-topics-meta', __('Topic Details','wpwaf'), array( $this, 'display_topics_meta_boxes' ), $this->post_type );
    }

    public function display_topics_meta_boxes() {
        global $wpwaf,$post,$template_data,$wpwa_template_loader;

        $data = array();
        $topic = $post;
        $template_data['topic_post_type']      = $this->post_type;
        $template_data['topic_meta_nonce']     = wp_create_nonce('wpwaf-topic-meta');
        $template_data['topic_sticky_status']  = get_post_meta( $topic->ID, '_wpwaf_topic_sticky_status', true );
        $template_data['topic_docs']           = (array) json_decode(get_post_meta( $topic->ID, '_wpwaf_topic_docs', true ));

        ob_start();
        $wpwa_template_loader->get_template_part( 'topic','meta');
        $display = ob_get_clean();
        echo $display;
    }

    public function save_topic_meta_data() {
        global $post,$wpwaf;

        // Verify the nonce value for secure form submission
        if ( isset($_POST['topic_meta_nonce']) && !wp_verify_nonce($_POST['topic_meta_nonce'], 'wpwaf-topic-meta' ) ) {
             return;
        }

        // Check for the autosaving feature of WordPress
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return;
        }

        if ( isset($_POST['post_type']) && $this->post_type == $_POST['post_type'] && current_user_can( 'edit_wpwaf_topics', $post->ID ) ) {

            $sticky_status  = isset( $_POST['wpwaf_sticky_status'] ) ? sanitize_text_field( trim($_POST['wpwaf_sticky_status']) ) : '';
       
            if ( $sticky_status == '0' )  {
                $this->error_message .= __('Sticky status cannot be empty. <br/>', 'wpwaf' );
            }

            if ( !empty( $this->error_message ) ) {

                remove_action( 'save_post', array( $this, 'save_topic_meta_data' ) );

                $post->post_status = 'draft';
                $post->post_title = isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : '';
                $post->post_content = isset($_POST['post_content']) ? ($_POST['post_content']) : '';
                wp_update_post( $post );

                add_action( 'save_post', array( $this, 'save_topic_meta_data' ) );

                $this->error_message = __('Topic creation failed.<br/>', 'wpwaf' ) . $this->error_message;
                set_transient( $this->post_type."_error_message_$post->ID", $this->error_message, 60 * 10 );

            } else {

                update_post_meta( $post->ID, '_wpwaf_topic_sticky_status', $sticky_status );

                $topic_docs = isset ($_POST['h_wpwaf_files']) ? $_POST['h_wpwaf_files'] : "";
                $topic_docs = json_encode($topic_docs);
                update_post_meta($post->ID, "_wpwaf_topic_docs", $topic_docs);
            }
        } else {
            return;
        }
    }

    public function generate_topic_messages( $messages ) {
        global $wpwaf;
        
        $params = array();        
        $params['post_type'] = $this->post_type;        
        $params['singular_name'] = __('Topic','wpwaf');
        $params['plural_name'] = __('Topics','wpwaf');

        
        $messages = $wpwaf->model_manager->generate_messages($messages,$params);  
        return $messages;

    }

    public function topic_admin_notices(){
        global $post;

        if(!isset($post)){ return; }

        $this->temp_error_message = get_transient( $this->post_type."_error_message_$post->ID" );
        delete_transient( $this->post_type."_error_message_$post->ID" );

        if (!( $this->temp_error_message)){
            return;
        }

        $message = '<div id="wpwaf-errors" class="error below-h2"><p>';
        $message .= $this->temp_error_message;
        $message .= '</p></div>';

        echo $message;
        remove_action( 'admin_notices', array($this,'topic_admin_notices') );
    }

    public function join_topics_to_forums() {
        global $wpwaf;
        p2p_register_connection_type( array(
                'name'  => 'topics_to_forums',
                'from'  => $this->post_type,
                'to'    => $wpwaf->forum->post_type
                ) );
    }




    public function process_topics() {
        global $wpdb;

        $request_data = json_decode(file_get_contents("php://input"));

        $forum_member = isset($_GET['forum_member_id']) ? $_GET['forum_member_id'] : '0';

        if (is_object($request_data) && isset($request_data->topic_title)) {

            $topic_title = isset($request_data->topic_title) ? $request_data->topic_title : '';
            $topic_content = isset($request_data->topic_content) ? $request_data->topic_content : '' ;
            $topic_forum = isset($request_data->topic_forum) ? $request_data->topic_forum : '' ;

            $err = FALSE;
            $err_message = '';

            if ($topic_title == '') {
                $err = TRUE;
                $err_message .= __('Topic Title is required.','wpwaf');
            }
            if ($topic_content == '') {
                $err = TRUE;
                $err_message .= __('Topic Description is required.','wpwaf');
            }
            if ($topic_forum == '') {
                $err = TRUE;
                $err_message .= __('Topic Forum is required.','wpwaf');
            }

            if ($err) {
                echo json_encode(array('status' => 'error', 'msg' => $err_message));
                exit;
            } else {

                $current_user = wp_get_current_user();

                $topic_details = array(
                    'post_title' => esc_html($topic_title),
                    'post_content' => esc_html($topic_content),
                    'post_status' => 'publish',
                    'post_type' => 'wpwaf_topic',
                    'post_author' => $current_user->ID
                );

                $result = wp_insert_post($topic_details);
                if (is_wp_error($result)) {
                    echo json_encode(array('status' => 'error', 'msg' => $result));
                } else {
                    update_post_meta($result, "_wpwaf_topic_sticky_status", 'normal');
                    
                    $topic_relations_table = $wpdb->prefix.'p2p'; 
                    $wpdb->insert(
                            $topic_relations_table,
                            array(
                                'p2p_from' => $result,
                                'p2p_to' => $topic_forum,
                                'p2p_type' => 'topics_to_forums',
                            ),
                            array(
                                '%d',
                                '%d',
                                '%s'
                            )
                    );


                    echo json_encode(array('status' => 'success'));
                }
            }
            exit;
        } else {

            $result = $this->list_topics($forum_member);
            echo json_encode($result);
            exit;
        }
    }

    

    public function list_topics($forum_member_id) {
        $topics = new WP_Query(array('author' => $forum_member_id, 'post_type' => 'wpwaf_topic', 
                                       'post_status' => 'publish','posts_per_page' => 15, 
                                       'orderby' => 'date'));
        $data = array();


        if ($topics->have_posts()) : while ($topics->have_posts()) : $topics->the_post();

                $topic_id = get_the_ID();
                $topic_content = get_the_content();
                $sticky_status = get_post_meta($topic_id, '_wpwaf_topic_sticky_status', TRUE);
                
                array_push($data, array("ID" => $topic_id, "topic_title" => get_the_title(), "topic_status" => $sticky_status,
                    "topic_content" => $topic_content));

            endwhile;
        endif;
// print_R($data);exit;
        return $data;
    }

    
    
}


?>
