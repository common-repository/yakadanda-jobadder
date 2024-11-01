<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('wp_ajax_yakadanda_jobadder_filter_action', 'yakadanda_jobadder_filter_callback');

add_action('wp_ajax_nopriv_yakadanda_jobadder_filter_action', 'yakadanda_jobadder_filter_callback');

function yakadanda_jobadder_filter_callback() {
  $locations = isset($_POST['locations']) ? $_POST['locations'] : array();
  $job_types = isset($_POST['job_types']) ? $_POST['job_types'] : array();
  
  $clean_locations = array();
  if ($locations) {
    foreach ($locations as $location) {
      if (intval($location)) {
        $clean_locations[] = $location;
      }
    }
  }
  $clean_job_types = array();
  if ($job_types) {
    foreach ($job_types as $job_type) {
      if (intval($job_type)) {
        $clean_job_types[] = $job_type;
      }
    }
  }

  if ($clean_locations || $clean_job_types) {
    $output = yakadanda_jobadder_filter_content($clean_locations, $clean_job_types);
  } else {
    $output = yakadanda_jobadder_archive_content();
  }

  echo $output;

  wp_die();
}

function yakadanda_jobadder_filter_content($locations = array(), $job_types = array()) {
  $output = "<div id='yakadanda-jobadder-jobs' class='job-preview-container'>" . __('No jobs are available.', 'yakadanda-jobadder') . "</div>";

  $tax_query = array();
  if ($locations) {
    array_push($tax_query, [
        'taxonomy' => 'yakadanda_jobadder_location',
        'field'    => 'term_id',
        'terms'    => $locations,
        'operator' => 'IN'
      ]);
  }
  if ($job_types) {
    array_push($tax_query, [
        'taxonomy' => 'yakadanda_jobadder_job_type',
        'field'    => 'term_id',
        'terms'    => $job_types,
        'operator' => 'IN'
      ]);
  }
  if ($locations && $job_types) {
    $tax_query = array_merge(['relation' => 'AND'], $tax_query);
  }

  $args = array(
      'post_type' => 'yakadanda_jobadder',
      'post_status' => 'publish',
      'posts_per_page' => '-1',
      'tax_query' => $tax_query,
    );

  $query = new WP_Query( $args );

  if ( $query->have_posts() ) {
    $output = "<div id='yakadanda-jobadder-jobs' class='job-preview-container'>";
    while ( $query->have_posts() ) {
      $query->the_post();
      
      $post_id = get_the_ID();
      $post_url = get_permalink($post_id);
      $locations = null;
      foreach ( wp_get_post_terms($post_id, 'yakadanda_jobadder_location') as $location ) {
        $locations .= ', ' . $location->name;
      }
      $locations = ltrim($locations, ', ');
      $pfx_date = get_the_date('d F Y', $post_id);
      $title = get_the_title();
      $summary = get_post_meta($post_id, 'yakadanda_jobadder_textareasmall_summary', true);
      
      $output .= "<a href='$post_url' class='FilterItem JobPreviewParent'>";
      $output .= "<article class='job-preview JobPreview'>";
      $output .= "<h5>$locations</h5>";
      $output .= "<span class='date'>$pfx_date</span>";
      $output .= "<h3 class='Title'>$title</h3>";
      $output .= "<p>$summary</p>";
      $output .= "</article>";
      $output .= "</a>";
    }
    $output .= "</div>";
  }

  return $output;
}

/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function yakadanda_jobadder_add_dashboard_widgets() {

  wp_add_dashboard_widget(
      'yakadanda_jobadder_dashboard_widget', // Widget slug.
      'Yakadanda JobAdder', // Title.
      'yakadanda_jobadder_dashboard_widget_function' // Display function.
  );
}

add_action('wp_dashboard_setup', 'yakadanda_jobadder_add_dashboard_widgets');

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function yakadanda_jobadder_dashboard_widget_function() {
  // Display whatever it is you want to show.
  echo "Last update: " . yakadanda_jobadder_get_xml_file_datetime();
}

function yakadanda_jobadder_get_xml_file_datetime($subject = NULL) {
  global $yakadanda_jobadder_options;

  $default_location = (get_option('timezone_string')) ? get_option('timezone_string') : date_default_timezone_get();
  $location = ($yakadanda_jobadder_options['tz']) ? $yakadanda_jobadder_options['tz'] : $default_location;
  
  // set timezone
  $tz = new DateTimeZone($location);

  $date = new DateTime( date( 'c', filemtime(YAKADANDA_JOBADDER_PLUGIN_DIR . '/xml/jobadder_data.xml') ) );
  $date->setTimezone($tz);

  $output = ($subject) ? $date->format('Y/m/d h:i:s A T') : $date->format('l, F d Y h:i:s A T');

  return $output;
}

/**
 * Schedule an thrice daily event
 */
add_action('yakadanda_jobadder_thrice_daily_event', 'yakadanda_jobadder_do_this_thrice_daily');

function yakadanda_jobadder_do_this_thrice_daily() {
  yakadanda_jobadder_event(false);
}

/**
 * Plugin API/Filter Reference/cron schedules
 * https://codex.wordpress.org/Plugin_API/Filter_Reference/cron_schedules
 */
function yakadanda_jobadder_add_thricedaily($schedules) {
  // add a 'thricedaily' schedule to the existing set
  $schedules['thricedaily'] = array(
    'interval' => 28800,
    'display' => __('Thrice Daily')
  );
  return $schedules;
}

add_filter('cron_schedules', 'yakadanda_jobadder_add_thricedaily');

/**
 * Global variable
 */
global $yakadanda_jobadder_options;
$yakadanda_jobadder_options = yakadanda_jobadder_options();

function yakadanda_jobadder_options() {
  $output = array();
  
  $default = array(
    'passphrase' => NULL,
    'tz' => NULL,
    'sender' => NULL,
    'email' => NULL
  );
  
  $options = wp_parse_args( get_option('yakadanda_jobadder_options'), $default );
  
  return array_merge((array) $output, (array) $options);
}

/**
 *  Register settings page
 */
add_action('admin_menu', 'yakadanda_jobadder_admin_menu');
function yakadanda_jobadder_admin_menu() {
  $settings_page = add_submenu_page('edit.php?post_type=yakadanda_jobadder', __('Settings', 'yakadanda-jobadder'), __('Settings', 'yakadanda-jobadder'), 'manage_options', 'yakadanda_jobadder_settings', 'yakadanda_jobadder_page_settings');
  add_action('load-' . $settings_page, 'yakadanda_jobadder_page_settings_help_tab');
}

function yakadanda_jobadder_page_settings() {
  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'yakadanda-google-hangout-events'));
  }
  
  global $yakadanda_jobadder_options;
  
  $data = $yakadanda_jobadder_options;
  
  $admin_email = get_option('admin_email');
  
  $passphrase = isset($data['passphrase']) ? $data['passphrase'] : null;
  $tz = isset($data['tz']) ? $data['tz'] : NULL;
  $email = isset($data['email']) ? $data['email'] : NULL;
  $sender = isset($data['sender']) ? $data['sender'] : NULL;

  $file_exists = ( file_exists(WP_PLUGIN_DIR . "/jobadder-catcher-$passphrase.php") ) ? '<strong style="color: green;">' . __('file found.', 'yakadanda-jobadder') . '</strong>' : '<strong style="color: red;">' . __('file not found.', 'yakadanda-jobadder') . '</strong>';
  $is_writable = ( is_writable( YAKADANDA_JOBADDER_PLUGIN_DIR . "/xml/jobadder_data.xml" ) ) ? '<strong style="color: green;">' . __('The file is writable.', 'yakadanda-jobadder') . '</strong>' : '<strong style="color: red;">' . __('The file is not writable.', 'yakadanda-jobadder') . '</strong>';
  $message = null;
  
  /* postData */
  if ( isset($_POST['update_settings']) ) {
    
    if (!wp_verify_nonce($_POST['yakadanda_jobadder_settings_nonce'], md5(YAKADANDA_JOBADDER_PLUGIN_DIR))) {
      return;
    }
    
    $options = array(
      'passphrase' => str_replace(' ', '', strtolower($_POST['passphrase'])),
      'tz' => $_POST['tz'],
      'sender' => sanitize_email( $_POST['sender'] ),
      'email' => sanitize_email( $_POST['email'] )
    );
    
    update_option('yakadanda_jobadder_options', $options);
    $data = get_option('yakadanda_jobadder_options');
    
    $passphrase = isset($data['passphrase']) ? $data['passphrase'] : NULL;
    $tz = isset($data['tz']) ? $data['tz'] : NULL;
    $email = isset($data['email']) ? $data['email'] : NULL;
    $sender = isset($data['sender']) ? $data['sender'] : NULL;
    $file_exists = ( file_exists(WP_PLUGIN_DIR . "/jobadder-catcher-$passphrase.php") ) ? '<strong style="color: green;">' . __('file found.', 'yakadanda-jobadder') . '</strong>' : '<strong style="color: red;">' . __('file not found.', 'yakadanda-jobadder') . '</strong>';
    $is_writable = ( is_writable( YAKADANDA_JOBADDER_PLUGIN_DIR . "/xml/jobadder_data.xml" ) ) ? '<strong style="color: green;">' . __('The file is writable.', 'yakadanda-jobadder') . '</strong>' : '<strong style="color: red;">' . __('The file is not writable.', 'yakadanda-jobadder') . '</strong>';
    $message = array('class' => 'updated', 'msg' => __('Settings updated.', 'yakadanda-jobadder'));
  }

  include(YAKADANDA_JOBADDER_PLUGIN_DIR . '/lib/page-settings.php');
}

function yakadanda_jobadder_page_settings_help_tab() {
  $screen = get_current_screen();
  
  $screen->add_help_tab(array(
    'id' => 'yakadanda-jobadder-page-settings-help-tab-section-setup',
    'title' => __('Documentation', 'yakadanda-jobadder'),
    'content' => yakadanda_jobadder_page_settings_help_tab_section_documentation()
  ));
}

function yakadanda_jobadder_page_settings_help_tab_section_documentation() {
  $output = '<h1>' . __('Setup', 'yakadanda-jobadder') . '</h1>';
  $output .= '<ul>';
  $output .= '<li></li>';
  $output .= '</ul>';
  
  return $output;
}
add_action('wp_ajax_yakadanda_jobadder_check_catcher_file_action', 'yakadanda_jobadder_check_catcher_file_callback');

add_action('wp_ajax_nopriv_yakadanda_jobadder_check_catcher_file_action', 'yakadanda_jobadder_check_catcher_file_callback');
function yakadanda_jobadder_check_catcher_file_callback() {
  $data = get_option('yakadanda_jobadder_options');  
  $passphrase = isset($data['passphrase']) ? $data['passphrase'] : null;
  $file_exists = ( file_exists(WP_PLUGIN_DIR . "/jobadder-catcher-$passphrase.php") ) ? '<strong style="color: green;">' . __('file found.', 'yakadanda-jobadder') . '</strong>' : '<strong style="color: red;">' . __('file not found.', 'yakadanda-jobadder') . '</strong>';
  
  $is_writable = ( is_writable( YAKADANDA_JOBADDER_PLUGIN_DIR . "/xml/jobadder_data.xml" ) ) ? '<strong style="color: green;">' . __('The file is writable.', 'yakadanda-jobadder') . '</strong>' : '<strong style="color: red;">' . __('The file is not writable.', 'yakadanda-jobadder') . '</strong>';
  
  $response = array(
    'file_exists' => $file_exists,
    'is_writable' => $is_writable
  );
  
  echo json_encode($response);
  wp_die();
}

/**
 * Timezones list with GMT offset
 *
 * @return array
 */
function yakadanda_jobadder_tz_list() {
  $zones_array = array();
  $timestamp = time();
  foreach(timezone_identifiers_list() as $key => $zone) {
    date_default_timezone_set($zone);
    $zones_array[$key]['zone'] = $zone;
    $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
  }
  return $zones_array;
}

function yakadanda_jobadder_get_domain($input) {
  // in case scheme relative URI is passed, e.g., //www.google.com/
  $input = trim($input, '/');

  // If scheme not included, prepend it
  if (!preg_match('#^http(s)?://#', $input)) {
      $input = 'http://' . $input;
  }

  $urlParts = parse_url($input);

  // remove www
  $domain = preg_replace('/^www\./', '', $urlParts['host']);

  return $domain;
}

function yakadanda_jobadder_send_notification() {
  global $yakadanda_jobadder_options;
  
  if ($email = $yakadanda_jobadder_options['email']) {
    $to = $email;
    $sender = ($yakadanda_jobadder_options['sender']) ? $yakadanda_jobadder_options['sender'] : get_option('admin_email');
    $subject = '[' . get_bloginfo('name') . '] - ' . yakadanda_jobadder_get_xml_file_datetime(true) . ' - XML file notification';
    $body = "Last update: " . yakadanda_jobadder_get_xml_file_datetime();
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . get_bloginfo('name') . ' &#60;' . $sender . '&#62;' . "\r\n";
    $attachments = array( YAKADANDA_JOBADDER_PLUGIN_DIR . '/xml/jobadder_data.xml' );

    $filemtime = filemtime(YAKADANDA_JOBADDER_PLUGIN_DIR . '/xml/jobadder_data.xml');
    if ($filemtime != get_option('yakadanda_jobadder_filemtime') ) {
      update_option('yakadanda_jobadder_filemtime', $filemtime);
      wp_mail( $to, $subject, $body, $headers, $attachments );
    }
  }
}

/*
 * RSS
 */
add_filter('the_excerpt_rss', 'yakadanda_jobadder_rss');
add_filter('the_content', 'yakadanda_jobadder_rss');
function yakadanda_jobadder_rss($content) {
  global $wp_query;
  
  if ( is_feed() ) {
    $this_post_id = $wp_query->post->ID;
    if ( get_post_type($this_post_id) == 'yakadanda_jobadder' ) {
      $job_post = get_post($this_post_id);
      
      $apply_url = get_post_meta($this_post_id, 'yakadanda_jobadder_url_apply_url', true);
      $job_update = ($dateupdated = get_post_meta($this_post_id, 'yakadanda_jobadder_textmedium_dateupdated', true)) ? $dateupdated : $job_post->post_date;
      
      $job_content = sprintf( '<p>%s</p>', date('d F Y', strtotime($job_update)) );
      $job_content .= $job_post->post_content;
      $job_content .= sprintf('<p><a target="_blank" href="%s">Apply</a></p>', $apply_url);
      
      $content = $job_content;
    }
  }

  return $content;
}
