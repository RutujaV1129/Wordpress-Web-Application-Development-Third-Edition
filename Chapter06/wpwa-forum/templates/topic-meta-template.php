<?php
    global $template_data;
    extract($template_data);    
?>

<input type="hidden" name="topic_meta_nonce" value="<?php echo $topic_meta_nonce; ?>" />

<table class="form-table">    
    <tr>
        <th style=''><label><?php _e('Sticky Status','wpwaf'); ?>*</label></th>
        <td><select class='widefat' name="wpwaf_sticky_status" id="wpwaf_sticky_status">
                <option <?php selected( $topic_sticky_status, '0' ); ?> value='0' ><?php _e('Please Select','wpwaf'); ?></option>
                <option <?php selected( $topic_sticky_status, 'normal' ); ?> value='normal' ><?php _e('Normal','wpwaf'); ?></option> 
                <option <?php selected( $topic_sticky_status, 'super_sticky' ); ?> value='super_sticky' ><?php _e('Super Sticky','wpwaf'); ?></option>      
                <option <?php selected( $topic_sticky_status, 'sticky' ); ?> value='sticky' ><?php _e('Sticky','wpwaf'); ?></option>              
            </select></td>
    </tr>
    <tr>
        <th style=''><label><?php _e('Topic Files','wpwaf'); ?></label></th>
        
        <td><input class='widefat wpwaf_files' type="file" name='wpwaf_files' id='wpwaf_files'  />
            <div class='wpwaf_preview_box' id='project_screens_panel' >
                <?php foreach($topic_docs as $doc){ 
                        if(trim($doc) != '') {
                ?>
                <img class='wpwaf_img_prev' style='float:left;with:75px;height:75px' src='<?php echo WPWA_FILE_UPLOAD_PLUGIN_URL."img/document.png"; ?>' />
                <div class='wpwaf_prev_file_name' style='margin:25px 0;float:left'><?php echo basename($doc); ?></div>
                <input class='wpwaf_img_prev_hidden' type='hidden' name='h_wpwaf_files[]' value='<?php echo $doc; ?>' />
                <div style='clear:both' >&nbsp;</div>
                <?php }} ?> 
            </div>
        </td>
    </tr>  
    
</table>
