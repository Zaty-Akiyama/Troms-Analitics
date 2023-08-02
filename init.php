<?php
/**
 * Troms analitics
 *
 * Plugin Name: Troms Analitics
 *
 * Description: ZATY開発のアナリティクスプラグインです。
 * Version: 1.2.0
 * Author: ZATY
 * Author URI: https://zaty.jp
 * Text Domain troms-analitics
 *
 */

if ( ! defined( 'ABSPATH' ) ) die( 'Invalid request.' );

if ( ! class_exists( 'Troms_Analitics' ) ) : 

class Troms_Analitics {
  
  public function __construct () {
    $this->init();

    register_activation_hook( __FILE__, [$this, 'plugin_activation'] );
    register_deactivation_hook( __FILE__, [$this, 'plugin_deactivation'] );

    $this->test();
  }

  private function init () {    
    $this->define();
    $this->includes();
  }

  private function test () {
    //テスト用のファイル本番環境では削除推奨
    include_once( TROMS_PLUGIN_DIR . 'tester/class-troms-database-test.php' );
    include_once( TROMS_PLUGIN_DIR . 'tester/class-troms-operate-test-column.php' );
      
    // $tester = new TROMS_Database_Test();
    // $tester->insert_dummy_data();

    // $table = new Troms_Create_Post_Views_Table();
    // $table->do_monthly_export();
  }

  private function define () {
    define( 'TROMS_VERSION', '1.0.0' );
    define( 'TROMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    define( 'TROMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    define( 'TROMS_IS_TEST', false );

    $upload_dir = wp_upload_dir();
    $export_dir = $upload_dir['basedir'] . '/post_view_exports';
    define( 'TROMS_EXPORT_DIR', $export_dir);
  }

  private function includes () {
    include_once( TROMS_PLUGIN_DIR . 'inc/class-troms-custom-fields.php' );

    include_once( TROMS_PLUGIN_DIR . 'inc/class-troms-create-post-views-table.php' );
    include_once( TROMS_PLUGIN_DIR . 'inc/class-troms-drop-post-views-table.php' );
    include_once( TROMS_PLUGIN_DIR . 'inc/class-troms-analitics-admin.php' );

    include_once( TROMS_PLUGIN_DIR . 'inc/class-troms-views-count-entry-point.php' );
    include_once( TROMS_PLUGIN_DIR . 'inc/class-troms-post-database-operation.php' );
  }
    
  public function plugin_activation () {
    $create_post_views_table = new Troms_Create_Post_Views_Table();
    $create_post_views_table->create_table();

    $custom_fields = new Troms_Custom_Fields();
    $custom_fields->plugin_activation();

    flush_rewrite_rules();
  }

  public function plugin_deactivation () {
    $drop_post_views_table = new Troms_Drop_Post_Views_Table();
    $drop_post_views_table->drop_table();

    flush_rewrite_rules();
  }

}

new Troms_Analitics();

/** 関数を定義 */

function troms_analitics_init () {
  global $analitics_operator;
  $analitics_operator = new Post_Database_Operation();
}
add_action( 'plugins_loaded', 'troms_analitics_init' );

function get_the_post_views ( $id = null, $start_date = null, $end_date = null ) {
  global $analitics_operator;
  
  if ( $start_date || $end_date ) {
    return $analitics_operator->get_the_post_views_within_dates( $id, $start_date, $end_date );
  }
  return $analitics_operator->get_the_post_views( $id );
}

function get_the_post_views_with_referrer_within_dates ( $id = null, $referrer = null, $start_date = null, $end_date = null ) {
  global $analitics_operator;
  
  if ( $start_date || $end_date ) {
    return $analitics_operator->get_the_post_views_with_referrer_within_dates( $id, $referrer, $start_date, $end_date );
  } elseif ( $referrer ) {
    return $analitics_operator->get_the_post_views_with_referrer( $id, $referrer );
  }
  return $analitics_operator->get_the_post_views( $id );
}

endif;