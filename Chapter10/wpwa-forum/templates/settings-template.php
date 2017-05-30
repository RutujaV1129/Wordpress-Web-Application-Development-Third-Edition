<?php
    global $template_data_settings;
    extract($template_data_settings);
?>



<div id="wpwaf-settings-panel">
    <h2><?php echo __('Forum Management Application Settings','wpwaf'); ?></h2>
    <form name="wpwaf-settings-frm" id="wpwaf-settings-frm" method="POST">    
        <div id="wpwaf-subscription-setting" class="wpwaf-settings-tab"><?php echo __('Topic Subscription Settings','wpwaf'); ?></div>
        <div class="wpwaf-settings-content">
            <div id="wpwaf-subscription-setting-content" class="wpwaf-settings-tab-content">
                <div class="label"><?php echo __('Topic Subscription Limit','wpwaf'); ?></div>
                <div class="field"><input type='text' id="wpwaf_topic_susbcribe_limit" name="wpwaf[topic_susbcribe_limit]" value="<?php echo $wpwaf_topic_susbcribe_limit; ?>" /></div>
            </div>
        </div>

        <div id="wpwaf-widget-setting" class="wpwaf-settings-tab"><?php echo __('Content Restriction Settings','wpwaf'); ?>
           </div>
        <div class="wpwaf-settings-content">
            <div id="wpwaf-widget-setting-content" class="wpwaf-settings-tab-content">
                <div class="label"><?php echo __('Site Lockdown Status','wpwaf'); ?></div>
                <div class="field"><select id="wpwaf_lockdown_status" name="wpwaf[lockdown_status]" class="" >
                 <option <?php selected('enabled',$wpwaf_lockdown_status); ?>  value="enabled"><?php _e('Enabled','wpwaf'); ?></option>
                 <option <?php selected('disabled',$wpwaf_lockdown_status); ?> value="disabled"><?php _e('Disabled','wpwaf'); ?></option>
            </select>
            </div>
            </div>
            <div id="wpwaf-widget-setting-content" class="wpwaf-settings-tab-content">
                <div class="label"><?php echo __('Individual Topic Restriction Status','wpwaf'); ?></div>
                <div class="field"><select id="wpwaf_single_topic_restrict_status" name="wpwaf[single_topic_restrict_status]" class="" >
                 <option <?php selected('enabled',$wpwaf_single_topic_restrict_status); ?> value="enabled"><?php _e('Enabled','wpwaf'); ?></option>
                <option <?php selected('disabled',$wpwaf_single_topic_restrict_status); ?> value="disabled"><?php _e('Disabled','wpwaf'); ?></option>
            </select>
            </div>
            </div>
        </div>
        
        <div >
            <div class="wpwaf-settings-tab-content">
                <div class="label">&nbsp;</div>
                <div class="field"><input type="submit" id="wpwaf_settings_submit" name="wpwaf_settings_submit" class="" value="<?php echo __('Save Settings','wpwaf'); ?>" /></div>
            </div>
        </div>
    </form>
</div>