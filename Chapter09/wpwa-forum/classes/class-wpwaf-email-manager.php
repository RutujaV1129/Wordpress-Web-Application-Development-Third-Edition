<?php

class WPWAF_Email_Manager {

    public function __construct() {
        add_action('new_to_publish', array($this, 'send_topic_notifications'));
        add_action('draft_to_publish', array($this, 'send_topic_notifications'));
        add_action('pending_to_publish', array($this, 'send_topic_notifications'));

    }

    

    public function send_topic_notifications($post) {
        global $wpdb;

        $permitted_posts = array('wpwaf_topic');
        if (isset($_POST['post_type']) && in_array($_POST['post_type'], $permitted_posts)) {

            require_once ABSPATH . WPINC . '/class-phpmailer.php';

            require_once ABSPATH . WPINC . '/class-smtp.php';
            $phpmailer = new PHPMailer(true);

            $phpmailer->From = "admin@gmail.com";
            $phpmailer->FromName = __("Forum Application","wpwaf");

            $phpmailer->SMTPAuth = true;
            $phpmailer->IsSMTP(); // telling the class to use SMTP
            $phpmailer->Host = "ssl://smtp.gmail.com"; // SMTP server
            $phpmailer->Username = "admin@gmail.com";
            $phpmailer->Password = "password";
            $phpmailer->Port = 465;
            $phpmailer->addAddress('admin@gmail.com', 'John');
            $phpmailer->Subject = __("New Topic on Forum Application","wpwaf");

            $subscribers = array();
            $user_query = new WP_User_Query(array('role__in' => array('administrator','wpwaf_moderator'),
                 'number' => 10, 'orderby' => 'registered', 'order' => 'desc'));
            foreach ($user_query->results as $member) {
                $subscribers[] = $member;
            }

            $author_id=$post->post_author;
            $author = get_userdata($author_id);
            $subscribers[] = $author;

            foreach ($subscribers as $subscriber) {
                $phpmailer->AddBcc($subscriber->user_email, $subscriber->user_nicename);
            }

            $phpmailer->Body = __("New Topic is available","wpwaf") . get_permalink($post->ID);
            $phpmailer->Send();
            
        }
    }

}


