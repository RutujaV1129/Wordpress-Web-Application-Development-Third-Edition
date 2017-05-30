<?php

class WPWAF_User {

    public function __construct() {

    }

    public function create_forum_member_profile($forum_member_id) {
        global $wpwaf,$wpwaf_topics_data,$wpwa_template_loader;
        
        $user_query = new WP_User_Query(array('include' => array($forum_member_id)));

        $wpwaf_topics_data = array();
        foreach ($user_query->results as $forum_member) {
            $wpwaf_topics_data['display_name'] = $forum_member->data->display_name;
        }

        $current_user = wp_get_current_user();

        $wpwaf_topics_data['forum_member_status'] = ($current_user->ID == $forum_member_id);
        $wpwaf_topics_data['forum_member_id'] = $forum_member_id;
        $wpwaf_topics_data['forum_list'] = $wpwaf->forum->get_forum_list();

        $wpwa_template_loader->get_template_part("forum-member");
        exit;
    }
}


