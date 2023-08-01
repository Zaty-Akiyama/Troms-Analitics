<?php

if ( ! function_exists( 'add_action' ) ) die( 'Invalid request.' );

class Troms_Custom_Fields {
  
  public function __construct () {
    $this->hooks();
  }

  private function hooks () {
    add_action( 'template_redirect', [$this, 'add_exists_posts_custom_field'] );

    add_action( 'manage_posts_custom_column', [$this, 'add_view_column'], 10, 2 );
    add_filter( 'manage_posts_columns', [$this, 'manage_posts_views_columns'] );
  }

  // プラグイン有効化時に実行
  public function plugin_activation () {
    $this->add_exists_posts_custom_field();
  }

  // 既存の投稿にカスタムフィールドを追加
  public function add_exists_posts_custom_field () {
    $args = [
      'posts_per_page' => -1,
    ];
    $args['post_type'] = apply_filters( 'exists_post_type', 'post' );

    $query = new WP_Query( $args );
    if ( $query->have_posts() ) {
      while ( $query->have_posts() ) {
        $query->the_post();

        $this->add_custom_field( get_the_ID() );
      }
    }
    wp_reset_postdata();
  }

  // カスタムフィールドを追加
  private function add_custom_field ( $id ) {
    $key = '_views_count';
    $value = 0;
    if ( get_post_meta( $id, $key, true ) === "" ) {
      $default_views = apply_filters( 'default_views_count', 0, $id );
      add_post_meta( $id, $key, $default_views );
    }
  }

  // カラムに表示する内容を追加
  public function add_view_column( $column_name, $post_id ) {

    if ( 'post_views_count' !== $column_name ) return;

    $views_count = get_post_meta($post_id, '_views_count', true);
    echo esc_html($views_count) . '<br/>';

  }

  // カラムを追加
  public function manage_posts_views_columns( $columns ) {
    array_splice( $columns, 2, 0, 'post_views_count' );

    $new_columns = array();
    foreach ($columns as $key => $value) {
      if( $key === 0 ) {
        $new_columns['post_views_count'] = 'view数';
      }else {
        $new_columns[$key] = $value;
      }
    }

    echo '<style>.column-post_views_count {width:140px;}.fixed .column-categories,.fixed .column-tags{width: 200px;}</style>';
    return $new_columns;
  }
}

new Troms_Custom_Fields();