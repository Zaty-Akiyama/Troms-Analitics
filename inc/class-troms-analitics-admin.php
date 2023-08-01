<?php
if ( ! function_exists( 'add_action' ) ) die( 'Invalid request.' );

class Troms_Analitics_Admin {
  public function __construct () {
    $this->init();
  }
  
  private function init () {
    $this->hooks();
  }

  private function hooks () {
    add_action( 'admin_menu', [$this, 'add_admin_menu'], 20 );
  }

  private function is_existed_troms_setting_menu () {
    global $menu;
    foreach ( $menu as $item ) {
      if ( $item[2] === 'troms-setting' ) return true;
    }
    return false;
  }

  public function add_admin_menu () {
    if ( $this->is_existed_troms_setting_menu() ) {
      add_submenu_page(
        'troms-setting',
        'アナリティクス',
        'アナリティクス',
        'manage_options',
        'troms-analitics',
        [$this, 'troms_analitics_page'],
        2
      );
    }else {
      add_menu_page(
        'アナリティクス',
        'アナリティクス',
        'manage_options',
        'troms-analitics',
        [$this, 'troms_analitics_page'],
        'dashicons-chart-pie',
        21
      );
    }
    
  }

  public function troms_analitics_page() {
    include_once( TROMS_PLUGIN_DIR . 'inc/admin/analitics-page.php' );
  }
}
new Troms_Analitics_Admin();