<?php

function custom_query_shortcode($atts) {
  // example: [query query="showposts=100&post_type=page&post_parent=453" pagination="false" content_template="loop"]
  // currently tested and in use with a custom timber(https://github.com/jarednova/timber) based wordpress theme.

  // defaults
  extract(shortcode_atts(array(
    "query" => '',
    "template" => '',
    "pagination" => 'false'
  ), $atts));

  // setup pagination for custom query
  $paged = get_query_var('paged') ? get_query_var('paged') : ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 );

  // add pagination
  $query = isset($pagination) ? $query . '&paged=' . $paged : $query;

  // de-funkify query
  $query = html_entity_decode($query);
  $query = preg_replace_callback('~&#x0*([0-9a-f]+);~i',function ($str){return chr(hexdec($str));},$query);
  $query = preg_replace_callback('~&#0*([0-9]+);~',function ($str){return chr($str);},$query);

  // set global $posts scope
  global $posts;

  // query is made
  query_posts($query);

  // reset and setup variables
  $content = '';

  // capture output
  ob_start();

  // the loop
  if (have_posts() && !is_home() && !is_main_query() && in_the_loop()) :
    while (have_posts()) : the_post();
      $content = get_template_part( 'content', isset($template) ? $template : get_post_format() );
    endwhile;

    if ($pagination !='false') :
      wpforge_content_nav( $pagination == 'true' ? 'nav_below' : $pagination );
    endif;

  // reset query and return content
  wp_reset_postdata();
  wp_reset_query();
  else:
    $content = get_template_part( 'content', 'none' );
  endif;

  // save output and clean
  $content = ob_get_contents();
  ob_end_clean();
  return $content;

}
add_shortcode("query", "custom_query_shortcode");

?>