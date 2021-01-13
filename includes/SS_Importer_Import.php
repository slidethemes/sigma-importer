<?php
/**
 * Class SS_Importer_Import
 *
 * Class responsible for demo import.
 *
 * @author     Slidesigma
 * @copyright  (c) Copyright by Slidesigma
 * @link       http://www.slidesigma.com
 * @since      1.0.0
 */

class SS_Importer_Import extends SS_Importer_Import_Helper{

  /**
   * Hold a reference to the WP_Importer Class
   *
   * @since    1.0.0
   * @access   public
   */
  public $wp_import;

  /**
   * Hold the path of the log file
   *
   * @since    1.0.0
   * @access   public
   */
  public $log_path;

  function __construct() {

    $this->demo_files = [];

    $this->init_actions();
    $this->load_wp_importer_files();

    add_action('admin_menu', array($this, 'admin_menus'));

  }

  /**
   * Initialize all action that makes the Importer function properly.
   *
   * @since    1.0.0
   * @access   public
   */
  public function init_actions(){

    add_action( 'wp_ajax_ss_importer_pre_processing', array($this, 'pre_processing') );
    add_action( 'wp_ajax_import_content', array($this, 'import_content') );

    add_action( 'ss_importer/after_import_content', array($this, 'import_widgets'), 10 );
    add_action( 'ss_importer/after_import_content', array($this, 'import_theme_opts'), 20 );
    add_action( 'ss_importer/after_import_content', array($this, 'import_sliders'), 30 );
    add_action( 'ss_importer/after_import_content', array($this, 'assign_front_page'), 40 );
    add_action( 'ss_importer/after_import_content', array($this, 'assign_blog'), 50 );
    add_action( 'ss_importer/after_import_content', array($this, 'assign_menus'), 60 );

  }

  /**
   * Load the WordPress importer files.
   *
   * @since 1.0.0
   */
  public function load_wp_importer_files(){

    require_once SS_IMPORTER_PATH . 'includes/wp-importer/wordpress-importer.php';

  }

  /**
   * Prepares the files for import, and initializes the logger to see what files are missing and what arent.
   *
   * @since 1.0.0
   */
  public function pre_processing(){

    // Check Ajax nonce
    check_ajax_referer( 'ss-importer-ajax-nonce', 'security' );

    // Try and increase the php memory limit to avoid having the importer crashing
    ini_set( 'memory_limit', apply_filters( 'ss_importer/php/memory_limit', '350M' ) );

    if( !SS_Helper::get_import_start_time() ){

      // Set the start time of the importer
      SS_Helper::set_import_start_time();

      // Execute custom user action
      do_action('ss_importer/before_import_content');

      // Get the log path
      $this->log_path = SS_File_Manager::get_log_path();

      $this->selected_demo_index = !empty( $_FILES ) ? $this->set_manual_import_files( $_FILES, $this->log_path ) : sanitize_text_field( $_POST['selectedDemo'] );

      // Are we using predefined import files?
      if ( empty( $_FILES ) && !empty( $this->get_selected_demo( $this->selected_demo_index ) ) ) {

        // Add this message to log file.
        SS_File_Manager::append_to_file(
          sprintf(
            __( 'The import files for %s were used.', 'ss-importer' ),
            $this->get_demo_name( $this->get_selected_demo( $this->selected_demo_index ) )
          ). SS_Helper::import_file_info( $this->get_selected_demo( $this->selected_demo_index ) ),
          $this->log_path,
          esc_html__( 'Demo Import files' , 'ss-importer' )
        );

      } else if( empty( $_FILES ) ) {
        // Send JSON Error response to the AJAX call.
        $this->response = array(
          'status'  => 'error',
          'message' => esc_html__( 'No import files specified!', 'ss-importer' )
        );
        wp_send_json( $this->response );
        die();
      }

      $data = [
        'log_path' => $this->log_path,
        'selected_demo_index' => $this->selected_demo_index
      ];

      SS_Helper::set_import_data( $data );

    }else{

      $data = SS_Helper::get_import_data();
      $this->selected_demo_index = $data['selected_demo_index'];
      $this->log_path = $data['log_path'];

    }

    $this->import_content( $this->get_xml_file( $this->get_selected_demo( $this->selected_demo_index ) ) );

  }

  /**
   * Imports the .xml file (Content)
   *
   * @since 1.0.0
   */
  public function import_content( $import_file ){

    $this->microtime = microtime( true );

    // Increase PHP max execution time if configuration allows it
		if ( strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) === false ) {
			set_time_limit( apply_filters( 'ss_importer/php/set_time_limit', 300 ) );
		}

		// Check, if we need to send another AJAX request and set the importing author to the current user.
		add_filter( 'wp_import_post_data_raw', array( $this, 'try_renew_heartbeat' ) );

		// Disables generation of multiple image sizes (thumbnails) in the content import step.
		if ( !apply_filters( 'ss_importer/import_default_images_only', true ) ) {
			add_filter( 'intermediate_image_sizes_advanced', '__return_null' );
		}

    // Options for import
    $options = apply_filters( 'ss_importer/import_options', array(
      'process_categories'  => true,
      'process_tags'        => true,
      'process_terms'       => true,
      'process_posts'       => true
    ));

    $logger = SS_Importer()->ss_logger;
    $this->wp_import = new WP_Import( $logger );
    $this->wp_import->fetch_attachments = apply_filters( 'ss_importer/fetch_attachments', true );

    if( $import_file ){
      $this->wp_import->import( $import_file, $options );
    }

    /**
     * @hooked import_widgets	- 10
     * @hooked import_theme_opts	- 20
     * @hooked import_sliders - 30
     * @hooked assign_front_page - 40
     * @hooked assign_blog - 50
     * @hooked assign_menus - 60
     */
    do_action( 'ss_importer/after_import_content', $this->get_selected_demo( $this->selected_demo_index ) );

    $this->import_end();

  }

  /**
   * The final step of the import process.
   *
   * @since 1.0.0
   */
  public function import_end(){

    $error_log_buffer = apply_filters( 'ss_importer/after_import_error_log_buffer', SS_Importer()->ss_logger->get_error_log_buffer() );

    $this->response['status'] = 'complete';

    $this->response['message'] = __( 'The demo import has finished, Happy blogging!', 'ss-importer' );

    //Add the error log buffer to the response
    if( !empty( $error_log_buffer ) ){
      $this->response['message'] = sprintf( __( 'The demo import has finished, however, some issues were found. You can check the status and the errors of the import process in this %1$slog file%2$s', 'ss-importer' ), '<a href="' . SS_File_Manager::get_log_url( $this->log_path ) .'" target="_blank">', '</a>' );
      $this->response['errors'] = $error_log_buffer;

      // Add the error log buffer to the log file.
      SS_File_Manager::append_to_file(
        str_replace('<br>', '', $error_log_buffer),
        $this->log_path,
        esc_html__( 'Errors' , 'ss-importer' )
      );
    }

    delete_transient('ss_importer/import_start_time');
    delete_transient('ss_importer/import_data');

    wp_send_json( $this->response );
    die();

  }

  /**
   * Imports the .wie file (Widgets)
   *
   * @since 1.0.0
   */
  public function import_widgets(){

    $widget_file = $this->get_wie_file( $this->get_selected_demo( $this->selected_demo_index ) );

    do_action('ss_importer/before_import_widgets', $widget_file);

    if($widget_file){
      $widgets_importer = new SS_Importer_Widgets;
      $widgets_importer::import( $widget_file, $this->log_path );
    }

  }

  /**
   * Imports the .json file (Theme Options)
   *
   * @since 1.0.0
   */
  public function import_theme_opts(){

    $options_file = $this->get_json_file( $this->get_selected_demo( $this->selected_demo_index ) );
    $opt_name = $this->get_opt_name( $this->get_selected_demo( $this->selected_demo_index ) );

    do_action('ss_importer/before_import_theme_opts', $options_file, $opt_name);

    if( $options_file && $opt_name && SS_Helper::is_redux_active() ){

      $result = $this->wp_import->import_theme_options( $options_file, $opt_name );

      if ( is_wp_error( $result ) ) {
        $error_message = $result->get_error_message();

        SS_Importer()->ss_logger->error( 'Redux import error: ' . $error_message );

        SS_File_Manager::append_to_file(
          $error_message,
          $this->log_path,
          esc_html__( 'Importing Redux' , 'ss-importer' )
        );

      }else{

        // Write error to log file.
  			SS_File_Manager::append_to_file(
  				sprintf(
            __('Redux options with option name "%s" imported successfully', 'ss-importer'),
            $opt_name
          ),
  				$this->log_path,
  				esc_html__( 'Importing Redux settings' , 'ss-importer' )
  			);

      }

    }elseif( !SS_Helper::is_redux_active() && ( $options_file || $opt_name ) ){

			$error_message = esc_html__( 'The Redux plugin is not activated, so the Redux import was skipped!', 'ss-importer' );

			SS_Importer()->ss_logger->warning( $error_message );

			// Write error to log file.
			SS_File_Manager::append_to_file(
				$error_message,
				$this->log_path,
				esc_html__( 'Importing Redux settings' , 'ss-importer' )
			);

			return false;
    }

  }

  /**
   * Imports the Slider Revolution sliders
   *
   * @since 1.0.0
   */
  public function import_sliders(){

    $sliders = $this->get_sliders( $this->get_selected_demo( $this->selected_demo_index ) );

    do_action('ss_importer/before_import_sliders', $sliders);

    if ( SS_Helper::is_rev_slider_active() && is_array($sliders) && !empty($sliders) ) {
			$rev_slider = new RevSlider();
			foreach($sliders as $slider){
				$rev_slider->importSliderFromPost( true, true, $slider );
			}
		}elseif( !SS_Helper::is_rev_slider_active() && is_array($sliders) ){

			$error_message = esc_html__( 'The Revolution plugin is not activated, so the Slider Revolution import was skipped!', 'ss-importer' );

			SS_Importer()->ss_logger->add_to_error_log_buffer( $error_message );

			// Write error to log file.
			SS_File_Manager::append_to_file(
				$error_message,
				$this->log_path,
				esc_html__( 'Importing Slider Revolution sliders' , 'ss-importer' )
			);

			return false;
    }


  }

  /**
   * Assigns the home page set from the import files object
   *
   * @since 1.0.0
   */
  public function assign_front_page(){

    $front_page = $this->get_front_page( $this->get_selected_demo( $this->selected_demo_index ) );

    do_action('ss_importer/before_assign_front_page', $front_page);

    if( $front_page = get_page_by_title( $front_page ) ){
  		update_option( 'show_on_front', 'page' );
  		update_option( 'page_on_front', $front_page->ID );
      return true;
    }
    return false;

  }

  /**
   * Assigns the blog page set from the import files object
   *
   * @since 1.0.0
   */
  public function assign_blog(){

    $blog_page = $this->get_blog_page( $this->get_selected_demo( $this->selected_demo_index ) );

    do_action('ss_importer/before_assign_blog', $blog_page);

    if( $blog_page = get_page_by_title( $blog_page ) ){
  		update_option( 'page_for_posts', $blog_page->ID );
      return true;
    }
    return false;

  }

  /**
   * Assigns the blog page set from the import files object
   *
   * @since 1.0.0
   */
  public function assign_menus(){

    $menus = $this->get_menus( $this->get_selected_demo( $this->selected_demo_index ) );

    do_action('ss_importer/before_assign_menus', $menus);

    if( is_array($menus) && !empty($menus) ){
      foreach( $menus as $key => $value ){

        if( $value ){
          $menu = get_term_by( 'name', $value, 'nav_menu' );
          $menu_id = $menu && !empty($menu) ? $menu->term_id : '';

          if( $menu_id ){
            $menus[$key] = $menu_id;
          }else{
            // TODO: Log that the menu ID doesnt exist
            unset($menus[$key]);
          }
        }else{
          // TODO: Log that the menu value is empty
          unset($menus[$key]);
        }

      }

      set_theme_mod( 'nav_menu_locations', $menus );

      return true;
    }
    return false;

  }

  /**
	 * Check if we need to create a new AJAX request, so that server does not timeout.
	 *
	 * @param array $data current post data.
	 * @return array
	 */
	public function try_renew_heartbeat( $post ) {

    $time = microtime( true ) - $this->microtime;
    $heartbeat = apply_filters( 'ss_importer/ajax_heartbeat', 25 );

		// We should make a new ajax call, if the time spent on 1 call is more than 25 seconds
		if ( $time > $heartbeat ) {

			$this->response = array(
				'status'  => 'heartbeat',
				'message' => esc_html__('Let\'s run another ajax call', 'ss-importer'),
			);

      $this->wp_import->set_processed_importer_data();

			// Send the request for a new AJAX call.
			wp_send_json( $this->response );
		}

		return $post;
	}

}
