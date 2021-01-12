<?php
/**
 * Class SS_Helper
 *
 * Class responsible for providing utility methods to be used across the plugin
 *
 * @author     Slidesigma
 * @copyright  (c) Copyright by Slidesigma
 * @link       http://www.slidesigma.com
 * @since      1.0.0
 */

class SS_Helper{

  public static $import_start_time;

  /**
	 * Set the $import_start_time class variable with the current date and time string.
   *
   * @since 1.0.0
	 */
	public static function set_import_start_time() {
		self::$import_start_time = date( apply_filters( 'ss_importer/date_format_for_file_names', 'Y-m-d__H-i-s' ) );
    set_transient( 'ss_importer/import_start_time', self::$import_start_time, 0.1 * HOUR_IN_SECONDS );
	}

  /**
	 * Get the $import_start_time class variable with the current date and time string.
   *
   * @since 1.0.0
	 */
  public static function get_import_start_time(){
    return get_transient('ss_importer/import_start_time');
  }

  /**
   * Set the status of our import data for the try_renew_heartbeat method
   *
   * @since 1.0.0
   */
  public static function set_import_data( $data ){
    set_transient( 'ss_importer/import_data', $data, 0.1 * HOUR_IN_SECONDS );
  }

  /**
   * Get the status of the previous try_renew_heartbeat
   *
   * @since 1.0.0
   */
  public static function get_import_data(){
    return get_transient( 'ss_importer/import_data' );
  }

  /**
	 * Get import file information and max execution time.
	 *
	 * @param array $selected_import_files array of selected import files.
	 */
	public static function import_file_info( $selected_import_files ) {
		$redux_file_string = '';

		if ( ! empty( $selected_import_files['import_redux'] ) ) {
			$redux_file_string = array_reduce( $selected_import_files['import_redux'], function( $string, $item ) {
				return sprintf( '%1$s%2$s -> %3$s %4$s', $string, $item['opt_name'], $item['import_redux_file'], PHP_EOL );
			}, '' );
		}

		return PHP_EOL .
		sprintf(
			__( 'Initial max execution time = %s seconds', 'ss-importer' ),
			ini_get( 'max_execution_time' )
		) . PHP_EOL .
		sprintf(
			__( 'Files info:%1$sSite URL = %2$s%1$sContent file = %3$s%1$sWidget file = %4$s%1$sRedux files:%1$s%5$s', 'ss-importer' ),
			PHP_EOL,
			get_site_url(),
			empty( $selected_import_files['import_file'] ) ? esc_html__( 'not defined!', 'ss-importer' ) : $selected_import_files['import_file'],
			empty( $selected_import_files['import_widget_file'] ) ? esc_html__( 'not defined!', 'ss-importer' ) : $selected_import_files['import_widget_file'],
			empty( $redux_file_string ) ? esc_html__( 'not defined!', 'ss-importer' ) : $redux_file_string
		);
	}

  /**
  * Return a list of valid files only
  *
  * @param array $import_files The custom files added by theme developer
  *
  * @since 1.0.0
  */
  public static function validate_content_files( $import_files ){
    return array_filter( $import_files, array( 'SS_Helper', 'validate_content_file' ));
  }

  /**
  * Filters the files
  *
  * @param array $file_info The individual content file
  *
  * @since 1.0.0
  */
  private static function validate_content_file( $file_info ){

    // Check if the file name & .xml file are not empty
    return (isset( $file_info['file_name'] ) && !empty( $file_info['file_name'] )) && isset( $file_info['import_file'] ) && !empty( $file_info['import_file'] );

  }

  /**
  * Return the thumbnail of the demo. Note that this function will return the theme screenshot if no image was provided
  *
  * @param array $demo The demo that is being queried
  *
  * @since 1.0.0
  */
  public static function get_demo_thumb( $demo ){

    $preview_thumb = isset( $demo['preview_thumb'] ) ? $demo['preview_thumb'] : '';

    // Fallback to return theme screenshot if no image was provided
    if ( empty( $preview_thumb ) ) {
      $theme = wp_get_theme();
      $preview_thumb = $theme->get_screenshot();
    }

    return $preview_thumb;

  }

  /**
  * Return a new array of fitlered categories for all demos
  *
  * @param array $demos The list of demos in which we want to filter the categories.
  *
  * @since 1.0.0
  */
  public static function format_demos_categories( $demos ){

    $categories = [];

		foreach ( $demos as $demo ) {
			if ( isset($demo['categories']) && !empty( $demo['categories'] ) && is_array( $demo['categories'] ) ) {
				foreach ( $demo['categories'] as $category ) {
					$categories[sanitize_key($category)] = $category;
				}
			}
		}

		return !empty( $categories ) ? $categories : false;

  }

  /**
  * Return a new array of fitlered categories for a single demo
  *
  * @param array $demos The list of demos in which we want to filter the categories.
  *
  * @since 1.0.0
  */
  public static function format_demo_item_categories( $demo ){

    $categories = [];

    if ( isset($demo['categories']) && !empty( $demo['categories'] ) && is_array( $demo['categories'] ) ) {
      foreach ( $demo['categories'] as $category ) {
        $categories[] = sanitize_key($category);
      }
    }

    return !empty( $categories ) ? implode(" ", $categories) : false;

  }

  /**
  * Check if TGMPA class exists
  *
  * @since 1.0.0
  */
  public static function is_tgmpa_active(){
    return did_action('tgmpa_register');
  }

  /**
  * Check if RevSlider class exists
  *
  * @since 1.0.0
  */
  public static function is_rev_slider_active(){
    return class_exists('RevSlider');
  }

  /**
  * Check if ReduxFramework class exists
  *
  * @since 1.0.0
  */
  public static function is_redux_active(){
    return class_exists('ReduxFramework');
  }

}
