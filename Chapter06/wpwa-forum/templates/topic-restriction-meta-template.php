<?php
	global $wp_roles,$wpwaf,$topic_restriction_params;
	extract($topic_restriction_params);

	$user_roles = $wp_roles->get_names();

    $visibility = get_post_meta( $post->ID, '_wpwaf_topic_visibility', true );
    $redirection_url = get_post_meta( $post->ID, '_wpwaf_topic_redirection_url', true );

    $visible_roles = get_post_meta( $post->ID, '_wpwaf_topic_roles', true );
    if(!is_array($visible_roles)){
    	$visible_roles = array();
    }

    $show_role_field = '';
    if( $visibility == 'role'){
    	$show_role_field = " style='display:block;' ";
    }
?>


<div class="wpwaf_topic_meta_row">
	<div class="wpwaf_topic_meta_row_label"><strong><?php _e('Visibility','wpwaf'); ?></strong></div>
	<div class="wpwaf_topic_meta_row_field">
		<select id="wpwaf_topic_visibility" name="wpwaf_topic_visibility" class="wpwaf-select2-setting">
			<option value='none' <?php selected('none',$visibility); ?> ><?php _e('Please Select','wpwaf'); ?></option>
			<option value='all' <?php selected('all',$visibility); ?> ><?php _e('Everyone','wpwaf'); ?></option>
			<option value='guest' <?php selected('guest',$visibility); ?> ><?php _e('Guests','wpwaf'); ?></option>
			<option value='member' <?php selected('member',$visibility); ?> ><?php _e('Members','wpwaf'); ?></option>
			<option value='role' <?php selected('role',$visibility); ?> ><?php _e('Selected User Roles','wpwaf'); ?></option>
			<?php echo apply_filters('wpwaf_custom_restrictions','', $visibility); ?>
		</select>
	</div>
</div>
<div class="wpwaf-clear"></div>

<div id="wpwaf_topic_role_panel" class="wpwaf_topic_meta_row" <?php echo $show_role_field; ?> >
	<div class="wpwaf_topic_meta_row_label"><strong><?php _e('Allowed User Roles','wpwaf'); ?></strong></div>
	<div class="wpwaf_topic_meta_row_field">
		<?php foreach($user_roles as $role_key => $role){
				$checked_val = ''; 

				if(in_array($role_key, $visible_roles)  ){
					$checked_val = ' checked '; 
	
				}
				if($role_key != 'administrator'){
			?>
			<input type="checkbox" <?php echo $checked_val; ?> name="wpwaf_topic_roles[]" value='<?php echo $role_key; ?>'><?php echo $role; ?><br/>
			<?php } ?>	
		<?php } ?>		
	</div>

</div>

<div class="wpwaf-clear"></div>

<div class="wpwaf_topic_meta_row">
	<div class="wpwaf_topic_meta_row_label"><strong><?php _e('Redirection URL','wpwaf'); ?></strong></div>
	<div class="wpwaf_topic_meta_row_field">
		<input type='text' id="wpwaf_topic_redirection_url" name="wpwaf_topic_redirection_url" value="<?php echo $redirection_url; ?>" />
			
	</div>
</div>
<div class="wpwaf-clear"></div>

<?php wp_nonce_field( 'wpwaf_restriction_settings', 'wpwaf_restriction_settings_nonce' ); ?>

