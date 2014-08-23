<?php
function new_excerpt_more($more) {
    global $post;
  return '<a class="more-link" href="'. get_permalink($post->ID) . '">Read More</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');
