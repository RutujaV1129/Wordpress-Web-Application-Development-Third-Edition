<?php

class WPWAF_User {

    public function __construct() {
        add_action('show_user_profile', array($this, "add_profile_fields"));
        add_action('edit_user_profile', array($this, "add_profile_fields"));
        
        add_action('edit_user_profile_update', array($this, "save_profile_fields"));
        add_action('personal_options_update', array($this, "save_profile_fields"));
    }

    public function create_forum_member_profile($forum_member_id) {
        global $wpwaf,$wpwaf_topics_data,$wpwa_template_loader;
        
        $user_query = new WP_User_Query(array('include' => array($forum_member_id)));

        $wpwaf_topics_data = array();
        foreach ($user_query->results as $forum_member) {
            $wpwaf_topics_data['display_name'] = $forum_member->data->display_name;
            $wpwaf_topics_data['interested_topics'] = get_user_meta($forum_member->ID, "_wpwaf_interested_topics", TRUE);
            $wpwaf_topics_data['skills'] = get_user_meta($forum_member->ID, "_wpwaf_skills", TRUE);
            $wpwaf_topics_data['country'] = get_user_meta($forum_member->ID, "_wpwaf_country", TRUE);
        }

        $current_user = wp_get_current_user();

        $wpwaf_topics_data['forum_member_status'] = ($current_user->ID == $forum_member_id);
        $wpwaf_topics_data['forum_member_id'] = $forum_member_id;
        $wpwaf_topics_data['forum_list'] = $wpwaf->forum->get_forum_list();

        $wpwa_template_loader->get_template_part("forum-member");
        exit;
    }

    public function get_administrators(){
        $administrators = array();

        $user_query = new WP_User_Query(array('role__in' => array('administrator'),
                 'number' => 25, 'orderby' => 'registered', 'order' => 'desc'));
        foreach ($user_query->results as $member) {
            $administrators[] = $member;
        }
        return $administrators;
    }

    public function get_moderators(){
        $moderators = array();

        $user_query = new WP_User_Query(array('role__in' => array('wpwaf_moderator'),
                 'number' => 25, 'orderby' => 'registered', 'order' => 'desc'));
        foreach ($user_query->results as $member) {
            $moderators[] = $member;
        }
        return $moderators;
    }

    public function add_profile_fields($user) {
        global $wpwa_template_loader,$wpwaf_template_data;

        $interested_topics = esc_html(get_user_meta($user->ID, "_wpwaf_interested_topics", TRUE));
        $skills = esc_html(get_user_meta($user->ID, "_wpwaf_skills", TRUE));
        $country = esc_html(get_user_meta($user->ID, "_wpwaf_country", TRUE));

        $wpwaf_template_data['interested_topics'] = $interested_topics;
        $wpwaf_template_data['skills'] = $skills;
        $wpwaf_template_data['country'] = $country;
        ob_start();
        $wpwa_template_loader->get_template_part("profile-fields");        
        echo ob_get_clean();
    }
    
    public function save_profile_fields() {
        global $user_ID;
   
        $interested_topics = isset($_POST['interested_topics']) ? esc_html(trim($_POST['interested_topics'])) : "";
        $skills = isset($_POST['skills']) ? esc_html(trim($_POST['skills'])) : "";
        $country = isset($_POST['country']) ? esc_html(trim($_POST['country'])) : "";

        update_user_meta($user_ID, "_wpwaf_interested_topics", $interested_topics);
        update_user_meta($user_ID, "_wpwaf_skills", $skills);
        update_user_meta($user_ID, "_wpwaf_country", $country);
    }
}


