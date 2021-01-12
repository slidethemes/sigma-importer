=== Sigma Importer ===
Contributors: slidesigma
Tags: import, content, demo, data, widgets, redux, theme options
Requires at least: 4.0
Tested up to: 5.6
Requires PHP: 5.3.2
Stable tag: 2.6.1
License: GPLv3 or later

Import your demo content, widgets and theme settings with one click. Theme authors! Enable simple demo import for your theme demo data.

== Installation ==

**From your WordPress dashboard**

1. Visit 'Plugins > Add New',
2. Search for 'Sigma Importer' and install the plugin,
3. Activate 'Sigma Importer' from your Plugins page.
4. Navigate to 'Appearance > Demo Importer' to get started.

**From WordPress.org**

1. Download 'Sigma Importer'.
2. Upload the 'one-click-demo-import' directory to your '/wp-content/plugins/' directory
3. Activate 'Sigma Importer' from your Plugins page.

== How to predefine demo imports? ==

Theme authors can add predefined demo content for their themes. The content supports content files (.xml) - Widget files (.wie) - Redux options (.json) - Revolution slider slides (.zip). Follow the below example for more instructions

`
function sigma_importer_import_files(){
  $demos = [
    array(
      'file_name'              => 'Your Demo Import Name',
      'description'            => __( 'Any extra instructions you might want to add', 'your-textdomain' ),
      'front_page'             => 'Home',
      'blog_page'              => 'Blog',
      'menus'                  => array(
        'primary-menu'  =>  'Primary Menu',
        'mobile-menu'   =>  'Mobile Menu',
        'top-menu'      =>  'Top Header Menu',
      ),
      'import_file'            => YOUR_CONTENT_XML_PATH,
      'import_widget_file'     => YOUR_WIDGET_WIE_PATH,
      'import_redux' => array(
        array(
          'import_redux_file' =>  YOUR_REDUX_JSON_PATH,
          'opt_name'       => YOUR_THEME_OPT_NAME,
        ),
      ),
      'preview_thumb'   => YOUR_DEMO_PREVIEW_THUMB_URL,
      'preview_url'     => YOUR_DEMO_URL,
      'sliders'         => array( SLIDER_ONE_URL, SLIDER_TWO_URL, SLIDER_THREE_URL, SLIDER_FOUR_URL )
    ),
  ];
  return $demos;
}
add_filter( 'ss_importer/importer_custom_demos' , sigma_importer_import_files() );
`

As for now, the importer only supports local files. If you tried to add remote URL links for your content files, the importer will just ignore them.

= 1.0.0 =
* Initial release!
