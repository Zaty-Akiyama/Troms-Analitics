<?php

class Troms_Operate_Test_Column {
  const TABLE_NAME = 'post_view_counts';

  public function __construct () {
    if ( TROMS_IS_TEST && ! $this->exist_testable_table() ) {
      $this->add_test_column();
    } elseif ( ! TROMS_IS_TEST && $this->exist_testable_table( )) {
      $this->drop_test_column_data();
    }
  }

  private function exist_testable_table () {
    global $wpdb;

    $table_name = $wpdb->prefix . self::TABLE_NAME; 

    $column_exists = $wpdb->get_results("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = '{$wpdb->dbname}' 
        AND TABLE_NAME = '$table_name' 
        AND COLUMN_NAME = 'testable'
    ");

    return $column_exists;
  }

  private function add_test_column () {
    global $wpdb;

    $table_name = $wpdb->prefix . self::TABLE_NAME; 

    $wpdb->query("
      ALTER TABLE {$table_name} 
      ADD testable TINYINT(1) DEFAULT NULL
    ");
  }

  private function drop_test_column_data () {
    global $wpdb;

    $table_name = $wpdb->prefix . self::TABLE_NAME; 

    $wpdb->query("
      DELETE FROM {$table_name} 
      WHERE testable IS NOT NULL
    ");

    $wpdb->query("
        ALTER TABLE {$table_name} 
        DROP COLUMN testable
    ");
  }

}
new Troms_Operate_Test_Column();