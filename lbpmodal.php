<?php
/**
 * lbpModal
 * Allows Menu Items to be given the class "lbpModal" for use with Lightbox Plus by using the link relationship property
 */
function add_menuclass($ulclass) {
  return preg_replace('/<a rel="modal"/', '<a class="lbpModal"', $ulclass, 1);
}
add_filter('wp_nav_menu','add_menuclass');
