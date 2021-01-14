<?php
/*
Plugin Name: Sigma Importer
Plugin URI: https://wordpress.org/plugins/sigma-importer/
Description: Import demo content with ease. Whether you are a developer adding one click demo import functionality to your theme, or you just want a quick import solution. This is the plugin for you.
Version: 1.0.2
Author: Slidesigma
Author URI: http://www.slidesigma.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Text Domain: ss-importer
*/

if(! defined('ABSPATH')){
  return;
}

/**
 * Class Sigma_Importer
 *
 * Sigma_Importer Core Plugin Class
 *
 * Sets up all Sigma_Importer portal functions and classes.
 *
 *
 * @author     Slidesigma
 * @copyright  (c) Copyright Slidesigma
 * @link       http://www.slidesigma.com
 * @since      1.0.0
 */

class Sigma_Importer{

  /**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
  const MINIMUM_PHP_VERSION = '5.5.0';

  /**
   * The name of the current active theme.
   *
   * @since    1.0.0
   * @access   public
   * @var      string    $theme_name    The name of the current active theme.
   */
  public $theme_name;

  /**
   * The version of the current theme,
   *
   * @since    1.0.0
   * @access   public
   * @var      string    $theme_version    The version of the current theme.
   */
  public $theme_version;

  /**
  * References the Sigma_Importer class.
  *
  * @since 1.0.0
  *
  */
  public static $_instance;

  /**
  * References the SS_Importer_System class.
  *
  * @since 1.0.0
  *
  * @var SS_Importer_System
  */
  public $ss_system = null;

  /**
  * References the SS_Importer_Import class.
  *
  * @since 1.0.0
  *
  * @var SS_Importer_Import
  */
  public $ss_import = null;

  /**
  * References the SS_Template_Actions class.
  *
  * @since 1.0.0
  *
  * @var SS_Template_Actions
  */
  public $ss_actions = null;

  /**
  * References the SS_Importer_Plugins_Manager class.
  *
  * @since 1.0.0
  *
  * @var SS_Importer_Plugins_Manager
  */
  public $ss_plugins_manager = null;

  /**
  * References the SS_Logger class.
  *
  * @since 1.0.0
  *
  * @var SS_Logger
  */
  public $ss_logger = null;

  /**
  * Class constructor.
  *
  * @access public
  */
  public function __construct(){

    $this->init();

    // add_action( 'after_setup_theme', array( $this, 'setup_plugin' ) );
    add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

  }

  /**
	 * Enable translation.
   *
   * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'ss-importer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

  /**
  * Get the reference to the instance of this class.
  * @return Sigma_Importer
  *
  * @since 1.0.0
  */
  public static function new_instance(){

    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;

  }

  /**
   * Checks if child theme is active.
   *
   * @since 1.0.0
   */
  public function is_child_theme_active(){
    return get_template_directory() === get_stylesheet_directory();
  }

  /**
  * Set the variables for Sigma_Importer class.
  *
  * @since 1.0.0
  */
  public function set_vars(){

    $this->theme_name = $this->is_child_theme_active() ? wp_get_theme() : wp_get_theme(get_template())->get('Name');
    $this->theme_version = $this->is_child_theme_active() ? wp_get_theme()->Version : wp_get_theme(get_template())->get('Version');

  }

  /**
  * Set the constants for the plugin.
  *
  * @since 1.0.0
  */
  private function set_constants(){

    if ( ! defined( 'SS_IMPORTER_PATH' ) ) {
			define( 'SS_IMPORTER_PATH', plugin_dir_path( __FILE__ ) );
		}
		if ( ! defined( 'SS_IMPORTER_URL' ) ) {
			define( 'SS_IMPORTER_URL', plugin_dir_url( __FILE__ ) );
		}
    if ( !defined('WP_LOAD_IMPORTERS') ){
      define('WP_LOAD_IMPORTERS', true);
    }

    // Action hook to set the plugin version constant.
		add_action( 'admin_init', array( $this, 'set_admin_constants' ) );

  }

  /**
	 * Set the admin area constants
   *
   * @since 1.0.0
	 */
	public function set_admin_constants() {
    if ( ! defined( 'SS_IMPORTER_VERSION' ) ) {
      $plugin_data = get_plugin_data( __FILE__ );
			define( 'SS_IMPORTER_VERSION', $plugin_data['Version'] );
		}
	}

  /**
  * Initializes Sigma Importer classes.
  *
  * @since 1.0.0
  */
  public function init(){

    if ( !( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) ) {
			return;
		}

    /**
		 * Display an error message if PHP version is less than 5.3.2
		 */
		if ( version_compare( phpversion(), self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_minimum_php_version' ) );
      return;
		}

    // Set the plugin constants
    $this->set_constants();

    // Set the plugin variables
    $this->set_vars();

    /**
     * Include the autoloader.
     *
     * @since 1.0.0
     */
    require_once SS_IMPORTER_PATH . 'includes/SS_Autoloader.php';
    new SS_Autoloader();

    $this->ss_actions = new SS_Template_Actions;
    $this->ss_system = new SS_Importer_System;
    $this->ss_import = new SS_Importer_Import;
    $this->ss_plugins_manager = new SS_Importer_Plugins_Manager;
    $this->ss_logger = new SS_Logger;

  }

  /**
  * Enqueue all required scripts to run this plugin.
  *
  * @since 1.0.0
  */
  public function enqueue_admin_scripts(){

    //Js
    wp_enqueue_script( 'ss-importer-main', SS_IMPORTER_URL . 'assets/js/main.js' , array( 'jquery' ), SS_IMPORTER_VERSION );

    // No need to define the ajax url since this script only runs for logged in users in the admin panel, which means we have access to ajaxurl.
    wp_localize_script( 'ss-importer-main', 'ss_importer',
      array(
        'ajax_nonce'       => wp_create_nonce( 'ss-importer-ajax-nonce' ),
        'import_files'     => $this->ss_import->get_demos(),
      )
    );

    //Css
    wp_enqueue_style( 'ss-importer-main', SS_IMPORTER_URL . 'assets/css/main.css', false, SS_IMPORTER_VERSION );

  }

  /**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
  public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'ss-importer' ),
			'<strong>' . esc_html__( 'Sigma Importer', 'ss-importer' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'ss-importer' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

}

/**
* Function to instantiate the Sigma Importer class
*
* @since 1.0.0
*
* @return Sigma_Importer
*/
if( ! function_exists(  'ss_Importer' ) ){
  function ss_importer(){
    return Sigma_Importer::new_instance();
  }
}

// Initialize Sigma Importer class
ss_importer();
