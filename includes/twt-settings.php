<div class="wrap">
  <h2>The World Table Settings</h2>
    <form action="options.php" method="post">
      <?php settings_fields('twt-plugin-settings'); ?>
      <?php do_settings_sections('plugin'); ?>
      <input name="Submit" type="submit" value="<?php esc_attr_e('Save Settings'); ?>" />
   </form>
</div>
