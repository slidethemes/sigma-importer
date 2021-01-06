<?php
/**
 * Class SS_Template_Actions
 *
 * Class responsible for adding markup to the existing template actions.
 *
 * @author     Slidesigma
 * @copyright  (c) Copyright by Slidesigma
 * @link       http://www.slidesigma.com
 * @since      1.0.0
 */

class SS_Template_Actions{

  function __construct() {

    add_action( 'ss_importer/before_import_page', array( $this, 'get_importer_page_title' ), 30 );
    add_action( 'ss_importer/before_importer_wrapper', array( $this, 'loading_markup' ), 10 );
    add_action( 'ss_importer/before_importer_wrapper', array( $this, 'complete_markup' ), 20 );

    add_action( 'ss_importer/manual_import_inputs', array( $this, 'xml_input_markup' ), 10 );
    add_action( 'ss_importer/manual_import_inputs', array( $this, 'widget_input_makup' ), 20 );
    add_action( 'ss_importer/manual_import_inputs', array( $this, 'redux_input_markup' ), 30 );
    add_action( 'ss_importer/manual_import_inputs', array( $this, 'manual_import_button_markup' ), 40 );

  }

  /**
  * Return the markup responsible for displaying the manual import for content files
  *
  * @since 1.0.0
  */
  public function xml_input_markup(){
    ?>
    <div class="ss_importer-input-field ss_importer-manual-upload-section">
      <h4><?php esc_html_e( 'Content Import - .xml file', 'ss-importer' ); ?></h4>
      <input id="ss_importer-content-file" type="file" name="content-file">
      <p><?php esc_html_e( 'Please select the .xml file which you need to import', 'ss-importer' ); ?></p>
    </div>
    <?php
  }

  /**
  * Return the markup responsible for displaying the manual import for widgets
  *
  * @since 1.0.0
  */
  public function widget_input_makup(){
    ?>
    <div class="ss_importer-input-field ss_importer-manual-upload-section">
      <h4><?php esc_html_e( 'Widget Import - .wie file', 'ss-importer' ); ?></h4>
      <input id="ss_importer-widget-file" type="file" name="widget-file">
      <p><?php esc_html_e( 'Please select the .wie file which you need to import', 'ss-importer' ); ?></p>
    </div>
    <?php
  }

  /**
  * Return the markup responsible for displaying the manual import for redux options
  *
  * @since 1.0.0
  */
  public function redux_input_markup(){
    if ( !SS_Helper::is_redux_active() ){
      return;
    }
    ?>
    <div class="ss_importer-input-field ss_importer-manual-upload-section">
      <h4><?php esc_html_e( 'Redux Options Import - .json file', 'ss-importer' ); ?></h4>
      <input id="ss_importer-redux-file" type="file" name="redux-file">
      <p><?php esc_html_e( 'Please select the .json file which you need to import', 'ss-importer' ); ?></p>
      <hr>
      <input placeholder="Options Name" id="ss_importer-redux-opt-name" class="ss_importer-text" type="text" name="redux-opt-name">
      <p><?php esc_html_e( 'Enter the Redux options name, also refers to opt_name', 'ss-importer' ); ?></p>
    </div>
    <?php
  }

  /**
  * Return the markup for the manual import button
  *
  * @since 1.0.0
  */
  public function manual_import_button_markup(){
    ?>
    <div class="ss_importer-manual-import-btn">
      <button type="button" class="button button-large button-primary ss_importer-import-data" name="import"> <?php echo esc_html__('Import Content', 'ss-importer') ?> </button>
    </div>
    <?php
  }

  /**
  * Return the loading markup after import button has been clicked.
  *
  * @since 1.0.0
  */
  public function loading_markup(){
    ?>
    <div class="ss_container text-center">
      <div class="ss_importer-loading ss_importer-state">
        <div class="ss_importer-state-icon">
          <svg x="0px" y="0px" viewBox="0 0 412 412" xml:space="preserve">
        	 	  <path d="M334,140h-64c-4.4,0-8,3.6-8,8c0,4.4,3.6,8,8,8h64c13.2,0,24,10.8,24,24v192c0,13.2-10.8,24-24,24H78
        	 		c-13.2,0-24-10.8-24-24V180c0-13.2,10.8-24,24-24h72c4.4,0,8-3.6,8-8c0-4.4-3.6-8-8-8H78c-22,0-40,18-40,40v192c0,22,18,40,40,40
        	 		h256c22,0,40-18,40-40V180C374,158,356,140,334,140z"/>
              <g class="ss_importer-state-arrow">
            	 	<path d="M206,28c4.4,0,8-3.6,8-8V8c0-4.4-3.6-8-8-8c-4.4,0-8,3.6-8,8v12C198,24.4,201.6,28,206,28z"/>
            	 	<path class="sigma_importer-state-morph" d="M129.6,211.6c-3.2,3.2-3.2,8,0,11.2l70.8,70.8c1.6,1.6,3.6,2.4,5.6,2.4s4-0.8,5.6-2.4l70.8-70.8c3.2-3.2,3.2-8,0-11.2
            	 		s-8-3.2-11.2,0L214,268.8V56c0-4.4-3.6-8-8-8c-4.4,0-8,3.6-8,8v212.8l-57.2-57.2C137.6,208.4,132.8,208.4,129.6,211.6z"/>
          	 </g>
          </svg>
        </div>
        <span><?php echo esc_html__('Importing...', 'ss-importer') ?></span>
      </div>
    </div>
    <?php
  }

  /**
  * Return the loading markup after import button has been clicked.
  *
  * @since 1.0.0
  */
  public function complete_markup(){
    ?>
    <div class="ss_container text-center">
      <div class="ss_importer-state ss_importer-complete">
        <div class="ss_importer-state-icon">
          <svg viewBox="0 -21 512.016 512">
            <path d="m234.667969 469.339844c-129.386719 0-234.667969-105.277344-234.667969-234.664063s105.28125-234.6679685 234.667969-234.6679685c44.992187 0 88.765625 12.8203125 126.589843 37.0976565 7.425782 4.78125 9.601563 14.679687 4.820313 22.125-4.796875 7.445312-14.675781 9.597656-22.121094 4.820312-32.640625-20.972656-70.441406-32.042969-109.289062-32.042969-111.746094 0-202.667969 90.921876-202.667969 202.667969 0 111.742188 90.921875 202.664063 202.667969 202.664063 111.742187 0 202.664062-90.921875 202.664062-202.664063 0-6.679687-.320312-13.292969-.9375-19.796875-.851562-8.8125 5.589844-16.621094 14.378907-17.472656 8.832031-.8125 16.617187 5.589844 17.472656 14.378906.722656 7.53125 1.085937 15.167969 1.085937 22.890625 0 129.386719-105.277343 234.664063-234.664062 234.664063zm0 0"/>
            <path d="m261.332031 288.007812c-4.09375 0-8.191406-1.558593-11.304687-4.691406l-96-96c-6.25-6.253906-6.25-16.386718 0-22.636718s16.382812-6.25 22.632812 0l84.695313 84.695312 223.335937-223.339844c6.253906-6.25 16.386719-6.25 22.636719 0s6.25 16.382813 0 22.632813l-234.667969 234.667969c-3.136718 3.113281-7.230468 4.671874-11.328125 4.671874zm0 0"/>
          </svg>
        </div>
        <span><?php echo esc_html__('Import Complete', 'ss-importer') ?></span>
        <a href="<?php echo esc_url(home_url()) ?>" class="button button-primary button-large"><?php echo esc_html__('Visit Site', 'ss-importer') ?></a>
        <a href="#" class="button button-large refresh-trigger"><?php echo esc_html__('Try Another', 'ss-importer') ?></a>
      </div>
    </div>
    <?php
  }

  /**
  * Return page title for the importer page.
  *
  * @since 1.0.0
  */
  public function get_importer_page_title(){
    ?>
    <div class="ss_importer-title-wrap">
      <h1 class="ss_importer-title"> <?php echo esc_html__('Welcome to', 'ss-importer') ?> <span class="ss-theme-name"><?php esc_html_e( 'Sigma Importer', 'ss-importer' ); ?></span> </h1>
      <p class="ss_importer-version"><?php echo esc_html__('Version', 'ss-importer') . ' ' . esc_html(SS_IMPORTER_VERSION) ?></p>
      <hr>
      <div class="ss_importer-title-inner">
        <h3><?php echo esc_html__('Some notes about the importer', 'ss-importer') ?></h3>
        <ul>
          <li> <?php echo esc_html__('All your existing content will remain the same', 'ss-importer') ?> </li>
          <li> <?php echo esc_html__('The content from the new file you are importing will get added on top of your existing content', 'ss-importer') ?> </li>
          <li> <?php echo esc_html__('Import might take some time, based on your hosting', 'ss-importer') ?> </li>
          <li> <?php echo esc_html__('Happy Importing!', 'ss-importer') ?> </li>
        </ul>
      </div>
    </div>
    <?php
  }

}
