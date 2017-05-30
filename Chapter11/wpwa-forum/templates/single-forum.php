<?php 
	global $post,$wpwaf,$wpwaf_single_topic_data;
	get_header(); 

	// echo "<pre>";print_r($post);exit;

	$administrators = $wpwaf->user->get_administrators();
	$moderators 	= $wpwaf->user->get_moderators();
	$forum_topics   = $wpwaf->topic->list_forum_topics($post->ID);
?>

<div id="wpwaf_forum_panel">
	<div id="wpwaf_forum_info">
		<div id="wpwaf_forum_info_left">
			<h3><?php echo $post->post_title; ?> <?php echo apply_filters('wpwaf_forum_header_buttons', '' , $post); ?></h3>
			<div><?php echo $post->post_content; ?></div>
		</div>
		<div id="wpwaf_forum_info_right">
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
	<?php if(current_user_can('edit_wpwaf_topics') && ($wpwaf->forum->is_forum_member($post->ID,get_current_user_id() ) || 
	current_user_can('manage_options') )) { ?>

	<?php if($wpwaf_single_topic_data['msg'] !== ''){ ?>
		<div class="wpwaf_forum_msg_<?php echo $wpwaf_single_topic_data['msg_status']; ?>"><?php echo $wpwaf_single_topic_data['msg']; ?></div>
	<?php } ?>

	
	<div id="wpwaf_create_topics">
		<form action="" method="POST" >

			<div class="wpwaf_create_topics_label"><?php _e('Topic Title','wpwaf'); ?></div>
			<div id="wpwaf_create_topics_editor">
				<input type="text" name="wpwaf_topic_title" id="wpwaf_topic_title" />
			</div>
			<div class="wpwaf_create_topics_label"><?php _e('Topic Content','wpwaf'); ?></div>
			<div id="wpwaf_create_topics_editor">
				<textarea name="wpwaf_topic_content" id="wpwaf_topic_content"></textarea>
			</div>
			<div id="">
				<input type="hidden" name="wpwaf_forum_id" value="<?php echo $post->ID; ?>" />
				<input type="submit" name="wpwaf_topic_submit" id="wpwaf_topic_submit" value="<?php _e('Create','wpwaf'); ?>" />
			</div>
		</form> 

	</div>
	<?php } ?>


	<div style="clear:both"></div>
	<div id="wpwaf_forum_topics">
		<?php 	foreach ($forum_topics as $key => $forum_topic) { ?>

		<div class="wpwaf_forum_topic">
		<div class="wpwaf_topic_sticky_status"><?php echo $forum_topic['sticky_status']; ?></div>
		<div class="wpwaf_topic_author_image" ><?php     echo $forum_topic['topic_author_image'];  ?></div>
		<div class="wpwaf_topic_right">
			<div class="wpwaf_topic_title" >
				<a href="<?php echo get_permalink($forum_topic['ID']); ?>"><?php echo $forum_topic['topic_title']; ?></a>
			</div>
			<div class="wpwaf_topic_started wpwaf_topic_stats" ><span><?php _e("Started by","wpwaf"); ?> :</span> <?php echo $forum_topic['topic_author_name']; ?></div>
			<!-- <div class="wpwaf_topic_last_reply wpwaf_topic_stats" ><span><?php _e("Last Reply by","wpwaf"); ?> :</span> <?php echo $forum_topic['topic_author_name']; ?></div> -->
		
			<div class="wpwaf_topic_started_date wpwaf_topic_stats" ><span><?php _e("Started on","wpwaf"); ?> :</span> <?php echo $forum_topic['topic_date']; ?></div>
			<!-- <div class="wpwaf_topic_last_reply_date wpwaf_topic_stats" ><span><?php _e("Last Reply on","wpwaf"); ?> :</span> <?php echo $forum_topic['topic_author_name']; ?></div> -->
		
		</div>
		<div class="wpwaf_clear"></div>

		</div>
		<?php	}  ?>
	</div>
</div>

<?php get_footer(); ?>