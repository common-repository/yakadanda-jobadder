<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('init', 'yakadanda_jobadder_cpt_init');

function yakadanda_jobadder_cpt_init() {
  $labels = array(
    'name' => _x('Jobs', 'post type general name', 'yakadanda-jobadder'),
    'singular_name' => _x('Job', 'post type singular name', 'yakadanda-jobadder'),
    'menu_name' => _x('JobAdder', 'admin menu', 'yakadanda-jobadder'),
    'name_admin_bar' => _x('Job', 'add new on admin bar', 'yakadanda-jobadder'),
    'add_new' => _x('Add New', 'job', 'yakadanda-jobadder'),
    'add_new_item' => __('Add New Job', 'yakadanda-jobadder'),
    'new_item' => __('New Job', 'yakadanda-jobadder'),
    'edit_item' => __('Edit Job', 'yakadanda-jobadder'),
    'view_item' => __('View Job', 'yakadanda-jobadder'),
    'all_items' => __('All Jobs', 'yakadanda-jobadder'),
    'search_items' => __('Search Jobs', 'yakadanda-jobadder'),
    'parent_item_colon' => __('Parent Jobs:', 'yakadanda-jobadder'),
    'not_found' => __('No jobs found.', 'yakadanda-jobadder'),
    'not_found_in_trash' => __('No jobs found in Trash.', 'yakadanda-jobadder')
  );

  $args = array(
    'labels' => $labels,
    'description' => __('Description.', 'yakadanda-jobadder'),
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'rewrite' => array('slug' => 'job'),
    'capability_type' => 'post', //'capability_type' => 'job',
    /*'capabilities' => array(
      'edit_post' => 'edit_job',
      'delete_post' => 'delete_job',
      'read_post' => 'read_job',
      'edit_posts' => 'edit_jobs',
      'edit_others_posts' => 'edit_others_jobs',
      'publish_posts' => 'publish_jobs',
      'read_private_posts' => 'read_private_jobs',
      'create_posts' => 'edit_jobs',
      'delete_posts' => 'delete_jobs',
      'delete_others_posts' => 'delete_others_jobs',
    ),*/
    'map_meta_cap' => true,
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => 20,
    'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
    'menu_icon' => 'dashicons-businessman'
  );

  register_post_type('yakadanda_jobadder', $args);
}

add_action('init', 'create_yakadanda_jobadder_taxonomies', 0);

function create_yakadanda_jobadder_taxonomies() {
  $labels = array(
    'name' => _x('Categories', 'taxonomy general name'),
    'singular_name' => _x('Category', 'taxonomy singular name'),
    'search_items' => __('Search Categories'),
    'all_items' => __('All Categories'),
    'parent_item' => __('Parent Category'),
    'parent_item_colon' => __('Parent Category:'),
    'edit_item' => __('Edit Category'),
    'update_item' => __('Update Category'),
    'add_new_item' => __('Add New Category'),
    'new_item_name' => __('New Category Name'),
    'menu_name' => __('Category'),
  );

  $args = array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array('slug' => 'jobadder-category'),
  );

  register_taxonomy('yakadanda_jobadder_category', array('yakadanda_jobadder'), $args);

  $labels = array(
    'name' => _x('Locations', 'taxonomy general name'),
    'singular_name' => _x('Location', 'taxonomy singular name'),
    'search_items' => __('Search Locations'),
    'popular_items' => __('Popular Locations'),
    'all_items' => __('All Locations'),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __('Edit Location'),
    'update_item' => __('Update Location'),
    'add_new_item' => __('Add New Location'),
    'new_item_name' => __('New Location Name'),
    'separate_items_with_commas' => __('Separate locations with commas'),
    'add_or_remove_items' => __('Add or remove locations'),
    'choose_from_most_used' => __('Choose from the most used locations'),
    'not_found' => __('No locations found.'),
    'menu_name' => __('Locations'),
  );

  $args = array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array('slug' => 'jobadder-location'),
  );

  register_taxonomy('yakadanda_jobadder_location', array('yakadanda_jobadder'), $args);
  
  $labels = array(
    'name' => _x('Job Types', 'taxonomy general name'),
    'singular_name' => _x('Job Type', 'taxonomy singular name'),
    'search_items' => __('Search Job Types'),
    'popular_items' => __('Popular Job Types'),
    'all_items' => __('All Job Types'),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __('Edit Job Type'),
    'update_item' => __('Update Job Type'),
    'add_new_item' => __('Add New Job Type'),
    'new_item_name' => __('New Job Type Name'),
    'separate_items_with_commas' => __('Separate job types with commas'),
    'add_or_remove_items' => __('Add or remove job types'),
    'choose_from_most_used' => __('Choose from the most used job types'),
    'not_found' => __('No job types found.'),
    'menu_name' => __('Job Types'),
  );

  $args = array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array('slug' => 'jobadder-job-type'),
  );

  register_taxonomy('yakadanda_jobadder_job_type', array('yakadanda_jobadder'), $args);
}
