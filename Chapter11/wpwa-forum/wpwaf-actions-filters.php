<?php

add_filter( 'bulk_actions-edit-wpwaf_topic', 'topic_action_buttons' );
 
function topic_action_buttons($bulk_actions) {
  $bulk_actions['wpwaf_topic_normal_switch'] = __( 'Mark Topic as Normal', 'wpwaf');
  $bulk_actions['wpwaf_topic_sticky_switch'] = __( 'Mark Topic as Sticky', 'wpwaf');
  $bulk_actions['wpwaf_topic_super_sticky_switch'] = __( 'Mark Topic as Super Sticky', 'wpwaf');
  return $bulk_actions;
}

add_filter( 'handle_bulk_actions-edit-wpwaf_topic', 'topic_action_handler', 10, 3 );
 
function topic_action_handler( $redirect_to, $do_action, $post_ids ) {
  if ( !in_array($do_action, array('wpwaf_topic_normal_switch','wpwaf_topic_sticky_switch',
    'wpwaf_topic_super_sticky_switch' ) ) ) {
    return $redirect_to;
  }
  foreach ( $post_ids as $post_id ) {
    switch ($do_action) {
        case 'wpwaf_topic_normal_switch':
            update_post_meta($post_id, '_wpwaf_topic_sticky_status', 'normal');
            break;
        
        case 'wpwaf_topic_sticky_switch':
            update_post_meta($post_id, '_wpwaf_topic_sticky_status', 'sticky');
            break;

        case 'wpwaf_topic_super_sticky_switch':
            update_post_meta($post_id, '_wpwaf_topic_sticky_status', 'super_sticky');
            break;
    }
    
  }
  $redirect_to = add_query_arg( 'bulk_emailed_posts', count( $post_ids ), $redirect_to );
  return $redirect_to;
}


add_action( 'admin_footer-post-new.php', 'wpwaf_create_post_status_list' );
add_action( 'admin_footer-post.php', 'wpwaf_create_post_status_list' );
function wpwaf_create_post_status_list(){
  global $post,$wpwaf;
  $complete = '';
  $label = '';
  if($post->post_type == 'wpwaf_topic'){
    $complete = ' selected=selected ';
    $label = "<span id='post-status-display'>".__('Resolved', 'wpwaf')."</span>";
?>
    <script>
    jQuery(document).ready(function($){
      $("select#post_status").append("<option value='wpwaf_topic_resolved' <?php echo $complete; ?> ><?php echo __('Resolved Status', 'wpwaf'); ?></option>");
      $(".misc-pub-section label").append("<?php echo $label; ?>");
    });
  </script>
<?php
  }
}

function wpwaf_custom_post_status(){
    register_post_status( 'wpwaf_topic_resolved');
}
// add_action( 'init', 'wpwaf_custom_post_status' );



function wpwaf_topic_list_filters() {
  global $typenow,$wpwaf;
  $topic_sticky_status = isset($_GET['wpwaf_topic_sticky_status']) ? $_GET['wpwaf_topic_sticky_status'] : '';
  if( $typenow == $wpwaf->topic->post_type ){

    $display  = "<select name='wpwaf_topic_sticky_status' id='wpwaf_topic_sticky_status' class='postform'>";
    $display .= "<option value=''>".__('Show All Topics', 'wpwaf')."</option>";
    $display .= "<option ". selected( $topic_sticky_status , 'normal',false) ." value='normal'>".__('Normal Topics', 'wpwaf') ."</option>";
    $display .= "<option ". selected($topic_sticky_status, 'sticky',false) ." value='sticky'>".__('Sticky Topics', 'wpwaf')."</option>";
    $display .= "<option ". selected($topic_sticky_status, 'super_sticky',false) ." value='super_sticky'>".__('Super Sticky Topics', 'wpwaf')."</option>";

    $display .= "</select>";
    echo $display;
  }
}
add_action( 'restrict_manage_posts','wpwaf_topic_list_filters' );


add_filter( 'parse_query', 'wpwaf_topic_list_filter_query' );
function wpwaf_topic_list_filter_query( $query ){
  global $pagenow,$wpwaf;
  $type = $wpwaf->topic->post_type;
  if (isset($_GET['post_type'])) {
    $type = $_GET['post_type'];
  }

  if ($wpwaf->topic->post_type == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['wpwaf_topic_sticky_status']) && $_GET['wpwaf_topic_sticky_status'] != '') {
    $query->query_vars['meta_key'] = '_wpwaf_topic_sticky_status';
    $query->query_vars['meta_value'] = $_GET['wpwaf_topic_sticky_status'];
  }
}



add_filter( 'manage_edit-wpwaf_topic_columns', 'wpwaf_topic_list_columns' ) ;
function wpwaf_topic_list_columns( $columns ) {
  $columns = array(
    'cb' => '<input type="checkbox" />',
    'title' => __( 'Topic' ,'wpwaf' ),
    'status' => __( 'Sticky Status' ,'wpwaf' ),
    'date' => __( 'Date' ,'wpwaf' ),
  );
  return $columns;
}


add_action( 'manage_wpwaf_topic_posts_custom_column', 'wpwaf_manage_topic_columns', 10, 2 );
function wpwaf_manage_topic_columns( $column, $post_id ) {
  global $post;
  switch( $column ) {
    case 'status' :
      $status = get_post_meta( $post_id, '_wpwaf_topic_sticky_status', true );
      if ( empty( $status ) )
        echo __( '-' );
      else
        echo $status;
      break;
    default :
      break;
  }
}




add_action('admin_menu', 'wpwaf_options_menu');
function wpwaf_options_menu() {
  add_options_page('WPWAF Options', 'WPWAF Options','administrator', __FILE__, 'wpwaf_options_page');
  add_action( 'admin_init', 'wpwaf_register_settings' );
}

function wpwaf_register_settings() {
  register_setting( 'wpwaf-settings-group', 'option1' );
  register_setting( 'wpwaf-settings-group', 'option2' );
}


function wpwaf_options_page() {
?>
<div class="wrap">
<form method="post" action="options.php">
  <?php settings_fields( 'wpwaf-settings-group' ); ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Option1</th>
        <td><input type="text" name="option1" value="<?php echo 
get_option('option1'); ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">Option2</th>
        <td><input type="text" name="option2" value="<?php echo 
get_option('option2'); ?>" /></td>
      </tr>
    </table>
  <?php submit_button(); ?>
</form>
</div>
<?php } 



function everytenminutes($schedules) {
    $schedules['everytenminutes'] = array(
        'interval' => 60*10,
        'display' => __('Once Ten Minutes','wpwaf')
    );
    return $schedules;
}
add_filter('cron_schedules', 'everytenminutes');

add_action("notification_sender", "notification_send");
function notification_send() {
    global $wpdb;

    require_once ABSPATH . WPINC . '/class-phpmailer.php';

    require_once ABSPATH . WPINC . '/class-smtp.php';

    $phpmailer = new PHPMailer(true);

    $phpmailer->From = "example@gmail.com";
    $phpmailer->FromName = __("Forum Application","wpwaf");
    $phpmailer->SMTPAuth = true;
    $phpmailer->IsSMTP(); 
    $phpmailer->Host = "ssl://smtp.gmail.com"; 
    $phpmailer->Username = "example@gmail.com";
    $phpmailer->Password = "password";
    $phpmailer->Port = 465;
    $phpmailer->IsHTML(true);

    $phpmailer->Subject = __("New Forum Updates","wpwaf");

    $topic_replies = $wpdb->prefix.'topic_replies'; 
    $sql  = $wpdb->prepare( "SELECT * from $topic_replies where notify_status = %d ", 0 );
    $result = $wpdb->get_results($sql);
    $topics = array();

    $message = "";
    if($result){
        foreach ($result as $key => $value) {
            $topic_id = $value->topic_id;

            $message.= "<a href='" . get_permalink($topic_id) . "'>" . get_the_title($topic_id) . "</a>";

            $topic_subscriptions_table = $wpdb->prefix.'topic_subscriptions'; 
            $sql_sub  = $wpdb->prepare( "SELECT tsr.*,usr.user_nicename,usr.user_email
                                FROM $wpdb->users as usr inner join $topic_subscriptions_table as tsr on usr.ID = tsr.id  WHERE 
                topic_id = %d ", $topic_id );
            $result_sub = $wpdb->get_results($sql_sub);
            
            if($result_sub){
                foreach ($result_sub as $key => $subs_data) {
                    $phpmailer->AddBcc($subs_data->user_email, $subs_data->user_nicename);
                }
            }

            $phpmailer->Body = __("New Updates from your favorite topics","wpwaf"). '<br/><br/><br/>'  . $message;
            // $phpmailer->Send();

            $wpdb->update( 
                $topic_replies, 
                array( 
                    'notify_status' => 1
                ), 
                array( 'topic_id' => $topic_id ), 
                array( 
                    '%d' 
                ), 
                array( '%d' ) 
            );
        }
    }    
}


?>



