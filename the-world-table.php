<?php
/*
  Plugin Name: The World Table - Making the conversation better
  Plugin URI: https://worldtable.co
  Description: The World Table integration plugin
  Version: 0.2
  Author: Mike Scalora
  Author URI: https://worldtable.co
  License: GPLv2+
  Text Domain: the-world-table
*/

$TWT_DEBUG = false;

class The_World_Table {

  function __construct() {
    add_action('admin_menu', array($this, 'add_menu'));
    add_action('admin_enqueue_scripts', array($this, 'styles'));

    register_activation_hook(__FILE__, array($this, 'install'));
    register_deactivation_hook(__FILE__, array($this, 'uninstall'));

    add_action('wp_head', array($this, 'add_head_elements'));
    add_action('pre_comment_on_post', array($this, 'pre_comment_on_post'));
    add_action('comment_form_before', array($this, 'comment_form_before'));    
  }

  function comment_form_before () {
    echo "<div id='twt-comments' style='outline: 2px dashed red; min-height: 1em;'>My Cool Commenting system here.</div>";
    echo "<style>#comments > :not(#twt-comments) { display: none !important; }</style>";
    echo "<script>setTimeout(function () {var nodes = document.querySelectorAll('#comments > :not(#twt-comments)'); for(var i = nodes.length-1; i >=0; i--) console.log(nodes[i]);}, 100);</script>";
  }

  function add_head_elements() {
    $siteInfo = $this->get_site_info();
    
    if ($TWT_DEBUG) echo "<meta name='twt-debug' contnet='" . htmlentities(var_export($siteInfo)) . "'>";
    
    if ($siteInfo) {
      wp_enqueue_script('twt-the-world-table', "https://${siteInfo[1]}/the-world-table.js", array(), null, true);
      echo '<meta name="twt-site-id" content="' . $siteInfo[0] . '">';
    }
  }

  function get_twt_options() {
    return get_option('twt-plugin-settings');
  }
  
  function get_site_info() {
    // returns array($siteId, $host)
    $options = $this->get_twt_options();
    if (!empty($options['twt-site-id'])) {
      $rawSiteId = trim($options['twt-site-id']);
      $parts = preg_split('/[ |,]+/', $rawSiteId);
      $siteId = $parts[0];
      $host = count($parts) > 1 || empty($parts[1]) ? $parts[1] : 'app.worldtable.co';
      return array($siteId, $host);
    }
    return false;
  }
  
  function add_menu() {
    add_menu_page('The World Table', 'World Table', 'manage_options', 'twt-settings',
        array(__CLASS__, 'page_file_path'), plugins_url('images/logo.png', __FILE__),'2.2.9');

    add_action('admin_init', array($this, 'register_plugin_settings'));
  }

  function register_plugin_settings() {
    register_setting('twt-plugin-settings', 'twt-plugin-settings', array($this, 'plugin_options_validate'));
    add_settings_section('plugin_main', null, array($this, 'twt_settings_form_section_title'), 'plugin');
    add_settings_field('twt-site-id', 'Site Id', array($this, 'site_id_form_emit'), 'plugin', 'plugin_main');
  }

  function twt_settings_form_section_title() {
    echo '<div class="twt-setting-section-title">Get <i>your</i> Site Id on the <a href="https://app.worldtable.co/#!/sites">World Table website profile page</a> </p>';
  }

  function site_id_form_emit() {
    $siteInfo = $this->get_site_info();
    $info = $siteInfo[0] . ' ' . $siteInfo[1];
    echo "<input id='twt-site-id' name='twt-plugin-settings[twt-site-id]' size='40' type='text' value='$info' />";
  }

  function plugin_options_validate($input) {
    $newinput['twt-site-id'] = trim($input['twt-site-id']);
    if(!preg_match('/^[a-z0-9]{16}_[a-z0-9]{1,7}([ |,]+[-a-z0-9._]+)?$/i', $newinput['twt-site-id'])) {
      $newinput['twt-site-id'] = '';
    }
    return $newinput;
  }

  static function page_file_path() {
    $screen = get_current_screen();
    include(dirname(__FILE__) . '/includes/twt-settings.php');
  }

  public function styles($page) {
    wp_enqueue_style('wp-analytify-style', plugins_url('css/the-world-table.css', __FILE__));
  }

  function install() {
  }

  function uninstall() {
  }
  
  function pre_comment_on_post($comment_post_ID) {
    if ($this->can_replace_commenting_system()) {
      wp_die(__('Sorry, the built-in commenting is disabled because The World Table is in use.') );
    } else {
      echo "<h4>Configure The World Table!</h4>\n";
    }
    return $comment_post_ID;
  }
  function can_replace_commenting_system() {
    return $this->get_site_info()!==false;
  }
}

new The_World_Table();

class TWT_Top_Comments extends WP_Widget {

  function TWT_Top_Comments() {
    parent::WP_Widget(false, $name = __('World Table Top Comments', 'wp_widget_plugin'));
  }

  function widget($args, $instance) {
    extract( $args );

    $options = get_option('twt-plugin-settings');

    echo $before_widget;
    if (empty($options['twt-site-id'])) {
      echo '<div>Enter the <i>Site Id</i> in the <b>The World Table</b> section of the <a href="' . get_admin_url() . '?page=twt-settings">WordPress Admin Dashboard</a></div>';
    } else {
      echo '<div id="twt-top-comments-list">' . $options['twt-site-id'] . '</div>';
    }
    echo $after_widget;

    // these are the widget options
    $title = apply_filters('widget_title', $instance['title']);
    $text = $instance['text'];
    $textarea = $instance['textarea'];
    echo $before_widget;
    // Display the widget
    echo '<div class="widget-text wp_widget_plugin_box">';

    // Check if title is set
    if ( $title ) {
      echo $before_title . $title . $after_title;
    }

    // Check if text is set
    if( $text ) {
      echo '<p class="wp_widget_plugin_text">'.$text.'</p>';
    }
    // Check if textarea is set
    if( $textarea ) {
      echo '<p class="wp_widget_plugin_textarea">'.$textarea.'</p>';
    }
    echo '</div>';
    echo $after_widget;
    /* ... */
  }
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("TWT_Top_Comments");'));
