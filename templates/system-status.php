<?php $status = SS_Importer()->ss_system->get_system_status(); ?>

<div class="ss_importer-system-status">

  <div class="ss_close trigger-system-status">
    <span></span>
    <span></span>
  </div>

  <div id="ss_importer-report-wrap">

    <table class="ss_importer-table">
      <thead>
        <tr>
          <th data-environment="<?php echo esc_attr__('Theme Status', 'ss-importer') ?>"><?php esc_html_e('General', 'ss-importer') ?></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td data-title="<?php echo esc_attr__('Theme Name: ', 'ss-importer') ?>"><?php esc_html_e('Theme Name: ', 'ss-importer') ?></td>
          <td><?php echo esc_html($status['active_theme']) ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('Current Version: ', 'ss-importer') ?>"><?php esc_html_e('Current Version: ', 'ss-importer') ?></td>
          <td><?php echo esc_html($status['theme_version']) ?></td>
        </tr>
      </tbody>
    </table>

    <table class="ss_importer-table">
      <thead>
        <tr>
          <th data-environment="<?php echo esc_attr__('Wordpress Status', 'ss-importer') ?>"><?php esc_html_e('Wordpress Status', 'ss-importer') ?></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td data-title="<?php echo esc_attr__('Home URL: ', 'ss-importer') ?>"><?php esc_html_e('Home URL: ', 'ss-importer') ?></td>
          <td><?php echo esc_html($status['home_url']); ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('Site URL: ', 'ss-importer') ?>"><?php esc_html_e('Site URL: ', 'ss-importer') ?></td>
          <td><?php echo esc_html($status['site_url']); ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('WP Version: ', 'ss-importer') ?>"><?php esc_html_e('WP Version: ', 'ss-importer') ?></td>
          <td><?php echo esc_html($status['wp_version']); ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('WP Multisite: ', 'ss-importer') ?>"><?php esc_html_e('WP Multisite: ', 'ss-importer') ?></td>
          <?php $multisite = $status['multisite'] == 1 ? 'On' : 'Off' ?>
          <td><?php echo esc_html($multisite) ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('WP Debug Mode: ', 'ss-importer') ?>"><?php esc_html_e('WP Debug Mode: ', 'ss-importer') ?></td>
          <?php $wp_debug = $status['wp_debug'] == 1 ? 'On' : 'Off' ?>
          <td><?php echo esc_html($wp_debug) ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('Language: ', 'ss-importer') ?>"><?php esc_html_e('Language: ', 'ss-importer') ?></td>
          <td><?php echo esc_html($status['language']) ?></td>
        </tr>
      </tbody>
    </table>

    <table class="ss_importer-table">
      <thead>
        <tr>
          <th data-environment="<?php echo esc_attr__('Server Status', 'ss-importer') ?>"><?php esc_html_e('Server Status (PHP)', 'ss-importer') ?></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td data-title="<?php echo esc_attr__('PHP Memory Limit: ', 'ss-importer') ?>"><?php esc_html_e('PHP Memory Limit: ', 'ss-importer') ?></td>
          <td><?php echo esc_html(size_format($status['memory'])) ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('PHP Time Limit: ', 'ss-importer') ?>"><?php esc_html_e('PHP Time Limit: ', 'ss-importer') ?></td>
          <td><?php echo esc_html($status['max_execution_time']) ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('PHP Version: ', 'ss-importer') ?>"><?php esc_html_e('PHP Version: ', 'ss-importer') ?></td>
          <td><?php echo esc_html($status['php_version']) ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('PHP Post Max Size: ', 'ss-importer') ?>"><?php esc_html_e('PHP Post Max Size:	', 'ss-importer') ?></td>
          <td><?php echo esc_html(size_format($status['post_max_size'])); ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('ZipArchive: ', 'ss-importer') ?>"><?php esc_html_e('ZipArchive: ', 'ss-importer') ?></td>
          <?php $zip_archive = $status['zip_archive'] == 1 ? 'On' : 'Off' ?>
          <td><?php echo esc_html($zip_archive) ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('MySQL Version: ', 'ss-importer') ?>"><?php esc_html_e('MySQL Version: ', 'ss-importer') ?></td>
          <td><?php echo esc_html($status['wpdb']) ?></td>
        </tr>
        <tr>
          <td data-title="<?php echo esc_attr__('Max Upload Size: ', 'ss-importer') ?>"><?php esc_html_e('Max Upload Size:	', 'ss-importer') ?></td>
          <td><?php echo esc_html(size_format($status['max_upload_size'])); ?></td>
        </tr>
      </tbody>
    </table>

  </div>

</div>
