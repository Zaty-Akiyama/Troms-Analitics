<?php
/**
 * @version 1.2.0
 * 計測をAjax通信から行うためのエントリーポイント
 */

class Views_Count_Entry_Point {
  public function __construct () {
    $this->hooks();
  }

  private function hooks () {
    add_action( 'rest_api_init', [$this, 'views_count_rewrite_rule'] );
  }

  public function views_count_rewrite_rule () {
    register_rest_route( 'troms/views_count', '/(?P<post_id>\d+)', [
      'methods' => 'POST',
      'callback' => [$this, 'views_count_entry_point'],
    ] );
  }
  public function views_count_entry_point ($data) {
    $post_data = $data->get_params();

    if ( empty($post_data) && $post_data['action'] !== 'troms_views_count' ) {
      exit;
    }

    $post_id = $post_data['post_id'];
    $post = get_posts( $post_id );

    if ( ! $post ) {
      exit;
    }

    $current_time = current_time( 'mysql' );

    $referrer = $post_data['referrer'];
    $ua = $post_data['user_agent'];
    $ip = $post_data['ip'];

    $insert_data = array(
      'post_id'    => $post_id,
      'view_date'  => $current_time,
      'referrer'   => $referrer,
      'ua'         => $ua,
      'ip'         => $ip
    );
    do_action( 'troms_views_count', $insert_data );

    exit;
  }
}

new Views_Count_Entry_Point();