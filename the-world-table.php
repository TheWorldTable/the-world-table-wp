<?php
/*
  Plugin Name: The World Table - Making the conversation better
  Plugin URI: https://worldtable.co
  Description: The WOrld Table integration plugin
  Version: 0.1
  Author: Mike Scalora
  Author URI: https://worldtable.co
  License: GPLv2+
  Text Domain: the-world-table
*/

function register_plugin_settings() {
    register_setting( 'twt-plugin-settings', 'twt-plugin-settings', 'plugin_options_validate' );
    add_settings_section('plugin_main', null, 'twt_settings_form_section_title', 'plugin');
    add_settings_field('twt-site-id', 'Site Id', 'site_id_form_emit', 'plugin', 'plugin_main');
}

function twt_settings_form_section_title() {
  echo '<div class="twt-setting-section-title">Get your World Table Site Id on the <a href="https://app.worldtable.co/#!/sites">profile page</a> </p>';
}

function site_id_form_emit() {
  $options = get_option('twt-plugin-settings');
  echo "<input id='twt-site-id' name='twt-plugin-settings[twt-site-id]' size='40' type='text' value='{$options['twt-site-id']}' />";
}

function plugin_options_validate($input) {
  $newinput['twt-site-id'] = trim($input['twt-site-id']);
  if(!preg_match('/^[a-z0-9]{16}_[a-z0-9]{1,7}$/i', $newinput['twt-site-id'])) {
    $newinput['twt-site-id'] = '';
  }
  return $newinput;
}

class The_World_Table {

    function __construct() {
        add_action( 'admin_menu', array( $this, 'wpa_add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'wpa_styles') );

        register_activation_hook( __FILE__, array( $this, 'wpa_install' ) );
        register_deactivation_hook( __FILE__, array( $this, 'wpa_uninstall' ) );
    }

    function wpa_add_menu() {
        add_menu_page( 'The World Table', 'World Table', 'manage_options', 'twt-settings',
            array(__CLASS__, 'wpa_page_file_path'), plugins_url('images/logo.png', __FILE__),'2.2.9');

        add_action( 'admin_init', 'register_plugin_settings' );
    }

    static function wpa_page_file_path() {
        $screen = get_current_screen();
        include( dirname(__FILE__) . '/includes/twt-settings.php' );
    }

    public function wpa_save_data($site_id) {
        if (get_option('site-id') === false) {
            add_option('site-id', $site_id, null, 'no');
        } else {
            update_option('site-id', $site_id);
        }
        return true;
    }

    public function wpa_styles( $page ) {
        wp_enqueue_style( 'wp-analytify-style', plugins_url('css/the-world-table.css', __FILE__));
    }

    function wpa_install() {
    }

    function wpa_uninstall() {
    }

}

new The_World_Table();
?>
