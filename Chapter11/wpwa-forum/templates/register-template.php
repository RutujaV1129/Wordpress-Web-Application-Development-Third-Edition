<?php 
    global $wpwaf_registration_params;    
    if(is_array($wpwaf_registration_params)){
        extract($wpwaf_registration_params);
    }
    get_header(); 
?>


<div id='wpwaf_custom_panel'>
    <div class='wpwaf-registration-form-header' ><?php echo __('Register New Account','wpwaf'); ?></div>
    <div id='wpwaf-registration-errors'>
        <?php
        if( isset($wpwaf_registration_params['errors']) && count($wpwaf_registration_params['errors']) > 0) {
            foreach ( $wpwaf_registration_params['errors'] as $error ) {
                echo '<p class="wpwaf_frm_error">' . $error . '</p>';
            }
        }
        ?>
    </div>

    <form id='wpwaf-registration-form' method='post' action='<?php echo get_site_url() . '/user/register'; ?>'>
        <ul>
            <li>
                <label class='wpwaf_frm_label'><?php echo __('Username','wpwaf'); ?></label>
                <input class='wpwaf_frm_field' type='text' id='wpwaf_user' name='wpwaf_user' value='<?php echo isset( $user_login ) ? $user_login : ''; ?>'  />
            </li>
            <li>
                <label class='wpwaf_frm_label'><?php echo __('E-mail','wpwaf'); ?></label>
                <input class='wpwaf_frm_field' type='text' id='wpwaf_email' name='wpwaf_email' value='<?php echo isset( $user_email ) ? $user_email : ''; ?>' />
            </li>
            <li>
                <label class='wpwaf_frm_label'><?php echo __('User Type','wpwaf'); ?></label>
                <select class='wpwaf_frm_field' name='wpwaf_user_type'>
                    <option <?php echo (isset( $user_type ) && $user_type == 'wpwaf_premium_member') ? 'selected' : ''; ?> value='wpwaf_premium_member'><?php echo __('Premium Member','wpwaf'); ?></option>
                    <option <?php echo (isset( $user_type ) && $user_type == 'wpwaf_free_member') ? 'selected' : ''; ?> value='wpwaf_free_member'><?php echo __('Free Member','wpwaf'); ?></option>
                </select>
            </li>
            <li>
                <label class='wpwaf_frm_label' for=''>&nbsp;</label>
                <input type='submit' name='wpwaf_reg_submit' value='<?php echo __('Register','wpwaf'); ?>' />
            </li>
        </ul>
    </form>
</div>

<?php get_footer(); ?>
