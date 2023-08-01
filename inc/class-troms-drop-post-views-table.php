<?php

if ( ! function_exists( 'add_action' ) ) die( 'Invalid request.' );

class Troms_Drop_Post_Views_Table {
  const TABLE_NAME = 'post_view_counts';

  public function __construct () {
  }

  private function check_table_exists() {
    global $wpdb;
    $table_name = $wpdb->prefix . self::TABLE_NAME;

    return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
  }

  private function delete_post_meta () {
    $args = [
      'posts_per_page' => -1,
    ];
    $args['post_type'] = apply_filters( 'exists_post_type', 'post' );

    $query = new WP_Query( $args );
    if ( $query->have_posts() ) {
      while ( $query->have_posts() ) {
        $query->the_post();

        delete_post_meta( get_the_ID(), '_views_count' );
      }
    }
    wp_reset_postdata();
  }

  public function drop_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . self::TABLE_NAME;

    $this->delete_post_meta();

    if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name ) return;

    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query( $sql );
  }
}