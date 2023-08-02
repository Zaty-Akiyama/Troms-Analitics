<?php

class Post_Database_Operation {

  const TABLE_NAME = 'post_view_counts';

  public function __construct () {
    $this->hooks();
  }

  private function hooks () {
    add_action( 'wp_head', [$this, 'insert_post_view_script'] );

    add_filter( 'default_views_count', [$this, 'default_views_count'], 10, 2 );

    add_action( 'troms_views_count', [$this, 'record_post_views'] );
  }

  public function default_views_count ( $_, $id ) {
    return get_the_post_views( $id );
  }
 
  private function is_bot () {
    $ua = $_SERVER["HTTP_USER_AGENT"];
    $bots = array(
      "googlebot",
      "msnbot",
      "yahoo",
      "AdsBot-Google-Mobile",
      "AdsBot-Google",
      "Mediapartners-Google",
      "Twitterbot",
      "APIs-Google",
      "Googlebot-Image",
      "dataminr.com",
      "AhrefsBot",
      "comscore.com",
      "YandexBot",
      "bingbot",
      "CriteoBot",
      "admantx",
      "Yeti",
      "spider",
      "facebookexternalhit",
      "Facebot",
      "ia_archiver",
      "MJ12bot",
      "Ezooms",
      "panscient.com",
      "Applebot",
      "Baiduspider",
      "pingdom.com_bot",
      "Sogou",
      "exabot",
      "SeznamBot",
      "CCBot",
      "DotBot",
      "BLEXBot",
      "Mail.RU_Bot",
      "DuckDuckBot",
      "Swiftbot",
      "SkypeUriPreview",
      "Discordbot",
      "Pinterestbot",
      "Chatwork LinkPreview",
      "Linespider",
      "monitoring",
      "Google-InspectionTool",
      "YaK"
    );
    foreach( $bots as $bot ) {
      if (stripos( $ua, $bot ) !== false){
        return true;
      }
    }
    return false;
  }

  // 記事の閲覧数を記録
  public function insert_post_view_script () {
    if ( ! is_single() || $this->is_bot() || is_user_logged_in() ) return;

    global $post;
    $post_id = $post->ID;
    $referrer = $_SERVER['HTTP_REFERER'] ?? '(direct)';
    $ua = $_SERVER["HTTP_USER_AGENT"];
    $ip = $_SERVER["REMOTE_ADDR"];

    $home_url = home_url();
    $script_HTML = <<<HTML
    <script>
      (function() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '$home_url/wp-json/troms/views_count/$post_id/', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify({
          action: 'troms_views_count',
          post_id: $post_id,
          referrer: '$referrer',
          user_agent: '$ua',
          ip: '$ip'
        }));
      })();
    </script>
    HTML;
    echo $script_HTML;
  }

  public function record_post_views ( $insert_data ) {
    global $wpdb;
    $table_name = $wpdb->prefix . self::TABLE_NAME;

    $wpdb->insert( $table_name, $insert_data );

    $post_id = $insert_data['post_id'];

    $post_views_metadata = get_post_meta( $post_id, '_views_count', true);
    if( $post_views_metadata === "" ) {
      update_post_meta( $post_id, '_views_count', (string) $this->get_the_post_views( $post_id ) );
    } else {
      $new_views = (int) $post_views_metadata + 1;
      update_post_meta( $post_id, '_views_count', (string) $new_views );
    }
  }

  //  記事の閲覧数を取得
  public function get_the_post_views ( $id = null ) {
    global $post, $wpdb;
    $table_name = $wpdb->prefix . self::TABLE_NAME;
    $post_id = $id ?? $post->ID;
    $results = $wpdb->get_results( "SELECT COUNT(*) as total FROM $table_name WHERE post_id = $post_id", OBJECT );
    
    $database_views = $results[0]->total;

    $total_files_data = get_transient( 'troms_total_files_data' );

    $files_views = 0;

    if ( !! $total_files_data ) {
      foreach ( $total_files_data as $data ) {
        if ( $data["post_id"] !== (string) $post_id ) continue;

        $files_views++;
      }
    }

    $total_views = (int) $database_views + $files_views;

    return apply_filters( 'display_views', $total_views );
  }

  public function get_the_post_views_within_dates ( $id = null, $start_date = null, $end_date = null ) {
    global $post, $wpdb;
    $table_name = $wpdb->prefix . self::TABLE_NAME;
    $post_id = $id ?? $post->ID;

    $start_date = $start_date ? $start_date : '1970-01-01';
    $end_date = $end_date ? $end_date : current_time( 'mysql' );

    $start_date_str = $start_date instanceof DateTime ? $start_date->format('Y-m-d') : $start_date;
    $end_date_str = $end_date instanceof DateTime ? $end_date->format('Y-m-d') : $end_date;

    $results = $wpdb->get_results( "SELECT COUNT(*) as total FROM $table_name WHERE post_id = $post_id AND view_date >= '$start_date_str' AND view_date <= '$end_date_str'", OBJECT );

    $database_views = $results[0]->total;

    $total_files_data = get_transient( 'troms_total_files_data' );

    $files_views = 0;

    if ( !! $total_files_data ) {
      foreach ( $total_files_data as $data ) {
        if ( $data["post_id"] !== (string) $post_id ) continue;
  
        $view_date = new DateTime($data["view_date"]);
        $start_date = $start_date instanceof DateTime ? $start_date : new DateTime($start_date);
        $end_date = $end_date instanceof DateTime ? $end_date : new DateTime($end_date);
  
        if ($view_date >= $start_date && $view_date <= $end_date) {
          $files_views++;
        }
      }
    }

    $total_views = (int) $database_views + $files_views;

    return apply_filters( 'display_views', $total_views );
  }

  public function get_the_post_views_with_referrer( $id = null, $referrer = null ) {
    global $post, $wpdb;
    $table_name = $wpdb->prefix . self::TABLE_NAME;
    $post_id = $id ?? $post->ID;
    $results = $wpdb->get_results( "SELECT COUNT(*) as total FROM $table_name WHERE post_id = $post_id AND referrer LIKE '%$referrer%'", OBJECT );

    $database_views = $results[0]->total;

    $total_files_data = get_transient( 'troms_total_files_data' );

    $files_views = 0;

    if( !! $total_files_data ) {
      foreach ( $total_files_data as $data ) {
        if ( $data["post_id"] !== (string) $post_id ) continue;

        if (strpos($data["referrer"], $referrer) !== false) {
          $files_views++;
        }
      }
    }

    $total_views = (int) $database_views + $files_views;

    return apply_filters( 'display_views', $total_views );
  }

  public function get_the_post_views_with_referrer_within_dates ( $id = null, $referrer = null, $start_date = null, $end_date = null ) {
    global $post, $wpdb;
    $table_name = $wpdb->prefix . self::TABLE_NAME;
    $post_id = $id ?? $post->ID;

    $start_date = $start_date ? $start_date : '1970-01-01';
    $end_date = $end_date ? $end_date : current_time( 'mysql' );

    $start_date_str = $start_date instanceof DateTime ? $start_date->format('Y-m-d') : $start_date;
    $end_date_str = $end_date instanceof DateTime ? $end_date->format('Y-m-d') : $end_date;

    $results = $wpdb->get_results( "SELECT COUNT(*) as total FROM $table_name WHERE post_id = $post_id AND referrer LIKE '%$referrer%' AND view_date >= '$start_date_str' AND view_date <= '$end_date_str'", OBJECT );

    $database_views = $results[0]->total;

    $total_files_data = get_transient( 'troms_total_files_data' );

    $files_views = 0;

    if ( !! $total_files_data ) {
      foreach ( $total_files_data as $data ) {
        if ( $data["post_id"] !== (string) $post_id ) continue;
  
        $view_date = new DateTime($data["view_date"]);
        $start_date = $start_date instanceof DateTime ? $start_date : new DateTime($start_date);
        $end_date = $end_date instanceof DateTime ? $end_date : new DateTime($end_date);
  
        if ($view_date >= $start_date && $view_date <= $end_date && strpos($data["referrer"], $referrer) !== false) {
          $files_views++;
        }
      }
    }

    $total_views = (int) $database_views + $files_views;

    return apply_filters( 'display_views', $total_views );
  }
}
