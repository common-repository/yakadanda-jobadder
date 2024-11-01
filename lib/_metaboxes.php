<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$prefix = 'yakadanda_jobadder_';

$meta_box = array(
  'id' => 'yakadanda-jobadder-meta-box',
  'title' => 'Meta Options',
  'page' => 'yakadanda_jobadder',
  'context' => 'normal',
  'priority' => 'high',
  'fields' => array(
    array(
      'name' => __('Advertiser', 'yakadanda-jobadder'),
      'desc' => __('Your Company', 'yakadanda-jobadder'),
      'id' => $prefix . 'textmedium_advertiser',
      'type' => 'text',
      'std' => ''
    ),
    array(
      'name' => __('Jid', 'yakadanda-jobadder'),
      'desc' => __('e.g. 10000', 'yakadanda-jobadder'),
      'id' => $prefix . 'textmedium_jid',
      'type' => 'text_int',
      'std' => ''
    ),
    array(
      'name' => __('Source', 'yakadanda-jobadder'),
      'desc' => __('e.g. xco62rdcabeezp6as7vnn5yfcm', 'yakadanda-jobadder'),
      'id' => $prefix . 'textmedium_source',
      'type' => 'text',
      'std' => ''
    ),
    array(
      'name' => __('Reference', 'yakadanda-jobadder'),
      'desc' => __('e.g. sample001', 'yakadanda-jobadder'),
      'id' => $prefix . 'textmedium_reference',
      'type' => 'text',
      'std' => ''
    ),
    array(
      'name' => __('Posted Date', 'yakadanda-jobadder'),
      'desc' => __('YYYY-MM-DD', 'yakadanda-jobadder'),
      'id' => $prefix . 'textmedium_dateposted',
      'type' => 'text',
      'std' => ''
    ),
    array(
      'name' => __('Updated Date', 'yakadanda-jobadder'),
      'desc' => __('e.g. 2016-08-31T01:48:39Z', 'yakadanda-jobadder'),
      'id' => $prefix . 'textmedium_dateupdated',
      'type' => 'text',
      'std' => ''
    ),
    array(
      'name' => __('Summary', 'yakadanda-jobadder'),
      'desc' => __('Job summary / short description', 'yakadanda-jobadder'),
      'id' => $prefix . 'textareasmall_summary',
      'type' => 'textarea',
      'std' => ''
    ),
    array(
      'name' => __('Search Title', 'yakadanda-jobadder'),
      'desc' => __('Search results title. The job title job seekers see when search results are returned.', 'yakadanda-jobadder'),
      'id' => $prefix . 'textmedium_search_title',
      'type' => 'text',
      'std' => ''
    ),
    array(
      'name' => __('Bullet Points', 'yakadanda-jobadder'),
      'desc' => __('Maximum of 3 bullet points per job ad.', 'yakadanda-jobadder'),
      'id' => $prefix . 'textmedium_bullet_points',
      'type' => 'text',
      'repeatable' => true,
      'std' => ''
    ),
    array(
      'name' => __('Salary', 'yakadanda-jobadder'),
      'desc' => __('Minimum salary value, and Maximum salary value.', 'yakadanda-jobadder'),
      'id' => $prefix . 'textmoney_salary_value',
      'type' => 'text_money',
      // 'before_field' => '£', // override '$' symbol if needed
      'repeatable' => true,
      'std' => ''
    ),
    array(
      'name' => __('Salary Period', 'yakadanda-jobadder'),
      'desc' => __('Salary period, e.g. PerAnnum', 'yakadanda-jobadder'),
      'id' => $prefix . 'textmedium_salary_period',
      'type' => 'text',
      'std' => ''
    ),
    array(
      'name' => __('Salary Text', 'yakadanda-jobadder'),
      'desc' => __('Additional salary text, e.g. "Including company car and phone"', 'yakadanda-jobadder'),
      'id' => $prefix . 'textareasmall_salary_text',
      'type' => 'textarea',
      'std' => ''
    ),
    array(
      'name' => __('Email To', 'yakadanda-jobadder'),
      'desc' => __('Email address applications should be sent to, e.g. applications@yourcompany.com', 'yakadanda-jobadder'),
      'id' => $prefix . 'email_apply_email_to',
      'type' => 'text_email',
      'std' => ''
    ),
    array(
      'name' => __('URL', 'yakadanda-jobadder'),
      'desc' => __('Unique application form URL for this job, e.g. http://apply.jobadder.com/999/10000/xco62rdcabeezp6as7vnn5yfcm', 'yakadanda-jobadder'),
      'id' => $prefix . 'url_apply_url',
      'type' => 'text_url',
      // 'protocols' => array('http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet'), // Array of allowed protocols
      'std' => ''
    )
  )
);

add_action('admin_menu', 'yakadanda_jobadder_add_box');

// Add meta box
function yakadanda_jobadder_add_box() {
  global $meta_box;

  add_meta_box($meta_box['id'], $meta_box['title'], 'yakadanda_jobadder_show_box', $meta_box['page'], $meta_box['context'], $meta_box['priority']);
}

// Callback function to show fields in meta box
function yakadanda_jobadder_show_box() {
  global $meta_box, $post;

  // Use nonce for verification
  echo '<input type="hidden" name="yakadanda_jobadder_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

  echo '<table class="form-table">';

  foreach ($meta_box['fields'] as $field) {
    // get current post meta data
    $meta = get_post_meta($post->ID, $field['id'], true);

    echo '<tr>',
    '<th scope="row"><label for="', $field['id'], '">', $field['name'], '</label></th>',
    '<td>';

    $input_type = 'text';
    if ($field['type'] == 'text_int') { $input_type = 'number'; }
    if ($field['type'] == 'text_email') { $input_type = 'email'; }
    if ($field['type'] == 'text_url') { $input_type = 'url'; }
    
    $before_field = NULL;
    if ($field['type'] == 'text_money') {
      $before_field = isset($field['before_field']) ? $field['before_field'] : '$';
    }

    if ( ($field['type'] == 'text') || ($field['type'] == 'text_int') || ($field['type'] == 'text_money') || ($field['type'] == 'text_email') || ($field['type'] == 'text_url') ) {
      
      if (isset($field['repeatable']) && $field['repeatable']) {
        $meta = ($meta) ? $meta : $field['std'];
        echo '<div id="' . $field['id'] . '_container" data-before_field="' . $before_field . '">';
        
        $before_field = ($before_field) ? "<span>$before_field&nbsp;</span>" : NULL;
        
        echo $before_field , '<input type="' , $input_type , '" name="', $field['id'], '[0]" id="', $field['id'] . "_0", '" value="', isset($meta[0]) ? $meta[0] : null, '" class="regular-text" />';
        echo '&nbsp;<a class="button" onclick="yj_delete_input(this, \'' . $field['id'] . '\'); return false;" href="#yj_delete_input">Delete</a>';
        if (is_array($meta)) {
          foreach($meta as $meta_k => $meta_v) { if ($meta_k == 0) { continue; }
            echo '<hr>';
            echo $before_field , '<input type="' , $input_type , '" name="', $field['id'], '[' . $meta_k . ']" id="', $field['id'] . "_$meta_k", '" value="', $meta_v, '" class="regular-text" />';
            echo '&nbsp;<a class="button" onclick="yj_delete_input(this, \'' . $field['id'] . '\'); return false;" href="#yj_delete_input">Delete</a>';
          }
        }
        echo '</div>';
      } else {
        echo $before_field , '<input type="' , $input_type , '" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" class="regular-text" />';
      }
      echo '<p class="description">' . $field['desc'] . '</p>';
      echo (isset($field['repeatable']) && $field['repeatable']) ? '<p><a class="button" onclick="yj_add_input(\'' . $field['id'] . '\'); return false;" href="#yj_add_input">Add</a></p>' : NULL;
      
    } else if ( $field['type'] == 'textarea' ) {
      
      echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" class="large-text">', $meta ? esc_textarea($meta) : esc_textarea($field['std']), '</textarea>';
      echo '<p class="description">' . $field['desc'] . '</p>';
      
    }

    echo '</td><td>',
    '</td></tr>';
  }

  echo '</table>';
}

add_action('save_post', 'yakadanda_jobadder_save_data');

// Save data from meta box
function yakadanda_jobadder_save_data($post_id) {
  global $meta_box;

  // Check if our nonce is set.
  if ( ! isset( $_POST['yakadanda_jobadder_meta_box_nonce'] ) ) {
      return $post_id;
  }
  
  // verify nonce
  if (!wp_verify_nonce($_POST['yakadanda_jobadder_meta_box_nonce'], basename(__FILE__))) {
    return $post_id;
  }

  // check autosave
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return $post_id;
  }

  // check permissions
  if ('page' == $_POST['post_type']) {
    if (!current_user_can('edit_page', $post_id)) {
      return $post_id;
    }
  } elseif (!current_user_can('edit_post', $post_id)) {
    return $post_id;
  }

  foreach ($meta_box['fields'] as $field) {
    $old = get_post_meta($post_id, $field['id'], true);
    $post_field = $_POST[$field['id']];
    $new = NULL;
    
    if ( is_array($post_field) ) {
      $clean_new = array();
      foreach($post_field as $post_k => $post_v) {
        $clean_new[] = sanitize_text_field($post_v);
      }
      $new = $clean_new;      
    } else {
      if ( ($field['type'] == 'text') || ($field['type'] == 'text_money') ) {
        $new = sanitize_text_field($post_field);
      }
      if ($field['type'] == 'text_int') {
        $new = intval($post_field) ? $post_field : NULL;
      }
      if ($field['type'] == 'text_email') {
        $new = is_email($post_field) ? sanitize_email($post_field) : NULL;
      }
      if ($field['type'] == 'text_url') {
        $new = esc_url($post_field);
      }
      if ($field['type'] == 'textarea') {
        $new = wp_kses_post($post_field);
      }
    }

    if ($new && $new != $old) {
      update_post_meta($post_id, $field['id'], $new);
    } elseif ('' == $new && $old) {
      delete_post_meta($post_id, $field['id'], $old);
    }
  }
}
