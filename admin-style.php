<?php
/**
 * Custom Stylesheet for Admin UI
 * Source: http://codex.wordpress.org/Creating_Admin_Themes
 */
function my_admin_head() {
  echo '<link rel="stylesheet" type="text/css" href="';
  echo get_template_directory_uri() . '/admin-style.css';
  echo '">';

  echo '<script language="javascript" type="text/javascript" src="/wp-includes/js/tinymce/tiny_mce.js"></script>';
  echo '<script language="javascript" type="text/javascript">';
  echo 'tinyMCE.init({mode : "textareas",';
  echo 'theme : "advanced",';
  echo 'plugins : "emotions,spellchecker,advhr,insertdatetime,preview",';
  echo 'theme_advanced_buttons1 : "link",';
  echo 'theme_advanced_buttons2 : "",';
  echo 'theme_advanced_toolbar_location : "bottom",';
  echo 'theme_advanced_toolbar_align : "left",';
  echo 'theme_advanced_resizing : true';
  echo '</script>';
}
add_action('admin_head', 'my_admin_head');
