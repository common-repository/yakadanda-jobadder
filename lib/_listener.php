<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * http://domain.com.au/?passphrase=0a751e411942f7c756726deeab8a2aca
 */

add_action('wp_loaded', 'yakadanda_jobadder_listener');

function yakadanda_jobadder_listener() {
  if ( !isset( $_GET['passphrase'] ) ) {
    return;
  }
  
  global $yakadanda_jobadder_options;
  
  $passphrase = $yakadanda_jobadder_options['passphrase'];
  
  if ( $_GET['passphrase'] != $passphrase ) {
    return;
  }
  
  //Listener is now activated
  yakadanda_jobadder_event(true);
}

function yakadanda_jobadder_event($real_cron_job = false) {
  $jobAdder = (file_exists(YAKADANDA_JOBADDER_PLUGIN_DIR . '/xml/jobadder_data.xml')) ? json_decode(json_encode(simplexml_load_file(YAKADANDA_JOBADDER_PLUGIN_URL . '/xml/jobadder_data.xml', 'SimpleXMLElement', LIBXML_NOCDATA)), TRUE) : null;

  if (isset($jobAdder['Fields']['Field'])) {
    foreach ($jobAdder['Fields']['Field'] as $k => $v) {
      if (isset($v['@attributes']['name']) && ($v['@attributes']['name'] != array()) ) {
        switch ($v['@attributes']['name']) {
          case 'Category':
            //yakadanda_jobadder_create_category($v['Values']['Value']);
            break;
          case 'Location':
            yakadanda_jobadder_create_location($v['Values']['Value']);
            break;
          case 'Work Type':
            yakadanda_jobadder_create_work_type($v['Values']['Value']);
            break;
        }
      }
    }
    yakadanda_jobadder_make_private();
    $jobs = ( isset($jobAdder['Job']['@attributes']) ) ? [$jobAdder['Job']] : $jobAdder['Job'];
    foreach ($jobs as $k => $v) {
      $post_id = yakadanda_jobadder_is_post_exist($v['@attributes']['jid']);
      yakadanda_jobadder_create_update_post($v, $jobAdder['@attributes'], $post_id);
    }

    // send notification
    yakadanda_jobadder_send_notification();

    if ($real_cron_job) { die("Success."); }
  } else {
    if ($real_cron_job) { die("Uh, what happened?"); }
  }
}

function yakadanda_jobadder_create_category($data) {
  foreach ($data as $datum) {
    $parent_category = wp_insert_term(sanitize_text_field($datum['@attributes']['name']), 'yakadanda_jobadder_category');
    $parent_category_id = array_key_exists('term_id', $parent_category) ? $parent_category['term_id'] : $parent_category->error_data['term_exists'];
    if ($parent_category_id && $datum['Field']['@attributes']['name'] == 'Sub Category' && count($datum['Field']['Values']['Value']) > 0) {
      foreach($datum['Field']['Values']['Value'] as $k => $v) {
        wp_insert_term(sanitize_text_field($v['@attributes']['name']), 'yakadanda_jobadder_category', ['parent' => $parent_category_id]);
      }
    }
  }
}

function yakadanda_jobadder_create_location($data) {
  foreach ($data as $datum) {
    wp_insert_term(sanitize_text_field($datum['@attributes']['name']), 'yakadanda_jobadder_location');
  }
}

function yakadanda_jobadder_create_work_type($data) {
  foreach ($data as $datum) {
    wp_insert_term(sanitize_text_field($datum['@attributes']['name']), 'yakadanda_jobadder_job_type');
  }
}

function yakadanda_jobadder_create_update_post($v, $root_attributes, $item_id = null) {
  $post = array(
      'post_title'   => wp_strip_all_tags($v['Title']),
      'post_content' => wp_kses_post($v['Description']),
      'post_excerpt' => $v['@attributes']['jid'],
      'post_status'  => 'publish',
      'post_type'    => 'yakadanda_jobadder',
      'post_date'    => $v['@attributes']['datePosted']
    );

  if ($item_id) {
    $post = array_merge(['ID' => $item_id], $post);
  }

  if ( $post_id = ($item_id) ? wp_update_post($post, false) : wp_insert_post($post, false) ) {
    $advertiser = (isset($root_attributes['advertiser']) && $root_attributes['advertiser'] != array()) ? $root_attributes['advertiser'] : null;
    $jid = (isset($root_attributes['jid']) && $root_attributes['jid'] != array()) ? $root_attributes['jid'] : null;
    $source = (isset($root_attributes['source']) && $root_attributes['source'] != array()) ? $root_attributes['source'] : null;
    $reference = (isset($v['@attributes']['reference']) && $v['@attributes']['reference'] != array()) ? $v['@attributes']['reference'] : null;
    $dateposted = (isset($v['@attributes']['datePosted']) && $v['@attributes']['datePosted'] != array()) ? $v['@attributes']['datePosted'] : null;
    $dateupdated = (isset($v['@attributes']['dateUpdated']) && $v['@attributes']['dateUpdated'] != array()) ? $v['@attributes']['dateUpdated'] : null;
    $summary = (isset($v['Summary']) && $v['Summary'] != array()) ? $v['Summary'] : null;
    $search_title = (isset($v['SearchTitle']) && $v['SearchTitle'] != array()) ? $v['SearchTitle'] : null;
    $bullet_points = (isset($v['BulletPoints']['BulletPoint']) && $v['BulletPoints']['BulletPoint'] != array()) ? $v['BulletPoints']['BulletPoint'] : null;
    $salary_value = null;
    if (isset($v['Salary']['MinValue']) || isset($v['Salary']['MaxValue'])) {
      $minvalue = (isset($v['Salary']['MinValue']) && $v['Salary']['MinValue'] != array()) ? $v['Salary']['MinValue'] : '&#164;';
      $maxvalue = (isset($v['Salary']['MaxValue']) && $v['Salary']['MaxValue'] != array()) ? $v['Salary']['MaxValue'] : '&#164;';
      $salary_value = array($minvalue, $maxvalue);
    }
    $salary_text = (isset($v['Salary']['Text']) && $v['Salary']['Text'] != array()) ? $v['Salary']['Text'] : null;
    $salary_period = (isset($v['Salary']['@attributes']['period']) && $v['Salary']['@attributes']['period'] != array()) ? $v['Salary']['@attributes']['period'] : null;
    $email_apply_email_to = (isset($v['Apply']['EmailTo']) && $v['Apply']['EmailTo'] != array()) ? $v['Apply']['EmailTo'] : null;
    $url_apply_url = (isset($v['Apply']['Url']) && $v['Apply']['Url'] != array()) ? $v['Apply']['Url'] : null;
    $category = (isset($v['Classifications']['Classification'][0]) && $v['Classifications']['Classification'][0] != array()) ? $v['Classifications']['Classification'][0] : null;
    $sub_category = (isset($v['Classifications']['Classification'][1]) && $v['Classifications']['Classification'][1] != array()) ? $v['Classifications']['Classification'][1] : null;
    $location = (isset($v['Classifications']['Classification'][2]) && $v['Classifications']['Classification'][2] != array()) ? $v['Classifications']['Classification'][2] : null;
    $job_type = (isset($v['Classifications']['Classification'][3]) && $v['Classifications']['Classification'][3] != array()) ? $v['Classifications']['Classification'][3] : null;

    $clean_bullet_points = array();
    if (is_array($bullet_points)) {
      foreach ($bullet_points as $bullet_k => $bullet_v) {
        $clean_bullet_points[] = sanitize_text_field($bullet_v);
      }
    } else {
      $clean_bullet_points[] = sanitize_text_field($bullet_points);
    }
    $bullet_points = $clean_bullet_points;
    
    $clean_salary_value = array();
    if (is_array($salary_value)) {
      foreach ($salary_value as $salary_k => $salary_v) {
        $clean_salary_value[] = sanitize_text_field($salary_v);
      }
    } else {
      $clean_salary_value[] = sanitize_text_field($salary_value);
    }
    $salary_value = $clean_salary_value;

    if ($item_id) {
      update_post_meta($post_id, 'yakadanda_jobadder_textmedium_advertiser', sanitize_text_field($advertiser));
      if (intval($jid)) {
        update_post_meta($post_id, 'yakadanda_jobadder_textmedium_jid', $jid);
      }
      update_post_meta($post_id, 'yakadanda_jobadder_textmedium_source', sanitize_text_field($source));
      update_post_meta($post_id, 'yakadanda_jobadder_textmedium_reference', sanitize_text_field($reference));
      update_post_meta($post_id, 'yakadanda_jobadder_textmedium_dateposted', $dateposted);
      update_post_meta($post_id, 'yakadanda_jobadder_textmedium_dateupdated', $dateupdated);
      update_post_meta($post_id, 'yakadanda_jobadder_textareasmall_summary', wp_kses_post($summary));
      update_post_meta($post_id, 'yakadanda_jobadder_textmedium_search_title', sanitize_text_field($search_title));
      update_post_meta($post_id, 'yakadanda_jobadder_textmedium_bullet_points', $bullet_points);
      update_post_meta($post_id, 'yakadanda_jobadder_textmoney_salary_value', $salary_value);
      update_post_meta($post_id, 'yakadanda_jobadder_textmedium_salary_period', sanitize_text_field($salary_period));
      update_post_meta($post_id, 'yakadanda_jobadder_textareasmall_salary_text', wp_kses_post($salary_text));
      if (is_email($email_apply_email_to)) {
        update_post_meta($post_id, 'yakadanda_jobadder_email_apply_email_to', sanitize_email($email_apply_email_to));
      }
      update_post_meta($post_id, 'yakadanda_jobadder_url_apply_url', esc_url($url_apply_url));
    } else {
      add_post_meta($post_id, 'yakadanda_jobadder_textmedium_advertiser', sanitize_text_field($advertiser));
      if (intval($jid)) {
        add_post_meta($post_id, 'yakadanda_jobadder_textmedium_jid', $jid);
      }
      add_post_meta($post_id, 'yakadanda_jobadder_textmedium_source', sanitize_text_field($source));
      add_post_meta($post_id, 'yakadanda_jobadder_textmedium_reference', sanitize_text_field($reference));
      add_post_meta($post_id, 'yakadanda_jobadder_textmedium_dateposted', $dateposted);
      add_post_meta($post_id, 'yakadanda_jobadder_textmedium_dateupdated', $dateupdated);
      add_post_meta($post_id, 'yakadanda_jobadder_textareasmall_summary', wp_kses_post($summary));
      add_post_meta($post_id, 'yakadanda_jobadder_textmedium_search_title', sanitize_text_field($search_title));
      add_post_meta($post_id, 'yakadanda_jobadder_textmedium_bullet_points', $bullet_points);
      add_post_meta($post_id, 'yakadanda_jobadder_textmoney_salary_value', $salary_value);
      add_post_meta($post_id, 'yakadanda_jobadder_textmedium_salary_period', sanitize_text_field($salary_period));
      add_post_meta($post_id, 'yakadanda_jobadder_textareasmall_salary_text', wp_kses_post($salary_text));
      if (is_email($email_apply_email_to)) {
        add_post_meta($post_id, 'yakadanda_jobadder_email_apply_email_to', sanitize_email($email_apply_email_to));
      }
      add_post_meta($post_id, 'yakadanda_jobadder_url_apply_url', esc_url($url_apply_url));
    }

    if (isset($v['Classifications']['Classification']) && count($v['Classifications']['Classification']) == 4) {
      $parent_category_id = null;
      if ($category) {
        $parent_category = wp_insert_term(sanitize_text_field($category), 'yakadanda_jobadder_category');
        $parent_category_id = array_key_exists('term_id', $parent_category) ? $parent_category['term_id'] : $parent_category->error_data['term_exists'];
        wp_set_object_terms($post_id, $parent_category_id, 'yakadanda_jobadder_category');
      }
      if ($sub_category) {
        if ($parent_category_id) {
          $child_category = wp_insert_term(sanitize_text_field($sub_category), 'yakadanda_jobadder_category', ['parent' => $parent_category_id]);
        } else {
          $child_category = wp_insert_term(sanitize_text_field($sub_category), 'yakadanda_jobadder_category');
        }
        $child_category_id = array_key_exists('term_id', $child_category) ? $child_category['term_id'] : $child_category->error_data['term_exists'];
        wp_set_object_terms($post_id, $child_category_id, 'yakadanda_jobadder_category');
      }
      if ($parent_category_id && $child_category_id) {
        wp_set_object_terms($post_id, [$parent_category_id, $child_category_id], 'yakadanda_jobadder_category');
      }

      if ($location) {
        wp_set_object_terms($post_id, $location, 'yakadanda_jobadder_location');
      }

      if ($job_type) {
        wp_set_object_terms($post_id, $job_type, 'yakadanda_jobadder_job_type');
      }
    }
  } else {
    die("insert or update post failed.");
  }
}

function yakadanda_jobadder_is_post_exist($jid) {
  global $wpdb;

  $post_record = $wpdb->get_row("
      SELECT *
      FROM $wpdb->posts
      WHERE `post_excerpt` = '{$jid}'
    ");

  return ($post_record) ? $post_record->ID : false;
}

function yakadanda_jobadder_make_private() {
  global $wpdb;

  $action = $wpdb->query("
      UPDATE $wpdb->posts SET `post_status` = 'private'
      WHERE `post_type` = 'yakadanda_jobadder'
      AND `post_status` = 'publish'
    ");

  return $action;
}
