<?php global $home_list_data; ?>
<div class='home_list_item'>
	<div class='list_panel'>
        <?php foreach($home_list_data["records"] as $record){ ?>
		<div class='list_row'><a href=''><?php echo $record['title']; ?></a>
		<?php do_action('wpwaf_home_widgets_controls',$record['type'],$record['ID']); ?></div>
	<?php } ?>
	</div>	
</div>
