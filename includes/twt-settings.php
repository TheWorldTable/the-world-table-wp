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
  <form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post" name="settings_form" id="settings_form">
    <div class="twt-form-row">
      <label>SiteId: <input type="text" name="site-id" value="" class="twt-site-id" placeholder="XXXXXXXXXXXXXXXX_XXXXXXX" 
        <?= empty(get_option('site_id')) ? '' : (' value="' . get_option('site_id') . '" ') ?>
      /></label>
    </div>
    <div class="twt-form-row">
      Customize other setting at <a href="https://app.worldtable.co/#!/sites">The Workd Table</a> web site. 
    </div>
    <div class="twt-action-row">
      <input type="submit" class="button-primary" value = "Save Changes" name = "save-site-id" />
    </div>
  </form>
</div>
