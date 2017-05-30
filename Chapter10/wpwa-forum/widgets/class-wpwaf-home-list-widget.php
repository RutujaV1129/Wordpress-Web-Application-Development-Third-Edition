<?php
class WPWAF_Home_List_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
                        'home_list_widget', // Base ID
                        'Home_List_Widget', // Name
                        array('description' => __('Home List Widget', 'wpwaf'),) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance) {
        global $wpwa_template_loader,$home_list_data;
        
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $list_type = apply_filters('widget_list_type', $instance['list_type']);

        echo $before_widget;
        if (!empty($title))
            echo $before_title . $title . $after_title;
        
        switch ($list_type) {
            case 'rec_topic':
                // Get list of recent topics from the database
                $topics_query = new WP_Query(array('post_type' => 'wpwaf_topic','post_status' =>'publish',
                  'order' => 'desc', 'orderby' => 'date' ));

                $home_list_data = array();
                $home_list_data["records"] = array();

                if($topics_query->have_posts()){
                    while($topics_query->have_posts()) : $topics_query->the_post();
                        array_push($home_list_data["records"], array("ID" => get_the_ID(), "title" => get_the_title(), "type"=>"favorite"));
                    endwhile;
                }
                $home_list_data["title"] = $title;
            
                ob_start();
                $wpwa_template_loader->get_template_part("home","list");
                echo ob_get_clean();
                break;

            case 'rec_users':
                // Get list of recent forum users from the database
                $user_query = new WP_User_Query(array('role__in' => array('wpwaf_free_member','wpwaf_premium_member'),
                 'number' => 10, 'orderby' => 'registered', 'order' => 'desc'));
                $home_list_data = array();
                $home_list_data["records"] = array();
                foreach ($user_query->results as $member) {
                    array_push($home_list_data["records"], array("ID" => $member->data->ID, "title" => $member->data->display_name, "type"=>""));
                }
                $home_list_data["title"] = $title;
                ob_start();
                $wpwa_template_loader->get_template_part("home","list");
                echo ob_get_clean();
                break;


            case 'fav_topics':
                // Will be implemented once we add feature to mark topics as favorite
        }



        echo $after_widget;
    }

    /**
     * Back-end widget form.
     * @param array $instance Previously saved values from database.
     */
    public function form($instance) {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('New title', 'wpwaf');
        }

        if (isset($instance['list_type'])) {
            $list_type = $instance['list_type'];
        } else {
            $list_type = 0;
        }
?>
        <p>
            <label for="<?php echo $this->get_field_name('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>


        <p>
            <label for="<?php echo $this->get_field_name('list_type'); ?>"><?php _e('List Type:'); ?></label>

        <select class="widefat" id="<?php echo $this->get_field_id('list_type'); ?>" name="<?php echo $this->get_field_name('list_type'); ?>" >
            <option <?php selected( $list_type, 0 ); ?>  value='0'>Select</option>
            <option <?php selected( $list_type, "rec_topic" ); ?>  value='rec_topic'><?php echo __('Recent Topics','wpwaf'); ?></option>
            <option <?php selected( $list_type, "rec_users" ); ?>  value='rec_users'><?php echo __('Recent Users','wpwaf'); ?></option>
            <option <?php selected( $list_type, "fav_topics" ); ?>  value='fav_topics'><?php echo __('Favorite Topics','wpwaf'); ?></option>
        </select>
    </p>
<?php
    }

    /**
     * Sanitize widget form values as they are saved.

     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
        $instance['list_type'] = (!empty($new_instance['list_type']) ) ? strip_tags($new_instance['list_type']) : '';

        return $instance;
    }

}

?>
