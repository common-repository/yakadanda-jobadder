<?php
/**
 * Plugin Name: Yakadanda JobAdder
 * Plugin URI: http://www.yakadanda.com/plugins/yakadanda-jobadder/
 * Description: Display the jobs from JobAdder.
 * Version: 0.0.2
 * Author: Peter Ricci
 * Author URI: http://www.yakadanda.com/
 * Text Domain: yakadanda-jobadder
 * Domain Path: /languages/
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

register_activation_hook(__FILE__, 'yakadanda_jobadder_activate');
function yakadanda_jobadder_activate() {
  yakadanda_jobadder_cpt_init();
  if (!get_option('yakadanda_jobadder_options')) {
    $initial_options = array(
      'passphrase' => str_replace(' ', '', strtolower(md5(microtime().rand()))),
      'tz' => NULL,
      'sender' => NULL,
      'email' => NULL
    );
    add_option('yakadanda_jobadder_options', $initial_options);
  }
  if (!get_option('yakadanda_jobadder_filemtime')) {
    add_option('yakadanda_jobadder_filemtime', NULL);
  }
  if (!wp_next_scheduled('yakadanda_jobadder_thrice_daily_event')) {
    wp_schedule_event(time(), 'thricedaily', 'yakadanda_jobadder_thrice_daily_event');
  }
  flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'yakadanda_jobadder_deactivate');
function yakadanda_jobadder_deactivate() {
  wp_clear_scheduled_hook('yakadanda_jobadder_thrice_daily_event');
}

if(!defined('YAKADANDA_JOBADDER_VER')) define('YAKADANDA_JOBADDER_VER', '0.0.2');
if(!defined('YAKADANDA_JOBADDER_PLUGIN_DIR')) define('YAKADANDA_JOBADDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
if(!defined('YAKADANDA_JOBADDER_PLUGIN_URL')) define('YAKADANDA_JOBADDER_PLUGIN_URL', plugins_url(null, __FILE__));
if(!defined('YAKADANDA_JOBADDER_THEME_DIR')) define('YAKADANDA_JOBADDER_THEME_DIR', get_stylesheet_directory());
if(!defined('YAKADANDA_JOBADDER_THEME_URL')) define('YAKADANDA_JOBADDER_THEME_URL', get_stylesheet_directory_uri());

add_action('plugins_loaded', 'yakadanda_jobadder_load_textdomain');
function yakadanda_jobadder_load_textdomain() {
  load_plugin_textdomain('yakadanda-jobadder', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
}

add_filter('plugin_action_links', 'yakadanda_jobadder_action_links', 10, 2);
function yakadanda_jobadder_action_links($links, $file) {
  static $yakadanda_jobadder;
  
  if (!$yakadanda_jobadder) $yakadanda_jobadder = plugin_basename(__FILE__);
  
  if ($file == $yakadanda_jobadder) {
    $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/edit.php?post_type=yakadanda_jobadder&page=yakadanda_jobadder_settings">' . __('Settings', 'yakadanda-jobadder') . '</a>';
    array_unshift($links, $settings_link);
  }
  
  return $links;
}

add_action('init', 'yakadanda_jobadder_register');
function yakadanda_jobadder_register() {
  $yakadanda_jobadder_style_src = ( file_exists(YAKADANDA_JOBADDER_THEME_DIR . '/css/yakadanda-jobadder.css' ) ) ? YAKADANDA_JOBADDER_THEME_URL : YAKADANDA_JOBADDER_PLUGIN_URL;
  wp_register_style('yakadanda-jobadder-style', $yakadanda_jobadder_style_src . '/css/yakadanda-jobadder.css', false, YAKADANDA_JOBADDER_VER, 'all');
  
  wp_register_script('yakadanda-jobadder-script', YAKADANDA_JOBADDER_PLUGIN_URL . '/js/main.min.js', ['jquery'], YAKADANDA_JOBADDER_VER, true );
}

add_action('admin_enqueue_scripts', 'yakadanda_jobadder_admin_enqueue_scripts');
function yakadanda_jobadder_admin_enqueue_scripts() {
  yakadanda_jobadder_wp_enqueue_scripts();
}

add_action('wp_enqueue_scripts', 'yakadanda_jobadder_wp_enqueue_scripts');
function yakadanda_jobadder_wp_enqueue_scripts() {
  wp_enqueue_style('yakadanda-jobadder-style');
  
  wp_enqueue_script('yakadanda-jobadder-script');

  wp_localize_script('yakadanda-jobadder-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}

require_once(dirname( __FILE__ ) . '/lib/includes.php');
