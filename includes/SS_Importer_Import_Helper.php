<?php
/**
 * Class SS_Importer_Import_Helper
 *
 * Class responsible for providing helper methods & properties for SS_Importer_Import class
 *
 * @author     Slidesigma
 * @copyright  (c) Copyright by Slidesigma
 * @link       http://www.slidesigma.com
 * @since      1.0.0
 */

class SS_Importer_Import_Helper{

  /**
   * The object which will be passed to add_submenu_page()
   *
   * @since    1.0.0
   * @access   public
   * @var      string
   */
  public $plugin_page_obj;

  /**
   * The result of add_submenu_page
   *
   * @since    1.0.0
   * @access   public
   * @var      string/boolean
   */
  public $plugin_page;

  /**
   * List of notices that will be displayed to the user PRIOR to import
   *
   * @since    1.0.0
   * @access   public
   * @var      array
   */
  public $notices;

  /**
	 * Time in milliseconds. This will be used to help us create new ajax calls after a set time interval
	 *
	 * @var float
	 */
  protected $microtime;

  /**
   * Holds the final response of the import process
   *
   * @since    1.0.0
   * @access   public
   * @var      string
   */
  public $response;

  /**
	 * The index of the selected demo
	 *
	 * @var int
	 */
  public $selected_demo_index;

  /**
  * Create the admin back-end menus.
  *
  * @since 1.0.0
  */
  public function admin_menus(){

    $this->plugin_page_obj = apply_filters( 'ss_importer/plugin_page_obj', array(
      'parent_slug' => 'themes.php',
      'page_title'  => esc_html__( 'Sigma Importer' , 'ss-importer' ),
      'menu_title'  => esc_html__( 'Demo Importer' , 'ss-importer' ),
      'capability'  => 'import',
      'menu_slug'   => 'ss-importer',
    ));

    $this->plugin_page = add_submenu_page(
      $this->plugin_page_obj['parent_slug'],
      $this->plugin_page_obj['page_title'],
      $this->plugin_page_obj['menu_title'],
      $this->plugin_page_obj['capability'],
      $this->plugin_page_obj['menu_slug'],
      apply_filters( 'ss_importer/importer_page_cb', array( $this, 'page_content' ) )
    );

    register_importer( $this->plugin_page_obj['menu_slug'], $this->plugin_page_obj['page_title'], $this->plugin_page_obj['menu_title'], apply_filters( 'sigma_importer/importer_page_cb', array( $this, 'page_content' ) ) );

  }

  /**
  * Return the list of warnings
  *
  * @since 1.0.0
  */
  public function get_notices(){

    if ( ini_get( 'safe_mode' ) ) {
      $this->notices[] = esc_html__('Your server is running PHP on safe mode. You might have some issues while importing a demo. To disable safe mode, please contact your hosting provider', 'ss-importer');
  	}
    if ( empty( $this->get_demos() ) ){
      $this->notices[] = esc_html__('The theme doesn\'t support any custom import files. Please use the manual importer to get started.', 'ss-importer');
    }

    return apply_filters( 'ss_importer/importer_prerequisite_notices' , $this->notices );

  }

  /**
  * Return the list of custom demos defined by theme developer
  *
  * @since 1.0.0
  */
  public function get_demos(){

    return SS_Helper::validate_content_files( apply_filters( 'ss_importer/importer_custom_demos' , [] ) );

  }

  /**
  * If manual content was provided, then we return the the same object, since we can only have 1 demo in a manual process,
  * otherwise return the demo from the list of custom demos based on key
  *
  * @param int/array $key - The index of the demo selected, or an array object of manually selected content files
  *
  * @since 1.0.0
  */
  protected function get_selected_demo( $key ){
    return is_array( $key ) ? $key : $this->get_demos()[$key];
  }

  protected function get_demo_name( $demo ){
    return isset($demo['file_name']) && !empty($demo['file_name']) ? $demo['file_name'] : '';
  }
  protected function get_xml_file( $demo ){
    return isset($demo['import_file']) && !empty($demo['import_file']) ? $demo['import_file'] : '';
  }
  protected function get_json_file( $demo ){
    return isset( $demo['import_redux'][0]['import_redux_file']) && !empty($demo['import_redux'][0]['import_redux_file']) ? $demo['import_redux'][0]['import_redux_file'] : '';
  }
  protected function get_dat_file( $demo ){
    return isset($demo['import_customizer_file']) && !empty($demo['import_customizer_file']) ? $demo['import_customizer_file'] : '';
  }
  protected function get_wie_file( $demo ){
    return isset($demo['import_widget_file']) && !empty($demo['import_widget_file']) ? $demo['import_widget_file'] : '';
  }
  protected function get_sliders( $demo ){
    return isset($demo['sliders']) && !empty($demo['sliders']) ? $demo['sliders'] : '';
  }
  protected function get_opt_name( $demo ){
    return isset( $demo['import_redux'][0]['opt_name']) && !empty($demo['import_redux'][0]['opt_name']) ? $demo['import_redux'][0]['opt_name'] : '';
  }
  protected function get_front_page( $demo ){
    return isset( $demo['front_page']) && !empty($demo['front_page']) ? $demo['front_page'] : '';
  }
  protected function get_blog_page( $demo ){
    return isset( $demo['blog_page']) && !empty($demo['blog_page']) ? $demo['blog_page'] : '';
  }
  protected function get_menus( $demo ){
    return isset( $demo['menus']) && !empty($demo['menus']) ? $demo['menus'] : '';
  }

  /**
  * Process the manually uploaded files and populate the demo_files object
  *
  * @param array $files - The files uploaded manually
  * @param string $log_path - Path to the log file
  *
  * @since 1.0.0
  */
  protected function set_manual_import_files( $files, $log_path ){

    // Upload settings to disable form and type testing for AJAX uploads.
		$upload_overrides = array(
			'test_form' => false,
			'test_type' => false,
		);

    $demo_files = [];

		// Handle demo file uploads.
		$content_file = isset( $_FILES['content_file'] ) ? wp_handle_upload( $_FILES['content_file'], $upload_overrides ) : array( 'error'  => esc_html__('No content file provided.', 'ss-importer') );
    $widget_file = isset( $_FILES['widget_file'] ) ? wp_handle_upload( $_FILES['widget_file'], $upload_overrides ) : array( 'error'  => esc_html__('No .wie file provided.', 'ss-importer') );

		$redux_file = isset( $_FILES['redux_file'] ) ? wp_handle_upload( $_FILES['redux_file'], $upload_overrides ) : array( 'error'  => esc_html__('No .json file provided.', 'ss-importer') );
    $redux_opt_name = isset( $_POST['redux_opt_name'] ) ? sanitize_text_field($_POST['redux_opt_name']) : '';

    /*=====================
    * PROCESS CONTENT FILE
    ======================*/
		if ( $content_file && !isset( $content_file['error'] ) ) {

			// Set uploaded content file.
			$demo_files['import_file'] = $content_file['file'];

		} else {

			SS_File_Manager::append_to_file(
				sprintf(
					__( 'Content file was not uploaded. Error: %s', 'ss-importer' ),
					$content_file['error']
				),
				$log_path,
				esc_html__( 'Content file Error' , 'ss-importer' )
			);

		}

    /*=====================
    * PROCESS WIDGET FILE
    ======================*/
		if ( $widget_file && !isset( $widget_file['error'] ) ) {

			// Set uploaded widget file.
			$demo_files['import_widget_file'] = $widget_file['file'];

		} else {

      SS_File_Manager::append_to_file(
				sprintf(
					__( 'Widget file was not uploaded. Error: %s', 'ss-importer' ),
					$widget_file['error']
				),
				$log_path,
        esc_html__( 'Widget file Error' , 'ss-importer' )
			);

		}

    /*=====================
    * PROCESS REDUX FILE
    ======================*/
		if ( $redux_file && !isset( $redux_file['error'] ) ) {

			if ( isset( $_POST['redux_opt_name'] ) && empty( $redux_opt_name ) ) {

        // @TODO: LOG TO THE BUFFER
        SS_File_Manager::append_to_file(
  				__( 'Redux file provided, but redux options name was not.', 'ss-importer' ),
  				$log_path,
          esc_html__( 'Redux file Error' , 'ss-importer' )
  			);

			}

			// Set uploaded Redux file.
			$demo_files['import_redux'] = array(
				array(
          'import_redux_file' => $redux_file['file'],
					'opt_name' => $redux_opt_name,
				),
			);

		} else {

      SS_File_Manager::append_to_file(
				sprintf(
					__( 'Redux Options file was not uploaded. Error: %s', 'ss-importer' ),
					$redux_file['error']
				),
				$log_path,
        esc_html__( 'Redux file Error' , 'ss-importer' )
			);

		}

    return $demo_files;

  }

  /**
  * The content for the importer page.
  *
  * @since 1.0.0
  */
  public function page_content(){

    require_once SS_IMPORTER_PATH . 'templates/page-import.php';

  }

}
