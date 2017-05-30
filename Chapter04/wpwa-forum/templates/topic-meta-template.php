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
        <td><input class='widefat' name='wpwaf_files' id='wpwaf_files' type='file' value='' /></td>
    </tr>  
    
</table>
