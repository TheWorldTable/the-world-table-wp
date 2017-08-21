<?php
  $twt = new The_World_Table();

  // Save SiteId
  if ( isset( $_POST["save-site-id"]) and isset($_POST["access_code"]) ) {
    if( $twt->wpa_save_data( $_POST["access_code"] )){
        $update_message = '<div id="setting-error-settings_updated" class="updated settings-error below-h2"><p><strong>Access code saved.</strong></p></div>';
    }
  }
?>

<div class="wrap">
  <h2>The World Table Settings</h2>
    <form action="options.php" method="post">
      <?php settings_fields('twt-plugin-settings'); ?>
      <?php do_settings_sections('plugin'); ?>
      <input name="Submit" type="submit" value="<?php esc_attr_e('Save Settings'); ?>" />
   </form>
</div>

<?php
