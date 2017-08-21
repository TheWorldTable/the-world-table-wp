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
    }

    static function wpa_page_file_path() {
        $screen = get_current_screen();
        include( dirname(__FILE__) . '/includes/twt-settings.php' );
    }

    public function wpa_save_data( $site_id ) {
        update_option( 'site_id', $site_id );
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
