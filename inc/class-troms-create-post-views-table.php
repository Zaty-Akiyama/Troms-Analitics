<?php

if ( ! function_exists( 'add_action' ) ) die( 'Invalid request.' );

class Troms_Create_Post_Views_Table {
  const TABLE_NAME = 'post_view_counts';

  public function __construct () {
    $this->hooks();

    $total_files_data = get_transient( 'troms_total_files_data' );

    if ( ! $total_files_data ) {
      $this->totalling_export_files();
    }
  }

  private function hooks () {
    add_action( 'monthly_export_hook', [$this, 'do_monthly_export'] );

    $this->monthly_export_setup();
  }

  public function create_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . self::TABLE_NAME;

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id mediumint(9) NOT NULL,
        view_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        referrer varchar(255) DEFAULT '' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
  }

  public function monthly_export_setup() {
    if ( ! wp_next_scheduled( 'monthly_export_hook' ) ) {
      wp_schedule_event( time(), 'monthly', 'monthly_export_hook' );
    }
  }

  public function do_monthly_export() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'post_view_counts';
    $last_month = date( 'Y-m-d H:i:s', strtotime('-1 month') );
    $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE view_date <= '$last_month'", OBJECT );
    
    if ( ! file_exists(TROMS_EXPORT_DIR) ) {
      mkdir(TROMS_EXPORT_DIR, 0755, true);
    }

    $export_file = TROMS_EXPORT_DIR . '/export-' . date( 'Y-m-d' ) . '.json';
    file_put_contents( $export_file, json_encode( $results ) );

    $wpdb->query( "DELETE FROM $table_name WHERE view_date <= '$last_month'" );

    $this->totalling_export_files();
  }

  public function totalling_export_files () {
    if ( ! file_exists( TROMS_EXPORT_DIR ) ) {
      set_transient( 'troms_total_files_data', 0, 60 * 60 * 24 * 34 );
      return;
    }

    $total_data = [];
    $dir = new DirectoryIterator( TROMS_EXPORT_DIR );
    
    foreach ( $dir as $fileinfo ) {
      if ( ! $fileinfo->isDot() && $fileinfo->getExtension() === 'json' ) {
        $jsonContent = file_get_contents( $fileinfo->getPathname() );
        $data = json_decode( $jsonContent, true );

        if ( json_last_error() === JSON_ERROR_NONE ) {

          $total_data = array_merge( $total_data, $data );
        }
      }
    }

    do_action( 'troms_analitics_output_files_data', $total_data );
    set_transient( 'troms_total_files_data', $total_data, 60 * 60 * 24 * 34 );
  }
}

new Troms_Create_Post_Views_Table();