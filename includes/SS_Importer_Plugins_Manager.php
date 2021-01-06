<?php
/**
 * Class SS_Importer_Plugins_Manager
 *
 * Class responsible for the plugins by extending TGMPA
 *
 * @author     Slidesigma
 * @copyright  (c) Copyright by Slidesigma
 * @link       http://www.slidesigma.com
 * @since      1.0.0
 */

class SS_Importer_Plugins_Manager{

  public function __construct(){

    add_filter( 'ss_importer/importer_prerequisite_notices', array( $this, 'update_notices' ), 10, 1 );
    add_filter( 'tgmpa_load', array( $this, 'tgmpa_load' ), 10, 1 );

  }

  public function tgmpa_load( $status ) {
    return is_admin() || current_user_can( 'install_themes' );
  }

  /**
  * Returns a list of all plugins assigned in our tgm config.
  *
  * @since 1.0.0
  */
  public function get_plugins() {

    if( !SS_Helper::is_tgmpa_active() ){
      return false;
    }

    $instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
    $plugins  = array(
      'all'      => array(), // Meaning: all plugins which still have open actions.
      'install'  => array(),
      'activate' => array(),
    );

    if( $instance->plugins ){
      foreach ( $instance->plugins as $slug => $plugin ) {
        if ( !$instance->can_plugin_activate( $slug ) && false === $instance->does_plugin_have_update( $slug ) ) {
          // No need to display plugins if they are installed, up-to-date and active.
          continue;
        } else {
          $plugins['all'][ $slug ] = $plugin;

          if ( ! $instance->is_plugin_installed( $slug ) ) {
            $plugins['install'][ $slug ] = $plugin;
          } else {

            if ( $instance->can_plugin_activate( $slug ) ) {
              $plugins['activate'][ $slug ] = $plugin;
            }elseif(  $instance->can_plugin_update( $slug ) ) {
              $plugins['update'][ $slug ] = $plugin;
            }

          }
        }
      }
    }

    return $plugins;
  }

  /**
  * Checks if we have pending plugins to activated/installed.
  *
  * @since 1.0.0
  */
  private function has_pending_plugins(){
    $plugins = $this->get_plugins();
    return (isset( $plugins['install'] ) && sizeof($plugins['install']) > 0) || (isset( $plugins['activate'] ) && sizeof($plugins['activate']) > 0);
  }

  /**
  * Updates the notices object of SS_Importer_Import_Helper if any plugins are pending installation or activation.
  *
  * @since 1.0.0
  */
  public function update_notices( $notices ){

    if( $this->has_pending_plugins() ){
      $notices[] = esc_html__('Some plugins are pending activation or installation, please activate all theme required plugins before importing', 'ss-importer');
    }
    return $notices;

  }

}
