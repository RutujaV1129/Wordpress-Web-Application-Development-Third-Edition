<?php
/*
   Plugin Name: WPWA Questions
   Plugin URI : -
   Description: Question and Answer Interface using WordPress Custom Post Types and Comments
   Version    : 1.0
   Author     : Rakhitha Nimesh
   Author URI: http://www.wpexpertdeveloper.com/
   License: GPLv2 or later
   Text Domain: wpwa-questions
 
 */
 
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'WPWA_Questions' ) ) {
    
    class WPWA_Questions{
    
        private static $instance;

        public static function instance() {
            
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPWA_Questions ) ) {
                self::$instance = new WPWA_Questions();
                self::$instance->setup_constants();

                self::$instance->includes();
                
                add_action( 'admin_enqueue_scripts',array(self::$instance,'load_admin_scripts'),9);
                add_action( 'wp_enqueue_scripts',array(self::$instance,'load_scripts'),9);

                add_action( 'init', array(self::$instance,'register_wp_questions'));
                add_filter( 'comments_template', array(self::$instance,'load_comments_template'));
                add_action( 'wp_ajax_mark_answer_status', array(self::$instance,'mark_answer_status'));
                add_filter( 'archive_template', array(self::$instance,'questions_list_template' ));
                
            }
            return self::$instance;
        }

        public function setup_constants() { 

            if ( ! defined( 'WPWA_VERSION' ) ) {
                define( 'WPWA_VERSION', '1.0' );
            }

            if ( ! defined( 'WPWA_PLUGIN_DIR' ) ) {
                define( 'WPWA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            if ( ! defined( 'WPWA_PLUGIN_URL' ) ) {
                define( 'WPWA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }

        }
        
        public function load_scripts(){

            wp_enqueue_script( 'jquery' );
            wp_register_script( 'wpwa-questions', plugins_url( 'js/questions.js', __FILE__ ), array('jquery'), '1.0', TRUE );
            wp_enqueue_script( 'wpwa-questions' );

            wp_register_style( 'wpwa-questions-css', plugins_url( 'css/questions.css', __FILE__ ) );
            wp_enqueue_style( 'wpwa-questions-css' );

            $config_array = array(
                    'ajaxURL' => admin_url( 'admin-ajax.php' ),
                    'ajaxNonce' => wp_create_nonce( 'ques-nonce' )
            );

            wp_localize_script( 'wpwa-questions', 'wpwaconf', $config_array );
            
        }
        
        public function load_admin_scripts(){
            
        }
        
        private function includes() {
            
            require_once WPWA_PLUGIN_DIR . 'functions.php';
            
        }

        public function load_textdomain() {
            
        }

        public function register_wp_questions() {
            $labels = array(
                'name'                  => __('Questions', 'wpwa_questions'),
                'singular_name'         => __('Question', 'wpwa_questions'),
                'add_new'               => __('Add New', 'wpwa_questions'),
                'add_new_item'          => __('Add New Question', 'wpwa_questions'),
                'edit_item'             => __('Edit Questions', 'wpwa_questions'),
                'new_item'              => __('New Question', 'wpwa_questions'),
                'view_item'             => __('View Question', 'wpwa_questions'),
                'search_items'          => __('Search Questions', 'wpwa_questions'),
                'not_found'             => __('No Questions found', 'wpwa_questions'),
                'not_found_in_trash'    => __('No Questions found in Trash', 'wpwa_questions'),
                'parent_item_colon'     => __('Parent Question:', 'wpwa_questions'),
                'menu_name'             => __('Questions', 'wpwa_questions'),
            );

            $args = array(
                'labels'                => $labels,
                'hierarchical'          => true,
                'description'           => __('Questions and Answers','wpwa_questions'),
                'supports'              => array('title', 'editor', 'comments'),
                'public'                => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'show_in_nav_menus'     => true,
                'publicly_queryable'    => true,
                'exclude_from_search'   => false,
                'has_archive'           => true,
                'query_var'             => true,
                'can_export'            => true,
                'rewrite'               => true,
                'capability_type'       => 'post'
            );

            register_post_type('wpwa_question', $args);
        }

        public function load_comments_template($template){
            return WPWA_PLUGIN_DIR.'comments.php';
        }

        public function comment_list( $comment, $args, $depth ) {
            global $post;

            $GLOBALS['comment'] = $comment;

            // Get current logged in user and author of question
            $current_user           = wp_get_current_user();
            $author_id              = $post->post_author;
            $show_answer_status     = false;

            // Set the button status for authors of the question
            if ( is_user_logged_in() && $current_user->ID == $author_id ) {
                $show_answer_status = true;
            }

            // Get the correct/incorrect status of the answer
            $comment_id = get_comment_ID();
            $answer_status = get_comment_meta( $comment_id, "_wpwa_answer_status", true );



            ?>
            <li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
            <article id="comment-<?php comment_ID(); ?>">
                <header class="comment-meta comment-author vcard">
                        <?php
                        // Display image of a tick for correct answers
                        if ( $answer_status ) {
                            echo "<div class='tick'><img src='".plugins_url( 'img/tick.png', __FILE__ )."' alt='Answer Status' /></div>";
                        }
                        ?>
                        <?php echo get_avatar( $comment, $size = '48', $default = '<path_to_url>' ); ?>
                <?php printf(__('<cite class="fn">Answered by %s</cite>'), get_comment_author_link() ) ?>

                </header>
                <?php if ( '0' == $comment->comment_approved ) : ?>
                <em><?php _e('Your answer is awaiting moderation.') ?></em>
                <br />
                <?php endif; ?>


                <div class="comment_text_panel"><?php comment_text() ?></div>

                <div class="reply">
                <?php comment_reply_link( array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']) ) ) ?>
                </div>


                <div>
                        <?php
                        // Display the button for authors to make the answer as correct or incorrect
                        if ( $show_answer_status ) {

                            $question_status = '';
                            $question_status_text = '';
                            if ( $answer_status ) {
                                $question_status = 'invalid';
                                $question_status_text = __('Mark as Incorrect','wpwa_questions');
                            } else {
                                $question_status = 'valid';
                                $question_status_text = __('Mark as Correct','wpwa_questions');
                            }

                    ?>
                    <input type="button" value="<?php echo $question_status_text; ?>"  class="answer-status answer_status-<?php echo $comment_id; ?>"
                           data-ques-status="<?php echo $question_status; ?>" />
                    <input type="hidden" value="<?php echo $comment_id; ?>" class="hcomment" />

                            <?php
                        }
                ?>
                </div>
            </article>
            </li>
        <?php
        }

        public function mark_answer_status() {

            $data = isset( $_POST['data'] ) ? $_POST['data'] : array();

            $comment_id     = isset( $data["comment_id"] ) ? absint($data["comment_id"]) : 0;
            $answer_status  = isset( $data["status"] ) ? $data["status"] : 0;
            

            // Mark answers in correct status to incorrect
            // or incorrect status to correct
            if ("valid" == $answer_status) {
                update_comment_meta( $comment_id, "_wpwa_answer_status", 1 );
            } else {
                update_comment_meta( $comment_id, "_wpwa_answer_status", 0 );
            }

            echo json_encode( array("status" => "success") );
            exit;
        }

        public function get_correct_answers($post_id) {
            $args = array(
                'post_id' => $post_id,
                'status' => 'approve',
                'meta_key' => '_wpwa_answer_status',
                'meta_value' => 1,
            );

            // Get number of correct answers for given question
            $comments = get_comments($args);
            printf(__('<cite class="fn">%s</cite> correct answers','wpwa_questions'), count($comments));
        }

        public function questions_list_template($template){
            global $post;

            if ( is_post_type_archive ( 'wpwa_question' ) ) {
                $template = WPWA_PLUGIN_DIR . '/questions-list-template.php';
            }
            return $template;
        }
        
    }
}



function WPWA_Questions() {
    global $wpwa;
	$wpwa = WPWA_Questions::instance();
}

WPWA_Questions();




