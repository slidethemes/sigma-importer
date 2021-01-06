<?php
/**
 * Class SS_File_Manager
 *
 * Class responsible for reading/writing in the uploads dir of WordPress
 *
 * @author     Slidesigma
 * @copyright  (c) Copyright by Slidesigma
 * @link       http://www.slidesigma.com
 * @since      1.0.0
 */

class SS_File_Manager{

  protected static $_fileSystemDefault = null;

  /**
  * Instantiate WP_Filesystem_Direct
  *
  * @return WP_Filesystem_Direct an instance of WP_Filesystem_Direct
  *
  * @since 1.0.0
  */
  public static function get_file_system(){
    if( is_null( self::$_fileSystemDefault ) ) {
      self::load_WpFileSystemDirect();
      self::$_fileSystemDefault = new WP_Filesystem_Direct( array() );
    }
    return self::$_fileSystemDefault;
  }

  /**
  * Load the WP_Filesystem_Direct class
  *
  * @since 1.0.0
  */
  private static function load_WpFileSystemDirect(){
    if ( !class_exists( 'WP_Filesystem_Base' ) ) {
      require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/class-wp-filesystem-base.php' );
    }
    if ( !class_exists( 'WP_Filesystem_Direct' ) ) {
      require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/class-wp-filesystem-direct.php' );
    }
    if( ! defined('FS_CHMOD_DIR') ) {
      define( 'FS_CHMOD_DIR', ( 0755 & ~umask() ) );
    }
    if( ! defined('FS_CHMOD_FILE') ) {
      define( 'FS_CHMOD_FILE', ( 0644 & ~umask() ) );
    }
  }

  /**
  * Get Upload Directory.
  *
  * @since 1.0.0
  */
  private static function get_upload_dir_path(){
    $wp_uploadsDir = wp_upload_dir();
    $dirPath = '';
    if(is_array($wp_uploadsDir)){
      if(isset($wp_uploadsDir['basedir'])) {
        return trailingslashit( $wp_uploadsDir['basedir'] );
      }
    }
    return $dirPath;
  }

  /**
	 * Write content to a file.
	 *
	 * @param string $content content to be saved to the file.
	 * @param string $file_path file path where the content should be saved.
	 * @return string|WP_Error path to the saved file or WP_Error object with error message.
	 */
	public static function write_to_file( $content, $file_path ) {

		// Verify WP file-system credentials.
		$verified_credentials = self::check_wp_filesystem_credentials();

		if ( is_wp_error( $verified_credentials ) ) {
			return $verified_credentials;
		}

		if ( ! self::get_file_system()->put_contents( $file_path, $content ) ) {
			return new \WP_Error(
				'failed_writing_file_to_server',
				sprintf(
					__( 'An error occurred while writing file to your server! Tried to write a file to: %s%s.', 'ss-importer' ),
					'<br>',
					$file_path
				)
			);
		}

		// Return the file path on successful file write.
		return $file_path;
	}

  /**
	 * Append content to the file.
	 *
	 * @param string $content content to be saved to the file.
	 * @param string $file_path file path where the content should be saved.
	 * @param string $separator_text separates the existing content of the file with the new content.
	 * @return boolean|WP_Error, path to the saved file or WP_Error object with error message.
	 */
	public static function append_to_file( $content, $file_path, $separator_text = '' ) {
		// Verify WP file-system credentials.
		$verified_credentials = self::check_wp_filesystem_credentials();

		if ( is_wp_error( $verified_credentials ) ) {
			return $verified_credentials;
		}

		$existing_data = '';
		if ( file_exists( $file_path ) ) {
			$existing_data = self::get_file_system()->get_contents( $file_path );
		}

		// Style separator.
		$separator = PHP_EOL . '---' . $separator_text . '---' . PHP_EOL;

		if ( ! self::get_file_system()->put_contents( $file_path, $existing_data . $separator . $content . PHP_EOL ) ) {
			return new \WP_Error(
				'failed_writing_file_to_server',
				sprintf(
					__( 'An error occurred while writing file to your server! Tried to write a file to: %s%s.', 'ss-importer' ),
					'<br>',
					$file_path
				)
			);
		}

		return true;
	}

  /**
	 * Get log file path
	 *
	 * @return string, path to the log file
	 */
	public static function get_log_path() {
		$upload_dir  = wp_upload_dir();
		$upload_path = apply_filters( 'ss_importer/upload_dir_path', trailingslashit( $upload_dir['path'] ) );

		$log_path = $upload_path . apply_filters( 'ss_importer/log_file_prefix', 'ss_importer_log_' ) . SS_Helper::$import_start_time . apply_filters( 'ss_importer/log_file_suffix', '.txt' );

		self::register_file_as_media_attachment( $log_path );

		return $log_path;
	}

  /**
	 * Register file as attachment to the Media page.
	 *
	 * @param string $log_path log file path.
	 * @return void
	 */
	public static function register_file_as_media_attachment( $log_path ) {
		// Check the type of file.
		$log_mimes = array( 'txt' => 'text/plain' );
		$filetype  = wp_check_filetype( basename( $log_path ), apply_filters( 'ss_importer/accepted_file_mime_types', $log_mimes ) );

		// Prepare an array of post data for the attachment.
		$attachment = array(
			'guid'           => self::get_log_url( $log_path ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => apply_filters( 'ss_importer/log_attachment_prefix', esc_html__( 'Sigma Importer - ', 'ss-importer' ) ) . preg_replace( '/\.[^.]+$/', '', basename( $log_path ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Insert the file as attachment in Media page.
		$attach_id = wp_insert_attachment( $attachment, $log_path );
	}

  /**
   * Get log file url
   *
   * @param string $log_path log path to use for the log filename.
   * @return string, url to the log file.
   */
  public static function get_log_url( $log_path ) {
    $upload_dir = wp_upload_dir();
    $upload_url = apply_filters( 'ss_importer/upload_dir_url', trailingslashit( $upload_dir['url'] ) );

    return $upload_url . basename( $log_path );
  }


  /**
	 * Get data from a file
	 *
	 * @param string $file_path file path where the content should be saved.
	 * @return string $data, content of the file or WP_Error object with error message.
	 */
	public static function get_from_file( $file_path ) {
		// Verify WP file-system credentials.
		$verified_credentials = self::check_wp_filesystem_credentials();

		if ( is_wp_error( $verified_credentials ) ) {
			return $verified_credentials;
		}

		$data = self::get_file_system()->get_contents( $file_path );

		if ( ! $data ) {
			return new \WP_Error(
				'failed_reading_file_from_server',
				sprintf(
					__( 'An error occurred while reading a file from your server! Tried reading file from path: %s%s.', 'ss-importer' ),
					'<br>',
					$file_path
				)
			);
		}

		// Return the file data.
		return $data;
	}

  /**
	 * Helper function: check for WP file-system credentials needed for reading and writing to a file.
	 *
	 * @return boolean|WP_Error
	 */
	private static function check_wp_filesystem_credentials() {
		// Check if the file-system method is 'direct', if not display an error.
		if ( ! ( 'direct' === get_filesystem_method() ) ) {
			return new \WP_Error(
				'no_direct_file_access',
				sprintf(
					__( 'This WordPress page does not have %sdirect%s write file access. This plugin needs it in order to save the demo import xml file to the upload directory of your site. You can change this setting with these instructions: %s.', 'ss-importer' ),
					'<strong>',
					'</strong>',
					'<a href="https://wordpress.org/support/article/editing-wp-config-php/" target="_blank">How to set <strong>direct</strong> filesystem method in your wp-config file.</a>'
				)
			);
		}

		// Get plugin page settings.
    $plugin_page_obj = apply_filters( 'ss_importer/plugin_page_obj', array(
      'parent_slug' => 'themes.php',
      'page_title'  => esc_html__( 'Sigma Importer' , 'ss-importer' ),
      'menu_title'  => esc_html__( 'Demo Importer' , 'ss-importer' ),
      'capability'  => 'import',
      'menu_slug'   => 'ss-importer',
    ));

		// Get user credentials for WP file-system API.
		$demo_import_page_url = wp_nonce_url( $plugin_page_obj['parent_slug'] . '?page=' . $plugin_page_obj['menu_slug'], $plugin_page_obj['menu_slug'] );

		if ( false === ( $creds = request_filesystem_credentials( $demo_import_page_url, '', false, false, null ) ) ) {
			return new \WP_error(
				'filesystem_credentials_could_not_be_retrieved',
				__( 'An error occurred while retrieving reading/writing permissions to your server (could not retrieve WP filesystem credentials)!', 'ss-importer' )
			);
		}

		// Now we have credentials, try to get the wp_filesystem running.
		if ( ! WP_Filesystem( $creds ) ) {
			return new \WP_Error(
				'wrong_login_credentials',
				__( 'Your WordPress login credentials don\'t allow to use WP_Filesystem!', 'ss-importer' )
			);
		}

		return true;
	}


}
