<?php
//Strip <p> tags from shortcodes. Author URI: http://www.johannheyne.de
add_filter('the_content', 'shortcode_empty_paragraph_fix');
function shortcode_empty_paragraph_fix($content)
{
  $array = array (
    '<p>[' => '[',
    ']</p>' => ']',
    ']<br />' => ']'
  );

  $content = strtr($content, $array);

  return $content;
}

//Allow additional mime type to be uploaded
function my_myme_types($mime_types){
  $mime_types['thmx'] = 'application/vnd.ms-officetheme'; //Allow MS Office Theme files
  $mime_types['ttf'] = 'application/x-font-ttf'; //Allow TrueType files
  $mime_types['ai'] = 'application/postscript'; //Allow Adobe Illustrator files
  $mime_types['eps'] = 'application/postscript'; //Allow Adobe EPS files
  return $mime_types;
}
add_filter('upload_mimes', 'my_myme_types', 1, 1);

// remove version info from head and feeds
function complete_version_removal() {
    return '';
}
add_filter('the_generator', 'complete_version_removal');
