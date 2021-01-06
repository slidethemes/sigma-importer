$test = [
  array(
    'file_name'              => 'Demo Import 1',
    'description'            => __( 'After you import this demo, you will have to setup the slider separately.', 'your-textdomain' ),
    'categories'             => array( 'Category 1', 'Category 2' ),
    'front_page'             => 'Home',
    'blog_page'              => 'Blog',
    'menus'                  => array(
      'primary-menu'  =>  'Primary Menu',
      'mobile-menu'   =>  'Mobile Menu',
      'top-menu'      =>  'Top Header Menu',
      'menu-left'     =>  '',
      'menu-right'    =>  '',
    ),
    'import_file'            => SS_IMPORTER_PATH .'content.xml',
    'import_widget_file'     => SS_IMPORTER_PATH .'widget.wie',
    'import_redux' => array(
      array(
        'import_redux_file' =>  SS_IMPORTER_PATH .'redux.json',
        'opt_name'       => 'miranda_hotel_options',
      ),
    ),
    'preview_thumb'   => SS_IMPORTER_URL .'thumb.jpg',
    'preview_url'     => 'http://www.your_domain.com/my-demo-1',
    'sliders'         => array( SS_IMPORTER_PATH . 'home-1.zip', SS_IMPORTER_PATH . 'home-2.zip', SS_IMPORTER_PATH . 'home-3.zip', SS_IMPORTER_PATH . 'slider-1.zip', SS_IMPORTER_PATH . 'slider-2.zip' )
  ),
];

= 1.0.0 =

*Release Date - 4 January, 2021*

* Initial release!
