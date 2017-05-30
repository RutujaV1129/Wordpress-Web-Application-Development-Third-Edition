<?php global $api_data;
      extract($api_data);
?>

<div class="wrap"><form action="" method="post" name="options">
    <h2><?php _e('API Credentials','wpwaf'); ?></h2>
    <table class="form-table" width="100%" cellpadding="10">
        <tbody>
        <tr>
        <td scope="row" align="left">
         <label><?php _e('API Token :','wpwaf'); ?><?php echo $api_token; ?></label>
        </td>
        </tr>
        </tbody>
    </table>
    <input class="button action" type="submit" name="api_settings" value="<?php _e('Update','wpwaf'); ?>" /></form>
</div>