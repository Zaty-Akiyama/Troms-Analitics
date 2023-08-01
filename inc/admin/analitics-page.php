<?php
  function table_views () {
    $posts = $_POST;

    if ( empty( $posts ) ) {
      return get_the_post_views();
    }

    $start_date = $posts['start_date'];
    $end_date = $posts['end_date'];
    $search = $posts['search'];

    $is_default = isset( $_POST['default'] ) && $_POST['default'];

    if( ! $is_default && $search ) {
  
      return get_the_post_views_with_referrer_within_dates( null, $search, $start_date, $end_date);
    }elseif ( ! $is_default ) {
      
      return get_the_post_views( null, $start_date, $end_date );
    }else {
      return get_the_post_views();
    }
  }
  $start_date = isset($_POST['default']) && !$_POST['default'] && isset($_POST['start_date']) ? esc_attr($_POST['start_date']) : '';
  $end_date = isset($_POST['default']) && !$_POST['default'] && isset($_POST['end_date']) ? esc_attr($_POST['end_date']) : '';
?>
<link rel="stylesheet" href="<?php echo esc_url( TROMS_PLUGIN_URL . 'assets/css/style.min.css' ); ?>">
<script src="<?php echo esc_url( TROMS_PLUGIN_URL. 'assets/js/script.js'); ?>"></script>

<div class="troms-analitics">
  <h1 class="troms-analitics__title">アナリティクス</h1>

  <form action="<?php echo esc_url( home_url() ); ?>/wp-admin/admin.php?page=troms-analitics" class="troms-analitics__form jsAnaliticsForm" method="POST">
    <label for="famous">
      <input type="button" value="デフォルトに戻す" class="troms-analitics__input jsDefaultButton">
    </label>
    <label for="start_date">
      <span class="troms-analitics__label">開始日</span>
      <input 
        type="date"
        name="start_date"
        id="start_date"
        class="troms-analitics__input jsStartInput"
        value="<?php echo $start_date; ?>"
        max="<?php echo $end_date; ?>"
      >
    </label>
    <label for="end_date">
      <span class="troms-analitics__label">終了日</span>
      <input
        type="date"
        name="end_date"
        id="end_date"
        class="troms-analitics__input jsEndInput"
        value="<?php echo $end_date; ?>"
        min="<?php echo $start_date; ?>"
      >
    </label>
    <label for="search">
      <span class="troms-analitics__label">リファラー検索</span>
      <input type="text" name="search" id="search" class="troms-analitics__input" placeholder="検索" value="<?php echo isset( $_POST['search'] ) ? esc_attr($_POST['search']) : ''; ?>">
    </label>
    <input class="jsSubmitButton" type="submit" value="ソート">
  </form>
  <table class="wp-list-table widefat fixed striped table-view-list posts jsAnaliticsTable">
    <thead class="troms-analitics__table-head">
      <tr>
        <th scope="col" id="ID" class="manage-column column-title troms-analitics__id-column">記事ID</th>
        <th scope="col" id="title" class="manage-column column-title">タイトル</th>
        <th scope="col" id="post_views" class="manage-column column-title troms-analitics__pv-column jsPvSortButton">PV数</th>
        <th scope="col" id="post_views" class="manage-column column-title">サムネイル</th>
      </tr>
    </thead>
    <tbody id="the-list">
      <?php
      $args = [
        'posts_per_page' => -1,
      ];
      $the_post = new WP_Query( $args );
      if ( $the_post->have_posts() ): ?>
      <?php while ( $the_post->have_posts() ): $the_post->the_post(); ?>

      <tr class="troms-analitics__data-row">
        <td class="troms-analitics__id-column"><?php echo esc_html( get_the_ID() ); ?></td>
        <td><?php echo esc_html(get_the_title()); ?>
        <div class="troms-analitics__links">
          <a href="<?php echo esc_url(get_edit_post_link()); ?>">編集</a>
          <a href="<?php echo esc_url(get_permalink()); ?>">表示</a></td>
        </div>
        <td><?php echo esc_html( table_views() ); ?></td>
        <td class="troms-analitics__thumbnail">
        <?php if ( has_post_thumbnail() ): ?>
          <?php the_post_thumbnail( 'large' ); ?>
        <?php endif; ?>
        </td>
      </tr>

      <?php endwhile; ?>
      <?php endif; ?>
      <?php wp_reset_postdata(); ?>
    </tbody>
  </table>
</div>
