<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action('widgets_init', 'yakadanda_jobadder_register_widget');

function yakadanda_jobadder_register_widget() {
  register_widget('Yakadanda_Jobadder_Filter_Widget');
  register_widget('Yakadanda_Jobadder_Details_In_Single_Widget');
}

class Yakadanda_Jobadder_Filter_Widget extends WP_Widget {

  function __construct() {
    parent::__construct(
        'yakadanda_jobadder_filter_widget',
        __('JobAdder Filter', 'yakadanda-jobadder'),
        array('description' => __('A JobAdder Filter Widget', 'yakadanda-jobadder'),)
    );
  }

  public function widget($args, $instance) {
    if ( !is_singular( 'yakadanda_jobadder' ) ) {
      echo $args['before_widget'];
      if (!empty($instance['title'])) {
        echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
      }

      $locations = $this->get_locations($instance['location_display']);

      $types = $this->get_types($instance['type_display']);

      ?>
        <div class="filter">
          <ul class="list-unstyled">
            <?php if ($instance['location']): ?>
              <li class="title">
                <?php echo $instance['location_title']; ?>
              </li>
              <li>
                <input id="location-all" type='checkbox' value='0' data-slug="all" checked>
                <label for="location-all"><?php _e('All', 'yakadanda-jobadder') ?></label>
              </li>
              <?php foreach ($locations as $term): ?>
                <?php
                  if ( (! in_array($term->term_id, $instance['location_custom'])) && ($instance['location_display'] == 'show_custom') ):
                    continue;
                  endif;
                ?>
                <li>
                  <input id="<?php echo "jobadder_location_{$term->term_id}"; ?>" name="jobadder_location" type="checkbox" value="<?php echo $term->term_id; ?>" data-slug="<?php echo $term->slug; ?>">
                  <label for="<?php echo "jobadder_location_{$term->term_id}"; ?>"><?php echo $term->name; ?></label>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
            <?php if ($instance['type']): ?>
              <li class="title">
                <?php echo $instance['type_title']; ?>
              </li>
              <li>
                <input id="job-type-all" type='checkbox' value='0' data-slug="all" checked>
                <label for="job-type-all"><?php _e('All', 'yakadanda-jobadder') ?></label>
              </li>
              <?php foreach ($types as $term): ?>
                <?php
                  if ( (! in_array($term->term_id, $instance['type_custom'])) && ($instance['type_display'] == 'show_custom') ):
                    continue;
                  endif;
                ?>
                <li>
                  <input id="<?php echo "jobadder_job_type_{$term->term_id}"; ?>" name="jobadder_job_type" type="checkbox" value="<?php echo $term->term_id; ?>" data-slug="<?php echo $term->slug; ?>">
                  <label for="<?php echo "jobadder_job_type_{$term->term_id}"; ?>"><?php echo $term->name; ?></label>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        </div>
      <?php

      echo $args['after_widget'];
    }
  }

  public function form($instance) {
    $title = !empty($instance['title']) ? $instance['title'] : null;
    
    $location = !empty($instance['location']) ? $instance['location'] : null;
    $location_title = !empty($instance['location_title']) ? $instance['location_title'] : null;
    $location_display = !empty($instance['location_display']) ? $instance['location_display'] : null;
    $location_custom = !empty($instance['location_custom']) ? $instance['location_custom'] : [];
    
    $location_display = ($location_display) ? $location_display : 'show_empty';
    
    $type = !empty($instance['type']) ? $instance['type'] : null;
    $type_title = !empty($instance['type_title']) ? $instance['type_title'] : null;
    $type_display = !empty($instance['type_display']) ? $instance['type_display'] : null;
    $type_custom = !empty($instance['type_custom']) ? $instance['type_custom'] : [];
    
    $type_display = ($type_display) ? $type_display : 'show_empty';
    
    $location_terms = $this->get_locations('show_empty');
    $type_terms = $this->get_types('show_empty');
    
    ?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'yakadanda-jobadder'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" placeholder="<?php _e('Widget Title', 'yakadanda-jobadder'); ?>">
      </p>
      <p>
        <label><?php _e('Location:', 'yakadanda-jobadder'); ?></label><br>
        <legend class="screen-reader-text"><span><?php _e('Location:', 'yakadanda-jobadder'); ?></span></legend>
        <label for="<?php echo $this->get_field_id('location'); ?>">
          <input type="checkbox" value="1" id="<?php echo $this->get_field_id('location'); ?>" name="<?php echo $this->get_field_name('location'); ?>" <?php echo ($location) ? 'checked' : NULL; ?> onchange="yj_location_switch(this)">
          <span><?php echo ($location) ? __('On') : __('Off'); ?></span>
        </label>
      </p>
      <p<?php echo ($location) ? NULL : " style='display: none;'"; ?> class="yj-location-switch">
        <input class="widefat" id="<?php echo $this->get_field_id('location_title'); ?>" name="<?php echo $this->get_field_name('location_title'); ?>" type="text" value="<?php echo esc_attr($location_title); ?>" placeholder="<?php _e('Location Title', 'yakadanda-jobadder'); ?>">
      </p>
      <p<?php echo ($location) ? NULL : " style='display: none;'"; ?> class="yj-location-switch">
        <label>
          <input type="radio" id="<?php echo $this->get_field_id('location_show_empty'); ?>" value="show_empty" name="<?php echo $this->get_field_name('location_display'); ?>" <?php echo ($location_display == 'show_empty') ? 'checked' : NULL; ?> onchange="yj_show_custom_location(this)">&nbsp;
          <span for="<?php echo $this->get_field_id('location_show_empty'); ?>"><?php _e('Display all locations', 'yakadanda-jobadder'); ?></span>
        </label><br>
        <label>
          <input type="radio" id="<?php echo $this->get_field_id('location_hide_empty'); ?>" value="hide_empty" name="<?php echo $this->get_field_name('location_display'); ?>" <?php echo ($location_display == 'hide_empty') ? 'checked' : NULL; ?> onchange="yj_show_custom_location(this)">&nbsp;
          <span for="<?php echo $this->get_field_id('location_hide_empty'); ?>"><?php _e('Only display locations with available jobs', 'yakadanda-jobadder'); ?></span>
        </label><br>
        <label>
          <input type="radio" id="<?php echo $this->get_field_id('location_show_custom'); ?>" value="show_custom" name="<?php echo $this->get_field_name('location_display'); ?>" <?php echo ($location_display == 'show_custom') ? 'checked' : NULL; ?> onchange="yj_show_custom_location(this)">&nbsp;
          <span for="<?php echo $this->get_field_id('location_show_custom'); ?>"><?php _e('Display custom locations', 'yakadanda-jobadder'); ?></span>
        </label><br>
      </p>
      <div<?php echo ($location) ? NULL : " style='display: none;'"; ?> class="yj-location-switch">
        <p<?php echo ($location_display == 'show_custom') ? NULL : " style='display: none;'"; ?> class="yj-show-custom-location">
          <?php foreach ($location_terms as $key => $term): ?>
            <input id="<?php echo $this->get_field_id("location_show_custom_{$key}"); ?>" name="<?php echo $this->get_field_name('location_custom[]'); ?>" type="checkbox" value="<?php echo $term->term_id; ?>" <?php echo in_array($term->term_id, $location_custom) ? 'checked' : NULL; ?>>
            <label for="<?php echo $this->get_field_id("location_show_custom_{$key}"); ?>"><?php echo $term->name; ?></label><br>
          <?php endforeach; ?>
        </p>
      </div>
      
      <p>
        <label><?php _e('Type:', 'yakadanda-jobadder'); ?></label><br>
        <legend class="screen-reader-text"><span><?php _e('Type:', 'yakadanda-jobadder'); ?></span></legend>
        <label for="<?php echo $this->get_field_id('type'); ?>">
          <input type="checkbox" value="1" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>" <?php echo ($type) ? 'checked' : NULL; ?> onchange="yj_type_switch(this)">
          <span><?php echo ($type) ? __('On') : __('Off'); ?></span>
        </label>
      </p>
      <p<?php echo ($type) ? NULL : " style='display: none;'"; ?> class="yj-type-switch">
        <input class="widefat" id="<?php echo $this->get_field_id('type_title'); ?>" name="<?php echo $this->get_field_name('type_title'); ?>" type="text" value="<?php echo esc_attr($type_title); ?>" placeholder="<?php _e('Type Title', 'yakadanda-jobadder'); ?>">
      </p>
      <p<?php echo ($type) ? NULL : " style='display: none;'"; ?> class="yj-type-switch">
        <label>
          <input type="radio" id="<?php echo $this->get_field_id('type_show_empty'); ?>" value="show_empty" name="<?php echo $this->get_field_name('type_display'); ?>" <?php echo ($type_display == 'show_empty') ? 'checked' : NULL; ?> onchange="yj_show_custom_type(this)">&nbsp;
          <span for="<?php echo $this->get_field_id('type_show_empty'); ?>"><?php _e('Display all types', 'yakadanda-jobadder'); ?></span>
        </label><br>
        <label>
          <input type="radio" id="<?php echo $this->get_field_id('type_hide_empty'); ?>" value="hide_empty" name="<?php echo $this->get_field_name('type_display'); ?>" <?php echo ($type_display == 'hide_empty') ? 'checked' : NULL; ?> onchange="yj_show_custom_type(this)">&nbsp;
          <span for="<?php echo $this->get_field_id('type_hide_empty'); ?>"><?php _e('Only display types with available jobs', 'yakadanda-jobadder'); ?></span>
        </label><br>
        <label>
          <input type="radio" id="<?php echo $this->get_field_id('type_show_custom'); ?>" value="show_custom" name="<?php echo $this->get_field_name('type_display'); ?>" <?php echo ($type_display == 'show_custom') ? 'checked' : NULL; ?> onchange="yj_show_custom_type(this)">&nbsp;
          <span for="<?php echo $this->get_field_id('type_show_custom'); ?>"><?php _e('Display custom types', 'yakadanda-jobadder'); ?></span>
        </label><br>
      </p>
      <div<?php echo ($type) ? NULL : " style='display: none;'"; ?> class="yj-type-switch">
        <p<?php echo ($type_display == 'show_custom') ? NULL : " style='display: none;'"; ?> class="yj-show-custom-type">
          <?php foreach ($type_terms as $key => $term): ?>
            <input id="<?php echo $this->get_field_id("type_show_custom_{$key}"); ?>" name="<?php echo $this->get_field_name('type_custom[]'); ?>" type="checkbox" value="<?php echo $term->term_id; ?>" <?php echo in_array($term->term_id, $type_custom) ? 'checked' : NULL; ?>>
            <label for="<?php echo $this->get_field_id("type_show_custom_{$key}"); ?>"><?php echo $term->name; ?></label><br>
          <?php endforeach; ?>
        </p>
      </div>
    <?php
    wp_nonce_field( 'yakadanda_jobadder_filter_action', 'yakadanda_jobadder_filter_nonce' );
  }

  public function update($new_instance, $old_instance) {
    $instance = array();
    
    if (!isset($_POST['yakadanda_jobadder_filter_nonce']) || !wp_verify_nonce($_POST['yakadanda_jobadder_filter_nonce'], 'yakadanda_jobadder_filter_action')) {
      $instance = $old_instance;
    } else {
      $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';

      $instance['location'] = (!empty($new_instance['location']) ) ? strip_tags($new_instance['location']) : '';
      $instance['location_title'] = (!empty($new_instance['location_title']) ) ? strip_tags($new_instance['location_title']) : '';
      $instance['location_display'] = (!empty($new_instance['location_display']) ) ? strip_tags($new_instance['location_display']) : '';
      $instance['location_custom'] = (!empty($new_instance['location_custom']) ) ? $new_instance['location_custom'] : [];

      $instance['type'] = (!empty($new_instance['type']) ) ? strip_tags($new_instance['type']) : '';
      $instance['type_title'] = (!empty($new_instance['type_title']) ) ? strip_tags($new_instance['type_title']) : '';
      $instance['type_display'] = (!empty($new_instance['type_display']) ) ? strip_tags($new_instance['type_display']) : '';
      $instance['type_custom'] = (!empty($new_instance['type_custom']) ) ? $new_instance['type_custom'] : [];
    }
    
    return $instance;
  }
  
  public function get_locations($location_display) {
    $arguments = array(
      'orderby' => 'name',
      'order' => 'ASC',
      'hide_empty' => ($location_display == 'hide_empty') ? true : false,
      'exclude' => array(),
      'exclude_tree' => array(),
      'include' => array(),
      'number' => '',
      'fields' => 'all',
      'slug' => '',
      'parent' => '',
      'hierarchical' => true,
      'child_of' => 0,
      'childless' => false,
      'get' => '',
      'name__like' => '',
      'description__like' => '',
      'pad_counts' => false,
      'offset' => '',
      'search' => '',
      'cache_domain' => 'core'
    );
    return get_terms(array('yakadanda_jobadder_location'), $arguments);
  }
  
  public function get_types($type_display) {
    $arguments = array(
      'orderby' => 'name',
      'order' => 'ASC',
      'hide_empty' => ($type_display == 'hide_empty') ? true : false,
      'exclude' => array(),
      'exclude_tree' => array(),
      'include' => array(),
      'number' => '',
      'fields' => 'all',
      'slug' => '',
      'parent' => '',
      'hierarchical' => true,
      'child_of' => 0,
      'childless' => false,
      'get' => '',
      'name__like' => '',
      'description__like' => '',
      'pad_counts' => false,
      'offset' => '',
      'search' => '',
      'cache_domain' => 'core'
    );
    return get_terms(array('yakadanda_jobadder_job_type'), $arguments);
  }

}

class Yakadanda_Jobadder_Details_In_Single_Widget extends WP_Widget {

  function __construct() {
    parent::__construct(
        'yakadanda_jobadder_details_in_single_widget',
        __('JobAdder Details In Single', 'yakadanda-jobadder'),
        array('description' => __('A JobAdder Details for single page', 'yakadanda-jobadder'),)
    );
  }

  public function widget($args, $instance) {
    if ( is_single() && is_singular( 'yakadanda_jobadder' ) ) {
      echo $args['before_widget'];
      if (!empty($instance['title'])) {
        echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
      }

      $post_id = get_the_ID();

      $locations = null;
      foreach ( wp_get_post_terms($post_id, 'yakadanda_jobadder_location') as $location ) {
        $locations .= ', ' . $location->name;
      }
      $locations = ltrim($locations, ', ');
      $categories = null;
      foreach ( wp_get_post_terms($post_id, 'yakadanda_jobadder_category') as $category ) {
        $categories .= ', ' . $category->name;
      }
      $categories = ltrim($categories, ', ');
      $types = null;
      foreach ( wp_get_post_terms($post_id, 'yakadanda_jobadder_job_type') as $type ) {
        $types .= ', ' . $type->name;
      }
      $types = ltrim($types, ', ');
      
      echo '<div class="details">';
      if (!empty($instance['location'])) {
        echo "<div class='field'>Location <strong>{$locations}</strong></div>";
      }
      if (!empty($instance['category'])) {
        echo "<div class='field'>Category <strong>{$categories}</strong></div>";
      }
      if (!empty($instance['type'])) {
        echo "<div class='field'>Type <strong>{$types}</strong></div>";
      }
      echo '</div>';

      echo $args['after_widget'];
    }
  }

  public function form($instance) {
    $title = !empty($instance['title']) ? $instance['title'] : null;
    $location = !empty($instance['location']) ? $instance['location'] : null;
    $category = !empty($instance['category']) ? $instance['category'] : null;
    $type = !empty($instance['type']) ? $instance['type'] : null;
    ?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'yakadanda-jobadder'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" placeholder="<?php _e('Widget Title', 'yakadanda-jobadder'); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('location'); ?>"><?php _e('Location label:', 'yakadanda-jobadder'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('location'); ?>" name="<?php echo $this->get_field_name('location'); ?>" type="text" value="<?php echo esc_attr($location); ?>" placeholder="<?php _e('Location', 'yakadanda-jobadder'); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category label:', 'yakadanda-jobadder'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" type="text" value="<?php echo esc_attr($category); ?>" placeholder="<?php _e('Category', 'yakadanda-jobadder'); ?>">
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Type label:', 'yakadanda-jobadder'); ?></label> 
        <input class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>" type="text" value="<?php echo esc_attr($type); ?>" placeholder="<?php _e('Type', 'yakadanda-jobadder'); ?>">
      </p>
    <?php
    wp_nonce_field( 'yakadanda_jobadder_details_in_single_action', 'yakadanda_jobadder_details_in_single_nonce' );
  }

  public function update($new_instance, $old_instance) {
    $instance = array();
  
    if (!isset($_POST['yakadanda_jobadder_details_in_single_nonce']) || !wp_verify_nonce($_POST['yakadanda_jobadder_details_in_single_nonce'], 'yakadanda_jobadder_details_in_single_action')) {
      $instance = $old_instance;
    } else {
      $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
      $instance['location'] = (!empty($new_instance['location']) ) ? strip_tags($new_instance['location']) : '';
      $instance['category'] = (!empty($new_instance['category']) ) ? strip_tags($new_instance['category']) : '';
      $instance['type'] = (!empty($new_instance['type']) ) ? strip_tags($new_instance['type']) : '';
    }
    
    return $instance;
  }

}
