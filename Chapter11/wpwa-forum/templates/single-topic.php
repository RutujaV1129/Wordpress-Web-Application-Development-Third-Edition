<?php 
	global $post,$wpwaf,$wpwaf_single_reply_data;
	get_header(); 
	$administrators = $wpwaf->user->get_administrators();
	$moderators 	= $wpwaf->user->get_moderators();
	$topic_replies   = $wpwaf->topic->list_topic_replies($post->ID);
?>

<div id="wpwaf_topic_panel">
	<div id="wpwaf_topic_info">
		<div id="wpwaf_topic_info_left">
			<h3><?php echo $post->post_title; ?></h3>
			<div><?php echo $post->post_content; ?></div>
		</div>
		<div id="wpwaf_topic_info_right">
			<h3><?php _e('Administrators','wpwaf'); ?></h3>
			<div>
				<?php 	foreach ($administrators as $key => $administrator) { ?>
				<div class="wpwaf_forum_admin_image"><?php     echo get_avatar( $administrator->ID, 32 );  ?></div>
						  <div><?php echo $administrator->data->display_name; ?></div>
				<?php	}  ?>
			</div>

			<h3><?php _e('Moderators','wpwaf'); ?></h3>
			<div>
				<?php 	foreach ($moderators as $key => $moderator) { ?>
				<div class="wpwaf_forum_admin_image"><?php     echo get_avatar( $moderator->ID, 32 );  ?></div>
						  <div><?php echo $moderator->data->display_name; ?></div>
				<?php	}  ?>
			</div>
		</div>
	</div>
	<div style="clear:both"></div>

	<?php if(is_user_logged_in()  && ($wpwaf->forum->is_forum_member($post->ID,get_current_user_id() ) || 
	current_user_can('manage_options') ) ) { ?>

	<?php if($wpwaf_single_reply_data['msg'] !== ''){ ?>
		<div class="wpwaf_topic_msg_<?php echo $wpwaf_single_reply_data['msg_status']; ?>"><?php echo $wpwaf_single_reply_data['msg']; ?></div>
	<?php } ?>

	<div class="wpwaf_create_topics_label"><?php _e('Your response','wpwaf'); ?></div>
	<div id="wpwaf_create_replies">
		<form action="" method="POST" >
		<div id="wpwaf_create_replies_editor">
			<textarea name="wpwaf_topic_content" id="wpwaf_topic_content"></textarea>
		</div>
		<div id="">
			<input type="hidden" name="wpwaf_topic_id" value="<?php echo $post->ID; ?>" />
			<input type="submit" name="wpwaf_reply_submit" id="wpwaf_reply_submit" value="<?php _e('Reply','wpwaf'); ?>" />
		</div>
		</form> 

	</div>
	<?php } ?>

<?php if(is_user_logged_in()  && ($wpwaf->forum->is_forum_member($post->ID,get_current_user_id() ) || 
current_user_can('manage_options') ) ) { ?>	
	<div id="wpwaf_forum_topics">
		<?php 	foreach ($topic_replies as $key => $topic_reply) { ?>

		<div class="wpwaf_forum_topic">
		<div class="wpwaf_topic_author_image" ><?php     echo $topic_reply['topic_author_image'];  ?></div>
		<div class="wpwaf_topic_right">
			<div class="wpwaf_topic_reply" >
				<?php echo $topic_reply['topic_content']; ?>
			</div>
			<div class="wpwaf_topic_started wpwaf_topic_stats" ><span><?php _e("Started by","wpwaf"); ?> :</span> <?php echo $topic_reply['topic_author_name']; ?></div>
			
			<div class="wpwaf_topic_started_date wpwaf_topic_stats" ><span><?php _e("Started on","wpwaf"); ?> :</span> <?php echo $topic_reply['topic_date']; ?></div>
			
		</div>
		<div class="wpwaf_clear"></div>
		</div>
		<?php	}  ?>

	</div>
</div>
<?php	}  ?>


<?php get_footer(); ?>