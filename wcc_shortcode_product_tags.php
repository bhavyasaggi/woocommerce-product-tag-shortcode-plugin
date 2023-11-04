<?php
/*
Plugin Name: Woocommerce Product Tags Shortcode
Plugin URI: https://bhavyasaggi.github.io/plugins/disable-bloat
Description: Add [product_tags] shortcode
Version: 0.1.0
Author: Bhavya Saggi
Author URI: https://bhavyasaggi.github.io/
License: MIT

------------------------------------------------------------------------

Copyright

*/

function wcc_shortcode_product_tags($atts)
{
  if (isset($atts['number'])) {
    $atts['limit'] = $atts['number'];
  }

  $atts = shortcode_atts(
    array(
      'limit' => '-1',
      'orderby' => 'count',
      'order' => 'DESC',
      'columns' => '4',
      'hide_empty' => 1,
      'ids' => '',
    ),
    $atts,
    'product_tags'
  );

  $ids = array_filter(array_map('trim', explode(',', $atts['ids'])));
  $hide_empty = (true === $atts['hide_empty'] || 'true' === $atts['hide_empty'] || 1 === $atts['hide_empty'] || '1' === $atts['hide_empty']) ? 1 : 0;

  // Get terms and workaround WP bug with parents/pad counts.
  $args = array(
    'orderby' => $atts['orderby'],
    'order' => $atts['order'],
    'hide_empty' => $hide_empty,
    'include' => $ids,
    'pad_counts' => true,
  );

  $product_tags = apply_filters(
    'woocommerce_product_categories',
    get_terms('product_tag', $args)
  );

  if ($hide_empty) {
    foreach ($product_tags as $key => $tag) {
      if (0 === $tag->count) {
        unset($product_tags[$key]);
      }
    }
  }

  $atts['limit'] = '-1' === $atts['limit'] ? null : intval($atts['limit']);
  if ($atts['limit']) {
    $product_tags = array_slice($product_tags, 0, $atts['limit']);
  }

  $columns = absint($atts['columns']);

  wc_set_loop_prop('columns', $columns);
  wc_set_loop_prop('is_shortcode', true);

  ob_start();

  if ($product_tags) {
    woocommerce_product_loop_start();

    foreach ($product_tags as $tag) {
      wc_get_template(
        'content-product_cat.php',
        array(
          'category' => $tag,
        )
      );
    }

    woocommerce_product_loop_end();
  }

  wc_reset_loop();

  return '<div class="woocommerce columns-' . $columns . '">' . ob_get_clean() . '</div>';
}

function wcc_shortcode_product_tags_init()
{
  add_shortcode('product_tags', 'wcc_shortcode_product_tags');
}

add_action('init', 'wcc_shortcode_product_tags_init');


?>