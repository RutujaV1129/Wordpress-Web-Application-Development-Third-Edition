<?php 

if ( !function_exists( 'wpwaf_send_user_notification' ) ) {

    function wpwaf_send_user_notification($user_id, $plaintext_pass = '', $activate_code = '') {

        $user = new WP_User($user_id);
        $user_login = stripslashes($user->user_login);
        $user_email = stripslashes($user->user_email);

        $message = sprintf(__('New user registration on %s:','wpwaf'), get_option('blogname')) . '\r\n\r\n';
        $message .= sprintf(__('Username: %s','wpwaf'), $user_login) . '\r\n\r\n';
        $message .= sprintf(__('E-mail: %s','wpwaf'), $user_email) . '\r\n';

        @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration','wpwaf'), get_option('blogname')), $message);

        if (empty($plaintext_pass))
            return;

        $act_link = site_url() . "/user/activate/?wpwaf_activation_code=$activate_code";

        $message = __('Hi there,','wpwaf') . '\r\n\r\n';
        $message .= sprintf(__('Welcome to %s! Please activate your account using the link:','wpwaf'), get_option('blogname')) . '\r\n\r\n';
        $message .= sprintf(__('<a href="%s">%s</a>','wpwaf'), $act_link, $act_link) . '\r\n';
        $message .= sprintf(__('Username: %s','wpwaf'), $user_login) . '\r\n';
        $message .= sprintf(__('Password: %s','wpwaf'), $plaintext_pass) . '\r\n\r\n';

        wp_mail($user_email, sprintf(__('[%s] Your username and password','wpwaf'), get_option('blogname')), $message);

    }

}

if ( !function_exists( 'wpwaf_add_query_string' ) ) {
    function wpwaf_add_query_string($link, $query_str) {

        $build_url = $link;

        $query_comp = explode('&', $query_str);

        foreach ($query_comp as $param) {
            $params = explode('=', $param);
            $key = isset($params[0]) ? $params[0] : '';
            $value = isset($params[1]) ? $params[1] : '';
            $build_url = add_query_arg($key, $value, $build_url);
        }

        return $build_url;
    }
}

?>