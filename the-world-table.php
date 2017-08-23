<?php
/*
  Plugin Name: The World Table - Making the conversation better
  Plugin URI: https://worldtable.co
  Description: The World Table integration plugin
  Version: 0.5
  Author: The World Table Team
  Author URI: https://worldtable.co
  License: GPLv2+
  Text Domain: the-world-table
*/

define('TWT_DEBUG', false);
define('TWT_SETTINGS_ID', 'twt-plugin-settings');

class The_World_Table_Plugin {
  
  function __construct () {
    add_action('admin_menu', array($this, 'add_menu'));
    add_action('admin_enqueue_scripts', array($this, 'admin_styles_and_scripts'));
    add_action('in_admin_footer', array($this, 'adorn_comment_admin'));

    register_activation_hook(__FILE__, array($this, 'install'));
    register_deactivation_hook(__FILE__, array($this, 'uninstall'));

    add_action('wp_head', array($this, 'add_head_elements'));
    add_action('pre_comment_on_post', array($this, 'pre_comment_on_post'));
    add_action('comment_form_before', array($this, 'comment_form_before'));
    
    add_action('widgets_init', create_function('', 'return register_widget("TWT_Top_Comments");'));
    add_action('widgets_init', create_function('', 'return register_widget("TWT_Comments");'));

    add_action( 'the_content', array($this, 'each_post'));
    add_action( 'loop_end', array($this, 'add_counts'));

    add_action( 'init', array($this, 'add_taxonomies_to_pages') );    
    $this->blog_id = get_current_blog_id();
    $this->details = function_exists('get_blog_details') ? get_blog_details() : false;
  }

  function add_taxonomies_to_pages() {
    register_taxonomy_for_object_type( 'post_tag', 'page' );
    //register_taxonomy_for_object_type( 'category', 'page' );
  } 

  function adorn_comment_admin() {
    $content = <<<'TWTCOMSCRIPT'
      <style>
        .wp-admin #wpbody-content {
          position: relative;
        }
        .wp-admin .twt-com-admin-mask {
          position: absolute;
          background: white;
          opacity: .5;
          top: 0;
          bottom: 0;
          left: 0;
          right: 0;
        }
        .twt-com-admin-msg h2 b {
          color: red;
        }
        .twt-com-admin-msg {
          margin-bottom: 2em;
        }
      </style>
      <script>
        (function ($) {
          var msg,
              comForm = jQuery('#comments-form');
          if (comForm.length) {
            jQuery('<div class="twt-com-admin-mask">').appendTo('#wpbody-content');
            var msg = '<h2><b>Note:</b> The World Table Commenting System is currently installed.</h2>';
            msg += '<p>For slightly better performance, you can disable the WordPress built-in commenting system ' +
              'on the Settings / Dicussion section by unchecking the <i>"Allow people to post comments on new articles"<i>. ' +
              'Existing comments will be preserved in the database and will reappear if you revert back to using ' +
              'the built-in commenting system by rechecking the option and deactivating the ' +
              'The World Table plugin.</p>';
            jQuery('<div class="twt-com-admin-msg"><div>' + msg + '</div><div>' +
                '<a href="admin.php?page=twt-settings"><button class="twt-admin-link" type="button">The World Table Admin Page</button></a> ' +
                '<button class="twt-admin-legacy" type="button">Access Existing WP Native Comments</button></div></div>')
                .prependTo('#wpbody');
            jQuery('.twt-admin-legacy').on('click', function () {
              jQuery('.twt-com-admin-mask, .twt-com-admin-msg button').remove();
            });
          }
        })(jQuery);
      </script>
TWTCOMSCRIPT;

    echo <<<'TWTADMINSCRIPT'
      <style>
        .wp-admin .form-table {
          width: auto;
        }
        .wp-admin .form-table th,
        .wp-admin .form-table td {
          width: auto;
        }
        .wp-admin .form-table th:after {
          content: ":";
        }
      </style>
      <script>
        (function () {
          function closest(el, selector) {
            var matchesFn;
            ['matches','webkitMatchesSelector','mozMatchesSelector','msMatchesSelector','oMatchesSelector'].some(function(fn) {
              if (typeof document.body[fn] == 'function') {
                matchesFn = fn;
                return true;
              }
              return false;
            })
            var parent;
            while (el) {
              parent = el.parentElement;
              if (parent && parent[matchesFn](selector)) {
                return parent;
              }
              el = parent;
            }
            return null;
          }
          var timer = setTimeout(function () {
            console.log('adding validation!');
            var submit = 0;
            var input = document.querySelector('[name^="twt-plugin-settings[twt-site-id]"]');
            if (input) {
              input.setAttribute('pattern','[a-f0-9]{16}_[a-f0-9]{1,7}([ ,|][-.a-fA-F0-9]+)?');
            }
          },50);
        })();
      </script>
TWTADMINSCRIPT;

    echo $content;
  }

  function each_post ($it) {
    $post = get_post();
    if ($post->post_type === 'post') {
      $post_id = $post->ID;
      $post_permalink = get_permalink();
      $articleId = $this->get_article_id();
      // this meta tab is used on the client to add articleIds to comment count tags inserted
      echo "<meta class='twt-post-info' name='$articleId' content='$post_permalink'/>";
    }
    return $it;
  }
  
  function add_counts () {
    $post = get_post();
    if ($post->post_type === 'post' || has_tag('commentable-page')) {
      echo <<<'TWTSCRIPT'
      <script>
      (function () {
        function closest(el, selector) {
          var matchesFn;
          ['matches','webkitMatchesSelector','mozMatchesSelector','msMatchesSelector','oMatchesSelector'].some(function(fn) {
            if (typeof document.body[fn] == 'function') {
              matchesFn = fn;
              return true;
            }
            return false;
          })
          var parent;
          while (el) {
            parent = el.parentElement;
            if (parent && parent[matchesFn](selector)) {
              return parent;
            }
            el = parent;
          }
          return null;
        }    
        var nodes = document.querySelectorAll('.twt-post-info');
        for (var i = 0; i < nodes.length; i++) {
          var node = nodes[i],
            article = closest(node, 'article'),
            articleId = node.getAttribute('name'),
            permalink = node.getAttribute('content'),
            header = article.querySelector('.entry-meta') || article.querySelector('header'),
            count = header.querySelector('.twt-count-container');
          if (header && !count) {
            var link = 
            header.insertAdjacentHTML('afterbegin', '<div class="twt-count-container"><a href="' + permalink + '#twtcomments" style="display: none;"><span twt-comment-count twt-article-id="' + articleId + '">0</span> comments</a>')
          }
        }
      })();
      </script>
TWTSCRIPT;
    }
  }
  
  function get_article_id () {
    $post = get_post();
    if ($this->details) {
      $details = $this->details;
      $articleId = "wp-network-site-" . $details->site_id . "-blog-" . $details->blog_id . '-' . $post->post_type . '-' . $post->ID;
      return $articleId;
    } else {
      $blog_id = get_current_blog_id();
      $articleId = "wp-blog-" . $blog_id . '-' . $post->post_type . '-' . $post->ID;
      return $articleId;
    }
  }

  function comment_form_before () {
    echo "<style>#comments { display: none !important; }</style>";
    return;
  }

  function add_head_elements() {
    $siteInfo = $this->get_site_info();
    if ($siteInfo) {
      wp_enqueue_script('twt-the-world-table', "https://${siteInfo[1]}/the-world-table.js", array(), null, true);
      wp_enqueue_style('twt-the-world-table-css', plugin_dir_url( __FILE__ ) . 'css/the-world-table.css');
      echo "<meta name='twt-site-id' content='${siteInfo[0]}'>";
    }
  }

  function get_twt_options() {
    return get_option(TWT_SETTINGS_ID);
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
    register_setting(TWT_SETTINGS_ID, TWT_SETTINGS_ID, array($this, 'plugin_options_validate'));
    add_settings_section('plugin_main', null, array($this, 'twt_settings_form_section_title'), 'plugin');
    add_settings_field('twt-site-id', 'Your Site Id', array($this, 'site_id_form_emit'), 'plugin', 'plugin_main');
  }

  function admin_footer () {
  }

  function twt_settings_form_section_title() {
    $info = $this->get_site_info();
    if (!$info) {
      echo '<div class="twt-get-site-id-msg">Get <i>your</i> Site Id on the <a href="https://app.worldtable.co/#!/sites">World Table website profile page</a></div>';
    }
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

  public function admin_styles_and_scripts($page) {
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

class TWT_Top_Comments extends WP_Widget {

  function TWT_Top_Comments() {
    parent::WP_Widget('twt-top-comments-list', $name = 'World Table Top Comments', array('description' => 'The <b>list</b> of the most recent and best comments across the site.'));
  }

  function widget($args, $instance) {
    extract( $args );

    $options = get_option(TWT_SETTINGS_ID);

    echo $before_widget;
    if (empty($options['twt-site-id'])) {
      echo '<div>Enter the <i>Site Id</i> in the <b>The World Table</b> section of the <a href="' . get_admin_url() . '?page=twt-settings">WordPress Admin Dashboard</a></div>';
    } else {
      echo '<div id="twt-top-comments-list"></div>';
    }
    echo $after_widget;
  }
}

class TWT_Comments extends WP_Widget {

  function TWT_Comments() {
    parent::WP_Widget('twt-comments', $name = 'World Table Comment Area', array('description' => "The page's comment thread and commenting area."));
  }

  function widget($args, $instance) {
    $post = get_post(); 
    if (is_singular() && ($post->post_type === 'post' || has_tag('commentable-pages'))) {
      extract( $args );

      $options = get_option(TWT_SETTINGS_ID);

      echo $before_widget;
      if (empty($options['twt-site-id'])) {
        echo '<div>Enter the <i>Site Id</i> in the <b>The World Table</b> section of the <a href="' . get_admin_url() . '?page=twt-settings">WordPress Admin Dashboard</a></div>';
      } else {
        echo '<div id="twt-comments"></div>';
      }
      echo $after_widget;
    }
  }
}

new The_World_Table_Plugin();
