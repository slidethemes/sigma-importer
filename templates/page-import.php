<?php

$ss_importer = ss_importer();
$notices = $ss_importer->ss_import->get_notices();
$custom_demos = $ss_importer->ss_import->get_demos();

?>

<div class="ss_importer-wrapper">

  <?php
  /**
   * @hooked SS_Importer_System::get_system_status_content	- 10
   * @hooked SS_Importer_System::get_system_status_button	- 20
   * @hooked SS_Template_Actions::get_importer_page_title - 30
   */
  do_action('ss_importer/before_import_page');
  ?>

  <div class="ss_container">

    <?php if($notices){ ?>
      <ul class="ss_importer-notice-wrap">
        <?php foreach($notices as $key => $notice){ ?>
          <li class="ss_importer-notice"><?php echo esc_html($notice) ?></li>
        <?php } ?>
      </ul>
    <?php }else{ ?>
      <ul class="ss_importer-notice-wrap success">
        <li class="ss_importer-notice"><?php echo esc_html__('You are Good to Go!', 'ss-importer'); ?></li>
      </ul>
    <?php } ?>

  </div>

  <?php
  /*
  * @hooked SS_Template_Actions::loading_markup - 10
  */
  do_action('ss_importer/before_importer_wrapper');
  ?>

  <div class="ss_importer-wrapper-inner">
    <?php if( $custom_demos ){ ?>

      <?php
      $demo_categories = SS_Helper::format_demos_categories( $custom_demos );
      if( !empty( $demo_categories && count($custom_demos) > 2 ) ){
      ?>
      <div class="ss_container">
        <div class="ss_importer-tabs">
          <a href="#" class="sigma_importer-tabs-item active" data-category="*"><?php echo esc_html__('All', 'ss-importer') ?></a>
          <?php foreach($demo_categories as $key => $category){ ?>
          <a href="#" class="sigma_importer-tabs-item" data-category="<?php echo esc_js($key) ?>"><?php echo esc_html($category) ?></a>
          <?php } ?>
        </div>
      </div>
      <?php } ?>

      <?php do_action('ss_importer/before_demos'); ?>

      <div class="ss_container">
        <div class="ss_importer-demos ss_row">
          <?php foreach($custom_demos as $key => $demo){ ?>
            <div class="ss_importer-demo" data-categories="<?php echo esc_js(SS_Helper::format_demo_item_categories( $demo )) ?>">

              <div class="ss_importer-demo-thumb" style="background-image: url(<?php echo esc_attr(SS_Helper::get_demo_thumb( $demo )) ?>)">
                <?php if( isset($demo['sliders']) && is_array($demo['sliders']) && !empty($demo['sliders']) && SS_Helper::is_rev_slider_active() ){ ?>
                  <span><?php echo esc_html(sizeof($demo['sliders'])) . ' ' . esc_html__(' sliders included', 'ss-importer') ?></span>
                <?php } ?>
              </div>

              <div class="ss_importer-demo-body">
                <h3><?php echo esc_html( $demo['file_name'] ) ?></h3>
                <p><?php echo esc_html( $demo['description'] ) ?></p>
                <div class="ss_importer-demo-buttons">

                  <button type="button" class="button button-large button-primary ss_importer-import-data" value="<?php echo esc_attr( $key ); ?>" name="import"> <?php echo esc_html__('Import', 'ss-importer') ?> </button>

                  <?php if( isset($demo['preview_url']) && !empty($demo['preview_url']) ){ ?>
                  <a class="button button-large" href="<?php echo esc_url($demo['preview_url']) ?>" target="_blank"><?php echo esc_html__('Preview', 'ss-importer') ?></a>
                  <?php } ?>

                </div>
              </div>
            </div>
          <?php } ?>
        </div>
      </div>

      <?php do_action('ss_importer/after_demos'); ?>

    <?php }else{ ?>

      <div class="ss_container">
        <div class="ss_importer-demos ss_importer-manual-upload">

          <?php
          /*
          * @hooked SS_Template_Actions::xml_input_markup - 10
          * @hooked SS_Template_Actions::widget_input_makup - 20
          * @hooked SS_Template_Actions::redux_input_markup - 30
          * @hooked SS_Template_Actions::manual_import_button_markup - 40
          */
          do_action('ss_importer/manual_import_inputs');
          ?>

    		</div>
      </div>

    <?php } ?>
  </div>

  <?php do_action('ss_importer/after_importer_wrapper'); ?>

  <div class="ss_container">
    <ul class="ss_importer-notice-wrap postprocess-import-logs"></ul>
  </div>

  <?php do_action('ss_importer/after_import_page'); ?>

</div>
