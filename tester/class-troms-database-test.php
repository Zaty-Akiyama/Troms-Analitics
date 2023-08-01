<?php

class Troms_Database_Test {
  const TABLE_NAME = 'post_view_counts';

  public function __construct () {
    if ( ! TROMS_IS_TEST ) {
      die( 'テストは許可されていません。' );
    }
  }
 
  public function insert_dummy_data () {
    global $wpdb;

    $table_name = $wpdb->prefix . self::TABLE_NAME; 

    $num_entries = 100;
    $referrers = ['https://google.com', 'https://facebook.com', 'https://twitter.com', 'https://bing.com'];
    $ids = [291, 281, 284, 282, 279, 277, 275, 257, 250, 194, 126, 28, 26, 24, 17, 11, 6, 1];

    for( $i = 0; $i < $num_entries; $i++ ) {
      $post_id = $ids[array_rand($ids)];
      $random_date = date('Y-m-d H:i:s', strtotime('-' . rand(0, 120) . ' days'));

      $referrer = $referrers[array_rand($referrers)];

      $wpdb->insert(
        $table_name,
        array(
          'post_id'    => $post_id,
          'view_date'  => $random_date,
          'referrer'   => $referrer,
          'testable'   => 1
        )
      );
    }
  }
}