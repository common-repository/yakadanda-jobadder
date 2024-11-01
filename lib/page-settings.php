<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="wrap yakadanda-jobadder-page-settings">
  <div id="icon-edit" class="icon32 icon32-posts-quote"><br></div>
  <h1><?php _e('Settings', 'yakadanda-jobadder'); ?></h1>
  <?php if ($message): ?>
    <div id="yakadanda-jobadder-message" class="<?php echo $message['class']; ?>">
      <p><?php echo $message['msg']; ?></p>
    </div>
  <?php endif; ?>
  <form action="" method="post" enctype="multipart/form-data">
    <p></p>
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row">
            <label for="passphrase"><?php _e('Passphrase', 'yakadanda-jobadder'); ?></label>
          </th>
          <td>
            <input type="text" name="passphrase" id="passphrase" class="regular-text" value="<?php echo $passphrase; ?>"/>
            <p class="description"><?php _e('Your passphrase for HTTP POST and real Cron job.', 'yakadanda-jobadder'); ?></p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">
            <label for="http-post-url"><?php _e('HTTP POST the XML data to', 'yakadanda-jobadder'); ?></label>
          </th>
          <td>
            <p><code id="http-post-url"><?php echo plugins_url('/jobadder-catcher-<span>' . $passphrase . '</span>.php'); ?></code></p>
            <p class="description"><?php printf(__('URL where JobAdder can post the data (request to JobAdder), see %s.', 'yakadanda-jobadder'), '<a href="https://jobadder.com/support/jobs-on-your-website#xml-job-feed" target="_blank">JOBS ON YOUR WEBSITE</a>'); ?></p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">
            <label for="file-status"><?php _e('Catcher file status', 'yakadanda-jobadder'); ?></label>
          </th>
          <td>
            <p id="file-status"><?php echo "jobadder-catcher-<span>$passphrase</span>.php <em>$file_exists</em>"; ?></p>
            <p class="description"><?php printf(__('Please move %s file to %s folder, and rename it to %s.', 'yakadanda-jobadder'), '<code>' . YAKADANDA_JOBADDER_PLUGIN_DIR . 'jobadder-catcher-unique_passphrase.php</code>', '<code>' . WP_PLUGIN_DIR . '/</code>', '<code>jobadder-catcher-<span>' . $passphrase . '</span>.php</code>', 'jobadder-catcher-<span>' . $passphrase . '</span>.php'); ?></p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">
            <label for="xml-status"><?php _e('XML file status', 'yakadanda-jobadder'); ?></label>
          </th>
          <td>
            <p id="is-writable"><em><?php echo $is_writable; ?></em></p>
            <p class="description"><?php printf(__('Make sure jobadder_data.xml file is writable, see %s.', 'yakadanda-jobadder'), '<a href="https://codex.wordpress.org/Changing_File_Permissions">Changing File Permissions</a>');?></p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">
            <label for="cron-url"><?php _e('Cron (optional)', 'yakadanda-jobadder'); ?></label>
          </th>
          <td>
            <p><a id="cron-url" data-url="<?php echo $cron_url = home_url('/?passphrase='); ?>" href="<?php echo $cron_url . $passphrase; ?>" target="_blank"><?php echo $cron_url . $passphrase; ?></a></p>
            <p class="description"><?php _e('Yakadanda JobAdder running thrice daily event to process xml file from JobAdder. If you want real cron job, use link above as URL to call.', 'yakadanda-jobadder'); ?></p>
          </td>
        </tr>
      </tbody>
    </table>
    <h2><?php _e('Dashboard Widget', 'yakadanda-jobadder'); ?></h2>
    <p></p>
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row">
            <label for="tz"><?php _e('Time Zone', 'yakadanda-jobadder'); ?></label>
          </th>
          <td>
            <select id="tz" name="tz">
              <option value="0"><?php _e('Please, select time zone', 'yakadanda-jobadder'); ?></option>
              <?php foreach(yakadanda_jobadder_tz_list() as $t): ?>
                <?php $selected = ($t['zone'] == $tz) ? 'selected' : NULL; ?>
                <option value="<?php print $t['zone'] ?>" <?php echo $selected; ?>>
                  <?php print $t['diff_from_GMT'] . ' - ' . $t['zone'] ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p class="description"><?php _e('Last update XML file timezone.', 'yakadanda-jobadder'); ?></p>
          </td>
        </tr>
      </tbody>
    </table>
    <h2><?php _e('Notification', 'yakadanda-jobadder'); ?></h2>
    <p><?php _e('Send XML file as attachment and information to email for notification (optional).', 'yakadanda-jobadder'); ?></p>
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row">
            <label for="sender"><?php _e('Sender Email', 'yakadanda-jobadder'); ?></label>
          </th>
          <td>
            <input id="sender" name="sender" type="email" class="regular-text" value="<?php echo $sender; ?>"/>
            <p class="description"><?php printf(__('Leave blank if sender email as %s', 'yakadanda-jobadder'), '<code>' . $admin_email . '</code>'); ?></p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">
            <label for="email"><?php _e('Recipient Email', 'yakadanda-jobadder'); ?></label>
          </th>
          <td>
            <input id="email" name="email" type="email" class="regular-text" value="<?php echo $email; ?>"/>
            <p class="description"><?php _e('Email address for notification.', 'yakadanda-jobadder'); ?></p>
          </td>
        </tr>
      </tbody>
    </table>
    <input type="hidden" name="update_settings" value="1" />
    <?php wp_nonce_field( md5(YAKADANDA_JOBADDER_PLUGIN_DIR), 'yakadanda_jobadder_settings_nonce' ); ?>
    <p class="submit">
      <input type="submit" value="<?php _e('Save Changes', 'yakadanda-jobadder'); ?>" class="button button-primary" id="submit" name="submit">
    </p>
  </form>
</div>
