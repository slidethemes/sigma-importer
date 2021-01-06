<?php
/**
 * Class SS_Importer_System
 *
 * Class responsible for handling all functions for the System Status
 *
 * @author     Slidesigma
 * @copyright  (c) Copyright by Slidesigma
 * @link       http://www.slidesigma.com
 * @since      1.0.0
 */

class SS_Importer_System{

  /**
   * Array that hold all the data for system status.
   *
   * @since    1.0.0
   * @access   public
   * @var      string    $system_status    Array that hold all the data for system status.
   */
  public $system_status;

  function __construct() {

    add_action('ss_importer/before_import_page', array($this, 'get_system_status_content'), 10);
    add_action('ss_importer/before_import_page', array($this, 'get_system_status_button'), 20);

  }

  public function get_system_status(){

    global $wpdb, $wp_version;

    $this->system_status = array(
      'theme_version'       => SS_Importer()->theme_version,
      'active_theme'        => SS_Importer()->theme_name,
      'php_version'         => phpversion(),
      'memory'              => wp_convert_hr_to_bytes( ini_get('memory_limit') ),
      'max_execution_time'  => ini_get('max_execution_time'),
      'max_upload_size'     => wp_max_upload_size() ,
      'post_max_size'       => wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) ) ,
      'zip_archive'         => class_exists( 'ZipArchive' ),
      'wp_debug'            => defined('WP_DEBUG'),
      'language'            => get_locale(),
      'multisite'           => is_multisite(),
      'site_url'            => get_site_url(),
      'home_url'            => get_home_url(),
      'wpdb'                => $wpdb->db_version(),
      'wp_version'          => $wp_version,
    );

    return apply_filters( 'ss_importer/system_status_obj', $this->system_status );

  }

  /**
  * Return the button that will trigger system status.
  *
  * @since 1.0.0
  */
  public function get_system_status_button(){
    ob_start();
    ?>
    <div class="text-right">
      <button class="button button-primary button-large trigger-system-status"><?php esc_html_e('System Status', 'ss-importer') ?></button>
    </div>
    <?php
    $system_button = ob_get_clean();
    echo apply_filters( 'ss_importer/system_status_button', $system_button );
  }

  /**
  * Populate the content for each admin menu page.
  *
  * @since 1.0.0
  */
  public function get_system_status_content(){
    require_once SS_IMPORTER_PATH . 'templates/system-status.php';
  }

}
