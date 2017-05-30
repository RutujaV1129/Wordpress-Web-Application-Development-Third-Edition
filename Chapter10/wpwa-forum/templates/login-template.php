<?php global $wpwaf_login_params;
      if(is_array($wpwaf_login_params)){
        extract($wpwaf_login_params);
      }
      get_header(); ?>

<div id='wpwaf_custom_panel'>
    <div class='wpwaf-login-form-header' ><?php echo __('Login','wpwaf'); ?></div>
    <div id='wpwaf-login-errors'>
        <?php

        if ( isset($wpwaf_login_params['errors']) && count($wpwaf_login_params['errors']) > 0) {
            foreach ( $wpwaf_login_params['errors'] as $error ) {
                echo '<p class="wpwaf_frm_error">'.$error .'</p>';
            }
        }


        if( isset( $wpwaf_login_params['success_message'] ) && $wpwaf_login_params['success_message'] != ""){
            echo '<p class="wpwaf_frm_success">' . $wpwaf_login_params['success_message'] . '</p>';
        }

        ?>
    </div>
    <form method='post' action='<?php echo site_url(); ?>/user/login' id='wpwaf_login_form' name='wpwaf_login_form'>
        <ul>
            <li>
                <label class='wpwaf_frm_label' for='username'><?php echo __('Username','wpwaf'); ?></label>
                <input class='wpwaf_frm_field' type='text'  name='wpwaf_username' value='<?php echo isset( $username ) ? $username : ''; ?>' />
            </li>
            <li>
                <label class='wpwaf_frm_label' for='password'><?php echo __('Password','wpwaf'); ?></label>
                <input class='wpwaf_frm_field' type='password' name='wpwaf_password' value="" />
            </li>
            <li>
                <label class='wpwaf_frm_label' >&nbsp;</label>
                <input  type='submit'  name='wpwaf_login_submit' value='<?php echo __('Login','wpwaf'); ?>' />
            </li>
        </ul>
    </form>
    <?php do_action('wpwaf_social_login'); ?>
</div>

<?php get_footer(); ?>
