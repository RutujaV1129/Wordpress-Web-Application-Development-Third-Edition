<?php global $wpwaf_forum_list_params; ?>
<div class='wpwaf_forum_list'>
	<div class='wpwaf_forum_list_header'><?php _e('Forums','wpwaf'); ?></div>
	<div class='wpwaf_forum_list_panel'>

		<div class='wpwaf_forum_list_item_head'>
    		<div class='wpwaf_forum_list_name'><?php _e('Forum Title','wpwaf'); ?></div>
			<div class='wpwaf_forum_list_count'><?php _e('Topics Count','wpwaf'); ?></div>
			<div class='wpwaf_clear'></div>
		</div>

		<?php foreach ($wpwaf_forum_list_params['forums'] as $forum) { ?>
			<div class='wpwaf_forum_list_item'>
        		<div class='wpwaf_forum_list_name'>
        			<a href='<?php echo get_permalink($forum->ID); ?>'><?php echo $forum->post_title; ?></a>
        		</div>
				<div class='wpwaf_forum_list_count'><?php echo $forum->topics_count; ?></div>
				<div class='wpwaf_clear'></div>
			</div>
			
		<?php } ?>        
	</div>	
</div>
