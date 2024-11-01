<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_shortcode('yakadanda-jobadder-archive', 'yakadanda_jobadder_archive_shortcode');

function yakadanda_jobadder_archive_shortcode($atts) {
  $attr = shortcode_atts( array(
        'foo' => 'something',
        'bar' => 'something else',
    ), $atts );

  $output = yakadanda_jobadder_archive_content($attr);

  return $output;
}

add_shortcode('yakadanda-jobadder-single', 'yakadanda_jobadder_single_shortcode');

function yakadanda_jobadder_single_shortcode($atts) {
  $attr = shortcode_atts( array(
        'foo' => 'something',
        'bar' => 'something else',
    ), $atts );

  $output = yakadanda_jobadder_single_content($attr);

  return $output;
}

add_filter( 'the_title', 'yakadanda_jobadder_the_title_filter', 10, 2 );

function yakadanda_jobadder_the_title_filter( $title ) {
  if ( is_single() && is_singular( 'yakadanda_jobadder' ) && in_the_loop() && $post_id = get_the_ID() ) {
    $pfx_date = get_the_date('d F Y', $post_id);
    $title = "<span>$pfx_date</span><br><strong>$title</strong>";
  }

  return $title;
}

add_filter( 'the_content', 'yakadanda_jobadder_the_content_filter', 20 );

function yakadanda_jobadder_the_content_filter( $content ) {
  if ( is_single() && is_singular( 'yakadanda_jobadder' ) ) {
    $content = yakadanda_jobadder_single_content();
  }

  return $content;
}

function yakadanda_jobadder_archive_content($attr = null) {
  $output = null;
  
  $filter_widget_options = current(get_option('widget_yakadanda_jobadder_filter_widget'));
  
  wp_reset_postdata();
  $args = array(
    'post_type' => 'yakadanda_jobadder',
    'post_status' => 'publish',
    'posts_per_page' => '-1'
  );
  
  $tax_query = array();
  if ( isset($filter_widget_options['location_display']) && ($filter_widget_options['location_display'] == 'show_custom') ) {
    $tax_query[] = array(
      'taxonomy' => 'yakadanda_jobadder_location',
      'field' => 'term_id',
      'terms' => $filter_widget_options['location_custom'],
      'operator' => 'IN'
    );
  }
  
  if ( isset($filter_widget_options['type_display']) && ($filter_widget_options['type_display'] == 'show_custom') ) {
    $tax_query[] = array(
      'taxonomy' => 'yakadanda_jobadder_job_type',
      'field' => 'term_id',
      'terms' => $filter_widget_options['type_custom'],
      'operator' => 'IN'
    );
  }
  
  if ($tax_query) {
    $tax_query['relation'] = 'AND';
    $args = array_merge($args, ['tax_query' => $tax_query]);
  }
  
  $query = new WP_Query( $args );

  if ( $query->have_posts() ) {
    $output .= "<div id='yakadanda-jobadder-jobs' class='job-preview-container'>";
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
  wp_reset_postdata();

  return $output;
}

function yakadanda_jobadder_single_content($attr = null) {
  $post_id = get_the_ID();
  $content = get_the_content();
  $bullet_points = ($bullet_points = get_post_meta($post_id, 'yakadanda_jobadder_textmedium_bullet_points', true)) ? $bullet_points : [];
  $bulletpoints = "<ul>";
  foreach ( $bullet_points as $bulletpoint ) {
    $bulletpoints .= "<li>$bulletpoint</li>";
  }
  $bulletpoints .= "</ul>";
  $url_apply = ($url_apply = get_post_meta($post_id, 'yakadanda_jobadder_url_apply_url', true)) ? $url_apply : "#";
  $apply = "<div class='buttons'><a href='" . $url_apply . "' class='btn btn-primary' target='_blank'>" . __('Apply', 'yakadanda-jobadder') . "</a></div>";

  $output = $content . $bulletpoints . $apply;

  return $output;
}

