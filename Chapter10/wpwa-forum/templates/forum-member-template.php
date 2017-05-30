<?php 
    global $wpwaf_topics_data;
    get_header(); 
?>

<div class='main_panel'>
    <div class='forum_member_profile_panel'>
        <h2><?php echo __('Personal Information','wpwa'); ?></h2>
        <div class='field_label'><?php echo __('Full Name','wpwa'); ?></div>
        <div class='field_value'><?php echo esc_html($wpwaf_topics_data['display_name']); ?></div>
    </div>

    <div id='forum_member_topics'>
        <h2><?php echo __('Topics','wpwa'); ?></h2>
        <div id="msg_container"></div>
        <?php if ($wpwaf_topics_data['forum_member_status']) {
 ?>
            <input type='button' id="wpwaf_add_topic" value="<?php echo __('Add New','wpwa'); ?>" />
<?php } ?>
        <div id='wpwaf_topic_add_panel' style='display:none' >
            <div class='field_row'>
                <div class='field_label'><?php echo __('Topic Title','wpwaf'); ?></div>
                <div class='field_value'><input type='text' id='wpwaf_topic_title' /></div>
            </div>
            <div class='field_row'>
                <div class='field_label'><?php echo __('Topic Content','wpwaf'); ?></div>
                <div class='field_value'><textarea id='wpwaf_topic_content' ></textarea></div>
            </div>
            <div class='field_row'>
                <div class='field_label'><?php echo __('Forum','wpwaf'); ?></div>
                <div class='field_value'>
                    <select id="wpwaf_topic_forum">
                        <option value="0"><?php echo __('Select','wpwaf'); ?></option>
                        <?php foreach ($wpwaf_topics_data['forum_list'] as $forum_id => $forum) { ?>
                            <option value="<?php echo $forum['ID']; ?>"><?php echo $forum['forum_title']; ?></option>
                        <?php } ?>
                        
                    </select></div>
            </div>
            <div class='field_row'>
                <div class='field_label'><input type='hidden' id='wpwaf_topic_forum_member' value='<?php echo $wpwaf_topics_data['forum_member_id']; ?>' /></div>
                <div class='field_value'><input type='button' id='wpwaf_topic_create' value='<?php echo __('Save','wpwaf'); ?>' /></div>
            </div>
        </div>
        <div >
            <table id='wpwaf_list_topics'>

            </table>
        </div>
    </div>
</div>


<script type="text/template" id="topic-list-template">

    <% _.each(topics, function(topic) { %>
    <tr class="topic_item">
        <td><%= topic.topic_title %></td>
        <td><%= topic.topic_content %></td>
        <td><%= topic.topic_status %></td>
    </tr>
    <% }); %>

</script>

<script type="text/template" id="topic-list-header">
    <tr >
        <th><?php echo __('Topic Title','wpwaf'); ?></th>
        <th><?php echo __('Topic Description','wpwaf'); ?></th>        
        <th><?php echo __('Sticky Status','wpwaf'); ?></th>
    </tr>

</script>



<?php get_footer(); ?>
