<?php
/**
 * Code based on Sermon Manager for WordPress by WP for Church
 */

// BEGIN - Custom Post Type
add_action('init', 'create_sermon_types');
function create_sermon_types()
{
  $directory = get_stylesheet_directory_uri();
  $labels = array(
    'name'               => _x('Sermon Series', 'post type general name'),
    'singular_name'      => _x('Sermon Series', 'post type singular name'),
    'add_new'            => _x('Add New', 'sermon'),
    'add_new_item'       => __('Add New Series'),
    'edit_item'          => __('Edit Sermon Series'),
    'new_item'           => __('New Series'),
    'view_item'          => __('View Sermon Series'),
    'search_items'       => __('Search Sermon Archives'),
    'not_found'          =>  __('No sermons found matching that criteria'),
    'not_found_in_trash' => __('No sermons found in Trash. Why would we throw them away?'),
    'parent_item_colon'  => '',
    'menu_name'          => 'Sermon Archives',
  );
  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'menu_icon'          => 'dashicons-rss',
    'capability_type'    => 'post',
    'has_archive'        => true,
    'rewrite'            => array('slug' => 'series'),
    'hierarchical'       => false,
    'menu_position'      => 5,
    'supports'           => array('title', 'editor', 'thumbnail')
  );
  register_post_type('sermon',$args);
}
// END - Custom Post Type

// BEGIN - Custom Taxonomies
add_action( 'init', 'create_sermon_taxonomies', 0 );
function create_sermon_taxonomies()
{
/* Sermon Topics */
$labels = array(
  'name'                       => _x( 'Sermon Topics', 'custom taxonomy general name'),
  'singular_name'              => _x( 'Sermon Topics', 'custom taxonomy singular name' ),
  'menu_name'                  => __( 'Sermon Topics'),
  'search_items'               => __( 'Search sermon topics' ),
  'popular_items'              => __( 'Most popular sermon topics' ),
  'all_items'                  => __( 'All sermon topics' ),
  'edit_item'                  => __( 'Edit sermon topic' ),
  'update_item'                => __( 'Update sermon topic' ),
  'add_new_item'               => __( 'Add new sermon topic' ),
  'new_item_name'              => __( 'New sermon topic' ),
  'separate_items_with_commas' => __( 'Separate sermon topics with commas' ),
  'add_or_remove_items'        => __( 'Add or remove sermon topics' ),
  'choose_from_most_used'      => __( 'Choose from most used sermon topics' ),
  'parent_item'                => null,
  'parent_item_colon'          => null,
);

register_taxonomy('sermon-topics','sermon', array(
  'hierarchical' => true,
  'labels'       => $labels,
  'show_ui'      => true,
  'query_var'    => true,
  'rewrite'      => true,
));
}
// END - Custom Taxonomies

// BEGIN - Custom Post Type Filter
add_filter('post_updated_messages', 'sermon_updated_messages');
function sermon_updated_messages( $messages ) {
  global $post, $post_ID;

  $messages['sermon'] = array(
    0 => '', // Unused. Messages start at index 1.
    1 => sprintf( __('Sermon series updated. <a href="%s">View sermon series</a>'), esc_url( get_permalink($post_ID) ) ),
    2 => __('Custom field updated.'),
    3 => __('Custom field deleted.'),
    4 => __('Sermon series updated.'),
    /* translators: %s: date and time of the revision */
    5 => isset($_GET['revision']) ? sprintf( __('Sermon series restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Sermon published. <a href="%s">View sermon series</a>'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Sermon series saved.'),
    8 => sprintf( __('Sermon series submitted. <a target="_blank" href="%s">Preview sermon series</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Series scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview sermon series</a>'),
      // translators: Publish box date format, see http://php.net/date
      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Sermon draft updated. <a target="_blank" href="%s">Preview sermon</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}

// BEGIN - Custom Search
add_filter('posts_where', 'custom_search_where');
add_filter('posts_join', 'custom_search_join');
add_filter('posts_request', 'request_filter');
add_filter('posts_groupby', 'custom_search_group');

function request_filter($content)
{
  // var_dump($content);
  return $content;
}

function custom_search_where($content)
{
  global $wpdb;

  if (is_search())
  {
    $search = get_search_query();
    $content .= " or ({$wpdb->prefix}postmeta.meta_key = 'week1_passage' and {$wpdb->prefix}postmeta.meta_value LIKE '%{$search}%') ";
    $content .= " or ({$wpdb->prefix}postmeta.meta_key = 'week1_date' and {$wpdb->prefix}postmeta.meta_value LIKE '%{$search}%') ";
    $content .= " or ({$wpdb->prefix}postmeta.meta_key = 'week1_speaker' and {$wpdb->prefix}postmeta.meta_value LIKE '%{$search}%') ";
  }

  return $content;
}

function custom_search_join($content)
{
  global $wpdb;

  if (is_search())
  {
    $content .= " left join {$wpdb->prefix}postmeta on {$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.id ";
  }
  return $content;
}

function custom_search_group($content)
{
  global $wpdb;
  if (is_search())
  {
    $content .= " {$wpdb->prefix}posts.id ";
  }
  return $content;
}
// END - Custom Search

//enqueue needed js and styles on sermon edit screen
add_action('admin_enqueue_scripts', 'admin_script_post');

function admin_script_post() {
  $directory = get_stylesheet_directory_uri();
  global $post_type;
    wp_enqueue_script('jquery');
    wp_enqueue_script('media-upload');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('thickbox');
    wp_enqueue_script('jquery-ui-accordion');
    wp_register_script( 'jquery-ui-autocomplete', $directory .'/js/jquery.ui.autocomplete.min.js' );
    wp_enqueue_script('jquery-ui-autocomplete');
}

// BEGIN - Create custom fields
add_action("admin_menu", "my_meta_boxes");

function my_meta_boxes() {
  add_meta_box("week1", "Week 1", "week1", "sermon", "normal", "high");
  add_meta_box("week2", "Week 2", "week2", "sermon", "normal", "high");
  add_meta_box("week3", "Week 3", "week3", "sermon", "normal", "high");
  add_meta_box("week4", "Week 4", "week4", "sermon", "normal", "high");
  add_meta_box("week5", "Week 5", "week5", "sermon", "normal", "high");
  add_meta_box("week6", "Week 6", "week6", "sermon", "normal", "high");
  add_meta_box("week7", "Week 7", "week7", "sermon", "normal", "high");
  add_meta_box("week8", "Week 8", "week8", "sermon", "normal", "high");
  add_meta_box("week9", "Week 9", "week9", "sermon", "normal", "high");
  add_meta_box("week10", "Week 10", "week10", "sermon", "normal", "high");
  add_meta_box("week11", "Week 11", "week11", "sermon", "normal", "high");
  add_meta_box("week12", "Week 12", "week12", "sermon", "normal", "high");
  add_meta_box("week13", "Week 13", "week13", "sermon", "normal", "high");
  add_meta_box("week14", "Week 14", "week14", "sermon", "normal", "high");
  add_meta_box("week15", "Week 15", "week15", "sermon", "normal", "high");
  add_meta_box("week16", "Week 16", "week16", "sermon", "normal", "high");
  add_meta_box("week17", "Week 17", "week17", "sermon", "normal", "high");
  add_meta_box("week18", "Week 18", "week18", "sermon", "normal", "high");
  add_meta_box("week19", "Week 19", "week19", "sermon", "normal", "high");
  add_meta_box("week20", "Week 20", "week20", "sermon", "normal", "high");
  add_meta_box("week21", "Week 21", "week21", "sermon", "normal", "high");

  add_meta_box('ns_meta', 'Next Steps', 'ns_meta', 'post', 'side', 'high');
  add_meta_box('ns_meta', 'Next Steps', 'ns_meta', 'page', 'side', 'high');

  $post_id = $_GET['post'] ? $_GET['post'] : $_POST['post_ID'] ;
  $template_file = get_post_meta($post_id,'_wp_page_template',TRUE);
    // check for a template type
    if ($template_file == 'article.php') {
      add_meta_box("feed_cat", "Feed Category", "feed_cat", "page", "side", "high");
    }
}
function hide_meta_boxes() {
  remove_meta_box( 'postcustom' , 'post' , 'normal' );
  remove_meta_box( 'postcustom' , 'page' , 'normal' );
}
add_action( 'admin_menu' , 'hide_meta_boxes' );

/* Week 1 */
function week1() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week1_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week1-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week1_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week1-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week1_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week1_title" value="<?php if (isset($custom['week1_title'])) { echo                 $custom["week1_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week1_date"  value="<?php if (isset($custom['week1_date']))  { echo date( 'F j, Y', $custom["week1_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week1_passage" value="<?php if (isset($custom['week1_passage'])) { echo $custom["week1_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week1_speaker" value="<?php if (isset($custom['week1_speaker'])) { echo $custom["week1_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week1_audio" name="week1_audio" value="<?php if (isset($custom['week1_audio'])) { echo $custom["week1_audio"][0]; } ?>" /><a class="week1-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week1_pdf"   name="week1_pdf"   value="<?php if (isset($custom['week1_pdf']))   { echo $custom["week1_pdf"][0];   } ?>" /><a class="week1-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week1_vimeo_title" value="<?php if (isset($custom['week1_vimeo_title'])) { echo $custom["week1_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week1_vimeo_id"    value="<?php if (isset($custom['week1_vimeo_id']))    { echo $custom["week1_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 2 */
function week2() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week2_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week2-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week2_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week2-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week2_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week2_title" value="<?php if (isset($custom['week2_title'])) { echo                 $custom["week2_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week2_date"  value="<?php if (isset($custom['week2_date']))  { echo date( 'F j, Y', $custom["week2_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week2_passage" value="<?php if (isset($custom['week2_passage'])) { echo $custom["week2_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week2_speaker" value="<?php if (isset($custom['week2_speaker'])) { echo $custom["week2_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week2_audio" name="week2_audio" value="<?php if (isset($custom['week2_audio'])) { echo $custom["week2_audio"][0]; } ?>" /><a class="week2-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week2_pdf"   name="week2_pdf"   value="<?php if (isset($custom['week2_pdf']))   { echo $custom["week2_pdf"][0];   } ?>" /><a class="week2-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week2_vimeo_title" value="<?php if (isset($custom['week2_vimeo_title'])) { echo $custom["week2_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week2_vimeo_id"    value="<?php if (isset($custom['week2_vimeo_id']))    { echo $custom["week2_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 3 */
function week3() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week3_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week3-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week3_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week3-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week3_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week3_title" value="<?php if (isset($custom['week3_title'])) { echo                 $custom["week3_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week3_date"  value="<?php if (isset($custom['week3_date']))  { echo date( 'F j, Y', $custom["week3_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week3_passage" value="<?php if (isset($custom['week3_passage'])) { echo $custom["week3_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week3_speaker" value="<?php if (isset($custom['week3_speaker'])) { echo $custom["week3_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week3_audio" name="week3_audio" value="<?php if (isset($custom['week3_audio'])) { echo $custom["week3_audio"][0]; } ?>" /><a class="week3-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week3_pdf"   name="week3_pdf"   value="<?php if (isset($custom['week3_pdf']))   { echo $custom["week3_pdf"][0];   } ?>" /><a class="week3-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week3_vimeo_title" value="<?php if (isset($custom['week3_vimeo_title'])) { echo $custom["week3_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week3_vimeo_id"    value="<?php if (isset($custom['week3_vimeo_id']))    { echo $custom["week3_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 4 */
function week4() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week4_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week4-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week4_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week4-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week4_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week4_title" value="<?php if (isset($custom['week4_title'])) { echo                 $custom["week4_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week4_date"  value="<?php if (isset($custom['week4_date']))  { echo date( 'F j, Y', $custom["week4_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week4_passage" value="<?php if (isset($custom['week4_passage'])) { echo $custom["week4_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week4_speaker" value="<?php if (isset($custom['week4_speaker'])) { echo $custom["week4_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week4_audio" name="week4_audio" value="<?php if (isset($custom['week4_audio'])) { echo $custom["week4_audio"][0]; } ?>" /><a class="week4-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week4_pdf"   name="week4_pdf"   value="<?php if (isset($custom['week4_pdf']))   { echo $custom["week4_pdf"][0];   } ?>" /><a class="week4-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week4_vimeo_title" value="<?php if (isset($custom['week4_vimeo_title'])) { echo $custom["week4_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week4_vimeo_id"    value="<?php if (isset($custom['week4_vimeo_id']))    { echo $custom["week4_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 5 */
function week5() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week5_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week5-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week5_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week5-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week5_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week5_title" value="<?php if (isset($custom['week5_title'])) { echo                 $custom["week5_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week5_date"  value="<?php if (isset($custom['week5_date']))  { echo date( 'F j, Y', $custom["week5_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week5_passage" value="<?php if (isset($custom['week5_passage'])) { echo $custom["week5_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week5_speaker" value="<?php if (isset($custom['week5_speaker'])) { echo $custom["week5_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week5_audio" name="week5_audio" value="<?php if (isset($custom['week5_audio'])) { echo $custom["week5_audio"][0]; } ?>" /><a class="week5-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week5_pdf"   name="week5_pdf"   value="<?php if (isset($custom['week5_pdf']))   { echo $custom["week5_pdf"][0];   } ?>" /><a class="week5-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week5_vimeo_title" value="<?php if (isset($custom['week5_vimeo_title'])) { echo $custom["week5_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week5_vimeo_id"    value="<?php if (isset($custom['week5_vimeo_id']))    { echo $custom["week5_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 6 */
function week6() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week6_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week6-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week6_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week6-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week6_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week6_title" value="<?php if (isset($custom['week6_title'])) { echo                 $custom["week6_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week6_date"  value="<?php if (isset($custom['week6_date']))  { echo date( 'F j, Y', $custom["week6_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week6_passage" value="<?php if (isset($custom['week6_passage'])) { echo $custom["week6_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week6_speaker" value="<?php if (isset($custom['week6_speaker'])) { echo $custom["week6_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week6_audio" name="week6_audio" value="<?php if (isset($custom['week6_audio'])) { echo $custom["week6_audio"][0]; } ?>" /><a class="week6-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week6_pdf"   name="week6_pdf"   value="<?php if (isset($custom['week6_pdf']))   { echo $custom["week6_pdf"][0];   } ?>" /><a class="week6-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week6_vimeo_title" value="<?php if (isset($custom['week6_vimeo_title'])) { echo $custom["week6_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week6_vimeo_id"    value="<?php if (isset($custom['week6_vimeo_id']))    { echo $custom["week6_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 7 */
function week7() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week7_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week7-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week7_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week7-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week7_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week7_title" value="<?php if (isset($custom['week7_title'])) { echo                 $custom["week7_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week7_date"  value="<?php if (isset($custom['week7_date']))  { echo date( 'F j, Y', $custom["week7_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week7_passage" value="<?php if (isset($custom['week7_passage'])) { echo $custom["week7_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week7_speaker" value="<?php if (isset($custom['week7_speaker'])) { echo $custom["week7_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week7_audio" name="week7_audio" value="<?php if (isset($custom['week7_audio'])) { echo $custom["week7_audio"][0]; } ?>" /><a class="week7-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week7_pdf"   name="week7_pdf"   value="<?php if (isset($custom['week7_pdf']))   { echo $custom["week7_pdf"][0];   } ?>" /><a class="week7-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week7_vimeo_title" value="<?php if (isset($custom['week7_vimeo_title'])) { echo $custom["week7_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week7_vimeo_id"    value="<?php if (isset($custom['week7_vimeo_id']))    { echo $custom["week7_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 8 */
function week8() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week8_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week8-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week8_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week8-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week8_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week8_title" value="<?php if (isset($custom['week8_title'])) { echo                 $custom["week8_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week8_date"  value="<?php if (isset($custom['week8_date']))  { echo date( 'F j, Y', $custom["week8_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week8_passage" value="<?php if (isset($custom['week8_passage'])) { echo $custom["week8_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week8_speaker" value="<?php if (isset($custom['week8_speaker'])) { echo $custom["week8_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week8_audio" name="week8_audio" value="<?php if (isset($custom['week8_audio'])) { echo $custom["week8_audio"][0]; } ?>" /><a class="week8-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week8_pdf"   name="week8_pdf"   value="<?php if (isset($custom['week8_pdf']))   { echo $custom["week8_pdf"][0];   } ?>" /><a class="week8-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week8_vimeo_title" value="<?php if (isset($custom['week8_vimeo_title'])) { echo $custom["week8_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week8_vimeo_id"    value="<?php if (isset($custom['week8_vimeo_id']))    { echo $custom["week8_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 9 */
function week9() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week9_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week9-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week9_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week9-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week9_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week9_title" value="<?php if (isset($custom['week9_title'])) { echo                 $custom["week9_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week9_date"  value="<?php if (isset($custom['week9_date']))  { echo date( 'F j, Y', $custom["week9_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week9_passage" value="<?php if (isset($custom['week9_passage'])) { echo $custom["week9_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week9_speaker" value="<?php if (isset($custom['week9_speaker'])) { echo $custom["week9_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week9_audio" name="week9_audio" value="<?php if (isset($custom['week9_audio'])) { echo $custom["week9_audio"][0]; } ?>" /><a class="week9-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week9_pdf"   name="week9_pdf"   value="<?php if (isset($custom['week9_pdf']))   { echo $custom["week9_pdf"][0];   } ?>" /><a class="week9-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week9_vimeo_title" value="<?php if (isset($custom['week9_vimeo_title'])) { echo $custom["week9_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week9_vimeo_id"    value="<?php if (isset($custom['week9_vimeo_id']))    { echo $custom["week9_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 10 */
function week10() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week10_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week10-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week10_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week10-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week10_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week10_title" value="<?php if (isset($custom['week10_title'])) { echo                 $custom["week10_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week10_date"  value="<?php if (isset($custom['week10_date']))  { echo date( 'F j, Y', $custom["week10_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week10_passage" value="<?php if (isset($custom['week10_passage'])) { echo $custom["week10_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week10_speaker" value="<?php if (isset($custom['week10_speaker'])) { echo $custom["week10_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week10_audio" name="week10_audio" value="<?php if (isset($custom['week10_audio'])) { echo $custom["week10_audio"][0]; } ?>" /><a class="week10-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week10_pdf"   name="week10_pdf"   value="<?php if (isset($custom['week10_pdf']))   { echo $custom["week10_pdf"][0];   } ?>" /><a class="week10-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week10_vimeo_title" value="<?php if (isset($custom['week10_vimeo_title'])) { echo $custom["week10_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week10_vimeo_id"    value="<?php if (isset($custom['week10_vimeo_id']))    { echo $custom["week10_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 11 */
function week11() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week11_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week11-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week11_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week11-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week11_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week11_title" value="<?php if (isset($custom['week11_title'])) { echo                 $custom["week11_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week11_date"  value="<?php if (isset($custom['week11_date']))  { echo date( 'F j, Y', $custom["week11_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week11_passage" value="<?php if (isset($custom['week11_passage'])) { echo $custom["week11_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week11_speaker" value="<?php if (isset($custom['week11_speaker'])) { echo $custom["week11_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week11_audio" name="week11_audio" value="<?php if (isset($custom['week11_audio'])) { echo $custom["week11_audio"][0]; } ?>" /><a class="week11-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week11_pdf"   name="week11_pdf"   value="<?php if (isset($custom['week11_pdf']))   { echo $custom["week11_pdf"][0];   } ?>" /><a class="week11-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week11_vimeo_title" value="<?php if (isset($custom['week11_vimeo_title'])) { echo $custom["week11_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week11_vimeo_id"    value="<?php if (isset($custom['week11_vimeo_id']))    { echo $custom["week11_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 12 */
function week12() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week12_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week12-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week12_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week12-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week12_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week12_title" value="<?php if (isset($custom['week12_title'])) { echo                 $custom["week12_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week12_date"  value="<?php if (isset($custom['week12_date']))  { echo date( 'F j, Y', $custom["week12_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week12_passage" value="<?php if (isset($custom['week12_passage'])) { echo $custom["week12_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week12_speaker" value="<?php if (isset($custom['week12_speaker'])) { echo $custom["week12_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week12_audio" name="week12_audio" value="<?php if (isset($custom['week12_audio'])) { echo $custom["week12_audio"][0]; } ?>" /><a class="week12-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week12_pdf"   name="week12_pdf"   value="<?php if (isset($custom['week12_pdf']))   { echo $custom["week12_pdf"][0];   } ?>" /><a class="week12-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week12_vimeo_title" value="<?php if (isset($custom['week12_vimeo_title'])) { echo $custom["week12_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week12_vimeo_id"    value="<?php if (isset($custom['week12_vimeo_id']))    { echo $custom["week12_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 13 */
function week13() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week13_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week13-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week13_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week13-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week13_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week13_title" value="<?php if (isset($custom['week13_title'])) { echo                 $custom["week13_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week13_date"  value="<?php if (isset($custom['week13_date']))  { echo date( 'F j, Y', $custom["week13_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week13_passage" value="<?php if (isset($custom['week13_passage'])) { echo $custom["week13_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week13_speaker" value="<?php if (isset($custom['week13_speaker'])) { echo $custom["week13_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week13_audio" name="week13_audio" value="<?php if (isset($custom['week13_audio'])) { echo $custom["week13_audio"][0]; } ?>" /><a class="week13-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week13_pdf"   name="week13_pdf"   value="<?php if (isset($custom['week13_pdf']))   { echo $custom["week13_pdf"][0];   } ?>" /><a class="week13-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week13_vimeo_title" value="<?php if (isset($custom['week13_vimeo_title'])) { echo $custom["week13_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week13_vimeo_id"    value="<?php if (isset($custom['week13_vimeo_id']))    { echo $custom["week13_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 14 */
function week14() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week14_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week14-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week14_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week14-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week14_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week14_title" value="<?php if (isset($custom['week14_title'])) { echo                 $custom["week14_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week14_date"  value="<?php if (isset($custom['week14_date']))  { echo date( 'F j, Y', $custom["week14_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week14_passage" value="<?php if (isset($custom['week14_passage'])) { echo $custom["week14_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week14_speaker" value="<?php if (isset($custom['week14_speaker'])) { echo $custom["week14_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week14_audio" name="week14_audio" value="<?php if (isset($custom['week14_audio'])) { echo $custom["week14_audio"][0]; } ?>" /><a class="week14-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week14_pdf"   name="week14_pdf"   value="<?php if (isset($custom['week14_pdf']))   { echo $custom["week14_pdf"][0];   } ?>" /><a class="week14-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week14_vimeo_title" value="<?php if (isset($custom['week14_vimeo_title'])) { echo $custom["week14_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week14_vimeo_id"    value="<?php if (isset($custom['week14_vimeo_id']))    { echo $custom["week14_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 15 */
function week15() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week15_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week15-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week15_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week15-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week15_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week15_title" value="<?php if (isset($custom['week15_title'])) { echo                 $custom["week15_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week15_date"  value="<?php if (isset($custom['week15_date']))  { echo date( 'F j, Y', $custom["week15_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week15_passage" value="<?php if (isset($custom['week15_passage'])) { echo $custom["week15_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week15_speaker" value="<?php if (isset($custom['week15_speaker'])) { echo $custom["week15_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week15_audio" name="week15_audio" value="<?php if (isset($custom['week15_audio'])) { echo $custom["week15_audio"][0]; } ?>" /><a class="week15-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week15_pdf"   name="week15_pdf"   value="<?php if (isset($custom['week15_pdf']))   { echo $custom["week15_pdf"][0];   } ?>" /><a class="week15-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week15_vimeo_title" value="<?php if (isset($custom['week15_vimeo_title'])) { echo $custom["week15_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week15_vimeo_id"    value="<?php if (isset($custom['week15_vimeo_id']))    { echo $custom["week15_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 16 */
function week16() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week16_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week16-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week16_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week16-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week16_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week16_title" value="<?php if (isset($custom['week16_title'])) { echo                 $custom["week16_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week16_date"  value="<?php if (isset($custom['week16_date']))  { echo date( 'F j, Y', $custom["week16_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week16_passage" value="<?php if (isset($custom['week16_passage'])) { echo $custom["week16_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week16_speaker" value="<?php if (isset($custom['week16_speaker'])) { echo $custom["week16_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week16_audio" name="week16_audio" value="<?php if (isset($custom['week16_audio'])) { echo $custom["week16_audio"][0]; } ?>" /><a class="week16-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week16_pdf"   name="week16_pdf"   value="<?php if (isset($custom['week16_pdf']))   { echo $custom["week16_pdf"][0];   } ?>" /><a class="week16-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week16_vimeo_title" value="<?php if (isset($custom['week16_vimeo_title'])) { echo $custom["week16_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week16_vimeo_id"    value="<?php if (isset($custom['week16_vimeo_id']))    { echo $custom["week16_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 17 */
function week17() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week17_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week17-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week17_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week17-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week17_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week17_title" value="<?php if (isset($custom['week17_title'])) { echo                 $custom["week17_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week17_date"  value="<?php if (isset($custom['week17_date']))  { echo date( 'F j, Y', $custom["week17_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week17_passage" value="<?php if (isset($custom['week17_passage'])) { echo $custom["week17_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week17_speaker" value="<?php if (isset($custom['week17_speaker'])) { echo $custom["week17_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week17_audio" name="week17_audio" value="<?php if (isset($custom['week17_audio'])) { echo $custom["week17_audio"][0]; } ?>" /><a class="week17-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week17_pdf"   name="week17_pdf"   value="<?php if (isset($custom['week17_pdf']))   { echo $custom["week17_pdf"][0];   } ?>" /><a class="week17-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week17_vimeo_title" value="<?php if (isset($custom['week17_vimeo_title'])) { echo $custom["week17_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week17_vimeo_id"    value="<?php if (isset($custom['week17_vimeo_id']))    { echo $custom["week17_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 18 */
function week18() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week18_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week18-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week18_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week18-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week18_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week18_title" value="<?php if (isset($custom['week18_title'])) { echo                 $custom["week18_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week18_date"  value="<?php if (isset($custom['week18_date']))  { echo date( 'F j, Y', $custom["week18_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week18_passage" value="<?php if (isset($custom['week18_passage'])) { echo $custom["week18_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week18_speaker" value="<?php if (isset($custom['week18_speaker'])) { echo $custom["week18_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week18_audio" name="week18_audio" value="<?php if (isset($custom['week18_audio'])) { echo $custom["week18_audio"][0]; } ?>" /><a class="week18-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week18_pdf"   name="week18_pdf"   value="<?php if (isset($custom['week18_pdf']))   { echo $custom["week18_pdf"][0];   } ?>" /><a class="week18-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week18_vimeo_title" value="<?php if (isset($custom['week18_vimeo_title'])) { echo $custom["week18_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week18_vimeo_id"    value="<?php if (isset($custom['week18_vimeo_id']))    { echo $custom["week18_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 19 */
function week19() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week19_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week19-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week19_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week19-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week19_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week19_title" value="<?php if (isset($custom['week19_title'])) { echo                 $custom["week19_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week19_date"  value="<?php if (isset($custom['week19_date']))  { echo date( 'F j, Y', $custom["week19_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week19_passage" value="<?php if (isset($custom['week19_passage'])) { echo $custom["week19_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week19_speaker" value="<?php if (isset($custom['week19_speaker'])) { echo $custom["week19_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week19_audio" name="week19_audio" value="<?php if (isset($custom['week19_audio'])) { echo $custom["week19_audio"][0]; } ?>" /><a class="week19-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week19_pdf"   name="week19_pdf"   value="<?php if (isset($custom['week19_pdf']))   { echo $custom["week19_pdf"][0];   } ?>" /><a class="week19-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week19_vimeo_title" value="<?php if (isset($custom['week19_vimeo_title'])) { echo $custom["week19_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week19_vimeo_id"    value="<?php if (isset($custom['week19_vimeo_id']))    { echo $custom["week19_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 20 */
function week20() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week20_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week20-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week20_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week20-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week20_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week20_title" value="<?php if (isset($custom['week20_title'])) { echo                 $custom["week20_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week20_date"  value="<?php if (isset($custom['week20_date']))  { echo date( 'F j, Y', $custom["week20_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week20_passage" value="<?php if (isset($custom['week20_passage'])) { echo $custom["week20_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week20_speaker" value="<?php if (isset($custom['week20_speaker'])) { echo $custom["week20_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week20_audio" name="week20_audio" value="<?php if (isset($custom['week20_audio'])) { echo $custom["week20_audio"][0]; } ?>" /><a class="week20-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week20_pdf"   name="week20_pdf"   value="<?php if (isset($custom['week20_pdf']))   { echo $custom["week20_pdf"][0];   } ?>" /><a class="week20-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week20_vimeo_title" value="<?php if (isset($custom['week20_vimeo_title'])) { echo $custom["week20_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week20_vimeo_id"    value="<?php if (isset($custom['week20_vimeo_id']))    { echo $custom["week20_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}

/* Week 21 */
function week21() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <script type="text/javascript">
    jQuery(document).ready(function(){jQuery("input[name='week21_date']").datepicker({dateFormat:'MM d, yy'});jQuery("#ui-datepicker-div").hide();});
    jQuery(document).ready(function(){jQuery('.week21-audio-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor=function(html){url=jQuery(html).attr('href');jQuery('#week21_audio').val(url);tb_remove();};return false;});});
    jQuery(document).ready(function(){jQuery('.week21-pdf-upload').click(function(){tb_show('','media-upload.php?TB_iframe=true');window.send_to_editor = function(html){url = jQuery(html).attr('href');jQuery('#week21_pdf').val(url);tb_remove();};return false;});});
  </script>

  <p>
    <label>Title</label><input type="text" size="40" name="week21_title" value="<?php if (isset($custom['week21_title'])) { echo                 $custom["week21_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Date</label> <input type="text" size="25" name="week21_date"  value="<?php if (isset($custom['week21_date']))  { echo date( 'F j, Y', $custom["week21_date"][0]); } ?>" />
  </p>

  <p>
    <label>Passage</label><input type="text" size="25" name="week21_passage" value="<?php if (isset($custom['week21_passage'])) { echo $custom["week21_passage"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Speaker</label><input type="text" size="25" name="week21_speaker" value="<?php if (isset($custom['week21_speaker'])) { echo $custom["week21_speaker"][0]; } ?>" />
  </p>

  <p>
    <label>MP3 File    <input type="text" size="25" id="week21_audio" name="week21_audio" value="<?php if (isset($custom['week21_audio'])) { echo $custom["week21_audio"][0]; } ?>" /><a class="week21-audio-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>&nbsp;&nbsp;&nbsp;
    <label>Presentation<input type="text" size="25" id="week21_pdf"   name="week21_pdf"   value="<?php if (isset($custom['week21_pdf']))   { echo $custom["week21_pdf"][0];   } ?>" /><a class="week21-pdf-upload menu-top menu-top-first menu-top-last button thickbox">Upload</a></label>
  </p>

  <p>
    <label>Vimeo Title</label><input type="text" size="25" name="week21_vimeo_title" value="<?php if (isset($custom['week21_vimeo_title'])) { echo $custom["week21_vimeo_title"][0]; } ?>" />&nbsp;&nbsp;&nbsp;
    <label>Vimeo ID</label>   <input type="text" size="25" name="week21_vimeo_id"    value="<?php if (isset($custom['week21_vimeo_id']))    { echo $custom["week21_vimeo_id"][0];    } ?>" />
  </p>
  <?php
}



/* Save Details */
add_action('save_post', 'save_details');


function save_details(){
  global $post;
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
  return $post_id;
  }

  if( defined('DOING_AJAX') && DOING_AJAX ) { //Prevents the metaboxes from being overwritten while quick editing.
  return $post_id;
  }

  if( ereg('/\edit\.php', $_SERVER['REQUEST_URI']) ) { //Detects if the save action is coming from a quick edit/batch edit.
  return $post_id;
  }
  // save all meta data
  if (isset($_POST['ns1'])) { update_post_meta($post->ID, "ns1", $_POST["ns1"]); }
  if (isset($_POST['ns2'])) { update_post_meta($post->ID, "ns2", $_POST["ns2"]); }
  if (isset($_POST['ns3'])) { update_post_meta($post->ID, "ns3", $_POST["ns3"]); }

  if (isset($_POST['week1_title']))       { update_post_meta($post->ID, "week1_title",           $_POST["week1_title"]); }
  if (isset($_POST['week1_passage']))     { update_post_meta($post->ID, "week1_passage",         $_POST["week1_passage"]); }
  if (isset($_POST['week1_speaker']))     { update_post_meta($post->ID, "week1_speaker",         $_POST["week1_speaker"]); }
  if (isset($_POST['week1_audio']))       { update_post_meta($post->ID, "week1_audio",           $_POST["week1_audio"]); }
  if (isset($_POST['week1_vimeo_title'])) { update_post_meta($post->ID, "week1_vimeo_title",     $_POST["week1_vimeo_title"]); }
  if (isset($_POST['week1_vimeo_id']))    { update_post_meta($post->ID, "week1_vimeo_id",        $_POST["week1_vimeo_id"]); }
  if (isset($_POST['week1_pdf']))         { update_post_meta($post->ID, "week1_pdf",             $_POST["week1_pdf"]); }
  if (isset($_POST['week1_date']))        { update_post_meta($post->ID, "week1_date", strtotime( $_POST["week1_date"] )); }

  if (isset($_POST['week2_title']))       { update_post_meta($post->ID, "week2_title",           $_POST["week2_title"]); }
  if (isset($_POST['week2_passage']))     { update_post_meta($post->ID, "week2_passage",         $_POST["week2_passage"]); }
  if (isset($_POST['week2_speaker']))     { update_post_meta($post->ID, "week2_speaker",         $_POST["week2_speaker"]); }
  if (isset($_POST['week2_audio']))       { update_post_meta($post->ID, "week2_audio",           $_POST["week2_audio"]); }
  if (isset($_POST['week2_vimeo_title'])) { update_post_meta($post->ID, "week2_vimeo_title",     $_POST["week2_vimeo_title"]); }
  if (isset($_POST['week2_vimeo_id']))    { update_post_meta($post->ID, "week2_vimeo_id",        $_POST["week2_vimeo_id"]); }
  if (isset($_POST['week2_pdf']))         { update_post_meta($post->ID, "week2_pdf",             $_POST["week2_pdf"]); }
  if (isset($_POST['week2_date']))        { update_post_meta($post->ID, "week2_date", strtotime( $_POST["week2_date"] )); }

  if (isset($_POST['week3_title']))       { update_post_meta($post->ID, "week3_title",           $_POST["week3_title"]); }
  if (isset($_POST['week3_passage']))     { update_post_meta($post->ID, "week3_passage",         $_POST["week3_passage"]); }
  if (isset($_POST['week3_speaker']))     { update_post_meta($post->ID, "week3_speaker",         $_POST["week3_speaker"]); }
  if (isset($_POST['week3_audio']))       { update_post_meta($post->ID, "week3_audio",           $_POST["week3_audio"]); }
  if (isset($_POST['week3_vimeo_title'])) { update_post_meta($post->ID, "week3_vimeo_title",     $_POST["week3_vimeo_title"]); }
  if (isset($_POST['week3_vimeo_id']))    { update_post_meta($post->ID, "week3_vimeo_id",        $_POST["week3_vimeo_id"]); }
  if (isset($_POST['week3_pdf']))         { update_post_meta($post->ID, "week3_pdf",             $_POST["week3_pdf"]); }
  if (isset($_POST['week3_date']))        { update_post_meta($post->ID, "week3_date", strtotime( $_POST["week3_date"] )); }

  if (isset($_POST['week4_title']))       { update_post_meta($post->ID, "week4_title",           $_POST["week4_title"]); }
  if (isset($_POST['week4_passage']))     { update_post_meta($post->ID, "week4_passage",         $_POST["week4_passage"]); }
  if (isset($_POST['week4_speaker']))     { update_post_meta($post->ID, "week4_speaker",         $_POST["week4_speaker"]); }
  if (isset($_POST['week4_audio']))       { update_post_meta($post->ID, "week4_audio",           $_POST["week4_audio"]); }
  if (isset($_POST['week4_vimeo_title'])) { update_post_meta($post->ID, "week4_vimeo_title",     $_POST["week4_vimeo_title"]); }
  if (isset($_POST['week4_vimeo_id']))    { update_post_meta($post->ID, "week4_vimeo_id",        $_POST["week4_vimeo_id"]); }
  if (isset($_POST['week4_pdf']))         { update_post_meta($post->ID, "week4_pdf",             $_POST["week4_pdf"]); }
  if (isset($_POST['week4_date']))        { update_post_meta($post->ID, "week4_date", strtotime( $_POST["week4_date"] )); }

  if (isset($_POST['week5_title']))       { update_post_meta($post->ID, "week5_title",           $_POST["week5_title"]); }
  if (isset($_POST['week5_passage']))     { update_post_meta($post->ID, "week5_passage",         $_POST["week5_passage"]); }
  if (isset($_POST['week5_speaker']))     { update_post_meta($post->ID, "week5_speaker",         $_POST["week5_speaker"]); }
  if (isset($_POST['week5_audio']))       { update_post_meta($post->ID, "week5_audio",           $_POST["week5_audio"]); }
  if (isset($_POST['week5_vimeo_title'])) { update_post_meta($post->ID, "week5_vimeo_title",     $_POST["week5_vimeo_title"]); }
  if (isset($_POST['week5_vimeo_id']))    { update_post_meta($post->ID, "week5_vimeo_id",        $_POST["week5_vimeo_id"]); }
  if (isset($_POST['week5_pdf']))         { update_post_meta($post->ID, "week5_pdf",             $_POST["week5_pdf"]); }
  if (isset($_POST['week5_date']))        { update_post_meta($post->ID, "week5_date", strtotime( $_POST["week5_date"] )); }

  if (isset($_POST['week6_title']))       { update_post_meta($post->ID, "week6_title",           $_POST["week6_title"]); }
  if (isset($_POST['week6_passage']))     { update_post_meta($post->ID, "week6_passage",         $_POST["week6_passage"]); }
  if (isset($_POST['week6_speaker']))     { update_post_meta($post->ID, "week6_speaker",         $_POST["week6_speaker"]); }
  if (isset($_POST['week6_audio']))       { update_post_meta($post->ID, "week6_audio",           $_POST["week6_audio"]); }
  if (isset($_POST['week6_vimeo_title'])) { update_post_meta($post->ID, "week6_vimeo_title",     $_POST["week6_vimeo_title"]); }
  if (isset($_POST['week6_vimeo_id']))    { update_post_meta($post->ID, "week6_vimeo_id",        $_POST["week6_vimeo_id"]); }
  if (isset($_POST['week6_pdf']))         { update_post_meta($post->ID, "week6_pdf",             $_POST["week6_pdf"]); }
  if (isset($_POST['week6_date']))        { update_post_meta($post->ID, "week6_date", strtotime( $_POST["week6_date"] )); }

  if (isset($_POST['week7_title']))       { update_post_meta($post->ID, "week7_title",           $_POST["week7_title"]); }
  if (isset($_POST['week7_passage']))     { update_post_meta($post->ID, "week7_passage",         $_POST["week7_passage"]); }
  if (isset($_POST['week7_speaker']))     { update_post_meta($post->ID, "week7_speaker",         $_POST["week7_speaker"]); }
  if (isset($_POST['week7_audio']))       { update_post_meta($post->ID, "week7_audio",           $_POST["week7_audio"]); }
  if (isset($_POST['week7_vimeo_title'])) { update_post_meta($post->ID, "week7_vimeo_title",     $_POST["week7_vimeo_title"]); }
  if (isset($_POST['week7_vimeo_id']))    { update_post_meta($post->ID, "week7_vimeo_id",        $_POST["week7_vimeo_id"]); }
  if (isset($_POST['week7_pdf']))         { update_post_meta($post->ID, "week7_pdf",             $_POST["week7_pdf"]); }
  if (isset($_POST['week7_date']))        { update_post_meta($post->ID, "week7_date", strtotime( $_POST["week7_date"] )); }

  if (isset($_POST['week8_title']))       { update_post_meta($post->ID, "week8_title",           $_POST["week8_title"]); }
  if (isset($_POST['week8_passage']))     { update_post_meta($post->ID, "week8_passage",         $_POST["week8_passage"]); }
  if (isset($_POST['week8_speaker']))     { update_post_meta($post->ID, "week8_speaker",         $_POST["week8_speaker"]); }
  if (isset($_POST['week8_audio']))       { update_post_meta($post->ID, "week8_audio",           $_POST["week8_audio"]); }
  if (isset($_POST['week8_vimeo_title'])) { update_post_meta($post->ID, "week8_vimeo_title",     $_POST["week8_vimeo_title"]); }
  if (isset($_POST['week8_vimeo_id']))    { update_post_meta($post->ID, "week8_vimeo_id",        $_POST["week8_vimeo_id"]); }
  if (isset($_POST['week8_pdf']))         { update_post_meta($post->ID, "week8_pdf",             $_POST["week8_pdf"]); }
  if (isset($_POST['week8_date']))        { update_post_meta($post->ID, "week8_date", strtotime( $_POST["week8_date"] )); }

  if (isset($_POST['week9_title']))       { update_post_meta($post->ID, "week9_title",           $_POST["week9_title"]); }
  if (isset($_POST['week9_passage']))     { update_post_meta($post->ID, "week9_passage",         $_POST["week9_passage"]); }
  if (isset($_POST['week9_speaker']))     { update_post_meta($post->ID, "week9_speaker",         $_POST["week9_speaker"]); }
  if (isset($_POST['week9_audio']))       { update_post_meta($post->ID, "week9_audio",           $_POST["week9_audio"]); }
  if (isset($_POST['week9_vimeo_title'])) { update_post_meta($post->ID, "week9_vimeo_title",     $_POST["week9_vimeo_title"]); }
  if (isset($_POST['week9_vimeo_id']))    { update_post_meta($post->ID, "week9_vimeo_id",        $_POST["week9_vimeo_id"]); }
  if (isset($_POST['week9_pdf']))         { update_post_meta($post->ID, "week9_pdf",             $_POST["week9_pdf"]); }
  if (isset($_POST['week9_date']))        { update_post_meta($post->ID, "week9_date", strtotime( $_POST["week9_date"] )); }

  if (isset($_POST['week10_title']))       { update_post_meta($post->ID, "week10_title",           $_POST["week10_title"]); }
  if (isset($_POST['week10_passage']))     { update_post_meta($post->ID, "week10_passage",         $_POST["week10_passage"]); }
  if (isset($_POST['week10_speaker']))     { update_post_meta($post->ID, "week10_speaker",         $_POST["week10_speaker"]); }
  if (isset($_POST['week10_audio']))       { update_post_meta($post->ID, "week10_audio",           $_POST["week10_audio"]); }
  if (isset($_POST['week10_vimeo_title'])) { update_post_meta($post->ID, "week10_vimeo_title",     $_POST["week10_vimeo_title"]); }
  if (isset($_POST['week10_vimeo_id']))    { update_post_meta($post->ID, "week10_vimeo_id",        $_POST["week10_vimeo_id"]); }
  if (isset($_POST['week10_pdf']))         { update_post_meta($post->ID, "week10_pdf",             $_POST["week10_pdf"]); }
  if (isset($_POST['week10_date']))        { update_post_meta($post->ID, "week10_date", strtotime( $_POST["week10_date"] )); }

  if (isset($_POST['week11_title']))       { update_post_meta($post->ID, "week11_title",           $_POST["week11_title"]); }
  if (isset($_POST['week11_passage']))     { update_post_meta($post->ID, "week11_passage",         $_POST["week11_passage"]); }
  if (isset($_POST['week11_speaker']))     { update_post_meta($post->ID, "week11_speaker",         $_POST["week11_speaker"]); }
  if (isset($_POST['week11_audio']))       { update_post_meta($post->ID, "week11_audio",           $_POST["week11_audio"]); }
  if (isset($_POST['week11_vimeo_title'])) { update_post_meta($post->ID, "week11_vimeo_title",     $_POST["week11_vimeo_title"]); }
  if (isset($_POST['week11_vimeo_id']))    { update_post_meta($post->ID, "week11_vimeo_id",        $_POST["week11_vimeo_id"]); }
  if (isset($_POST['week11_pdf']))         { update_post_meta($post->ID, "week11_pdf",             $_POST["week11_pdf"]); }
  if (isset($_POST['week11_date']))        { update_post_meta($post->ID, "week11_date", strtotime( $_POST["week11_date"] )); }

  if (isset($_POST['week12_title']))       { update_post_meta($post->ID, "week12_title",           $_POST["week12_title"]); }
  if (isset($_POST['week12_passage']))     { update_post_meta($post->ID, "week12_passage",         $_POST["week12_passage"]); }
  if (isset($_POST['week12_speaker']))     { update_post_meta($post->ID, "week12_speaker",         $_POST["week12_speaker"]); }
  if (isset($_POST['week12_audio']))       { update_post_meta($post->ID, "week12_audio",           $_POST["week12_audio"]); }
  if (isset($_POST['week12_vimeo_title'])) { update_post_meta($post->ID, "week12_vimeo_title",     $_POST["week12_vimeo_title"]); }
  if (isset($_POST['week12_vimeo_id']))    { update_post_meta($post->ID, "week12_vimeo_id",        $_POST["week12_vimeo_id"]); }
  if (isset($_POST['week12_pdf']))         { update_post_meta($post->ID, "week12_pdf",             $_POST["week12_pdf"]); }
  if (isset($_POST['week12_date']))        { update_post_meta($post->ID, "week12_date", strtotime( $_POST["week12_date"] )); }

  if (isset($_POST['week13_title']))       { update_post_meta($post->ID, "week13_title",           $_POST["week13_title"]); }
  if (isset($_POST['week13_passage']))     { update_post_meta($post->ID, "week13_passage",         $_POST["week13_passage"]); }
  if (isset($_POST['week13_speaker']))     { update_post_meta($post->ID, "week13_speaker",         $_POST["week13_speaker"]); }
  if (isset($_POST['week13_audio']))       { update_post_meta($post->ID, "week13_audio",           $_POST["week13_audio"]); }
  if (isset($_POST['week13_vimeo_title'])) { update_post_meta($post->ID, "week13_vimeo_title",     $_POST["week13_vimeo_title"]); }
  if (isset($_POST['week13_vimeo_id']))    { update_post_meta($post->ID, "week13_vimeo_id",        $_POST["week13_vimeo_id"]); }
  if (isset($_POST['week13_pdf']))         { update_post_meta($post->ID, "week13_pdf",             $_POST["week13_pdf"]); }
  if (isset($_POST['week13_date']))        { update_post_meta($post->ID, "week13_date", strtotime( $_POST["week13_date"] )); }

  if (isset($_POST['week14_title']))       { update_post_meta($post->ID, "week14_title",           $_POST["week14_title"]); }
  if (isset($_POST['week14_passage']))     { update_post_meta($post->ID, "week14_passage",         $_POST["week14_passage"]); }
  if (isset($_POST['week14_speaker']))     { update_post_meta($post->ID, "week14_speaker",         $_POST["week14_speaker"]); }
  if (isset($_POST['week14_audio']))       { update_post_meta($post->ID, "week14_audio",           $_POST["week14_audio"]); }
  if (isset($_POST['week14_vimeo_title'])) { update_post_meta($post->ID, "week14_vimeo_title",     $_POST["week14_vimeo_title"]); }
  if (isset($_POST['week14_vimeo_id']))    { update_post_meta($post->ID, "week14_vimeo_id",        $_POST["week14_vimeo_id"]); }
  if (isset($_POST['week14_pdf']))         { update_post_meta($post->ID, "week14_pdf",             $_POST["week14_pdf"]); }
  if (isset($_POST['week14_date']))        { update_post_meta($post->ID, "week14_date", strtotime( $_POST["week14_date"] )); }

  if (isset($_POST['week15_title']))       { update_post_meta($post->ID, "week15_title",           $_POST["week15_title"]); }
  if (isset($_POST['week15_passage']))     { update_post_meta($post->ID, "week15_passage",         $_POST["week15_passage"]); }
  if (isset($_POST['week15_speaker']))     { update_post_meta($post->ID, "week15_speaker",         $_POST["week15_speaker"]); }
  if (isset($_POST['week15_audio']))       { update_post_meta($post->ID, "week15_audio",           $_POST["week15_audio"]); }
  if (isset($_POST['week15_vimeo_title'])) { update_post_meta($post->ID, "week15_vimeo_title",     $_POST["week15_vimeo_title"]); }
  if (isset($_POST['week15_vimeo_id']))    { update_post_meta($post->ID, "week15_vimeo_id",        $_POST["week15_vimeo_id"]); }
  if (isset($_POST['week15_pdf']))         { update_post_meta($post->ID, "week15_pdf",             $_POST["week15_pdf"]); }
  if (isset($_POST['week15_date']))        { update_post_meta($post->ID, "week15_date", strtotime( $_POST["week15_date"] )); }

  if (isset($_POST['week16_title']))       { update_post_meta($post->ID, "week16_title",           $_POST["week16_title"]); }
  if (isset($_POST['week16_passage']))     { update_post_meta($post->ID, "week16_passage",         $_POST["week16_passage"]); }
  if (isset($_POST['week16_speaker']))     { update_post_meta($post->ID, "week16_speaker",         $_POST["week16_speaker"]); }
  if (isset($_POST['week16_audio']))       { update_post_meta($post->ID, "week16_audio",           $_POST["week16_audio"]); }
  if (isset($_POST['week16_vimeo_title'])) { update_post_meta($post->ID, "week16_vimeo_title",     $_POST["week16_vimeo_title"]); }
  if (isset($_POST['week16_vimeo_id']))    { update_post_meta($post->ID, "week16_vimeo_id",        $_POST["week16_vimeo_id"]); }
  if (isset($_POST['week16_pdf']))         { update_post_meta($post->ID, "week16_pdf",             $_POST["week16_pdf"]); }
  if (isset($_POST['week16_date']))        { update_post_meta($post->ID, "week16_date", strtotime( $_POST["week16_date"] )); }

  if (isset($_POST['week17_title']))       { update_post_meta($post->ID, "week17_title",           $_POST["week17_title"]); }
  if (isset($_POST['week17_passage']))     { update_post_meta($post->ID, "week17_passage",         $_POST["week17_passage"]); }
  if (isset($_POST['week17_speaker']))     { update_post_meta($post->ID, "week17_speaker",         $_POST["week17_speaker"]); }
  if (isset($_POST['week17_audio']))       { update_post_meta($post->ID, "week17_audio",           $_POST["week17_audio"]); }
  if (isset($_POST['week17_vimeo_title'])) { update_post_meta($post->ID, "week17_vimeo_title",     $_POST["week17_vimeo_title"]); }
  if (isset($_POST['week17_vimeo_id']))    { update_post_meta($post->ID, "week17_vimeo_id",        $_POST["week17_vimeo_id"]); }
  if (isset($_POST['week17_pdf']))         { update_post_meta($post->ID, "week17_pdf",             $_POST["week17_pdf"]); }
  if (isset($_POST['week17_date']))        { update_post_meta($post->ID, "week17_date", strtotime( $_POST["week17_date"] )); }

  if (isset($_POST['week18_title']))       { update_post_meta($post->ID, "week18_title",           $_POST["week18_title"]); }
  if (isset($_POST['week18_passage']))     { update_post_meta($post->ID, "week18_passage",         $_POST["week18_passage"]); }
  if (isset($_POST['week18_speaker']))     { update_post_meta($post->ID, "week18_speaker",         $_POST["week18_speaker"]); }
  if (isset($_POST['week18_audio']))       { update_post_meta($post->ID, "week18_audio",           $_POST["week18_audio"]); }
  if (isset($_POST['week18_vimeo_title'])) { update_post_meta($post->ID, "week18_vimeo_title",     $_POST["week18_vimeo_title"]); }
  if (isset($_POST['week18_vimeo_id']))    { update_post_meta($post->ID, "week18_vimeo_id",        $_POST["week18_vimeo_id"]); }
  if (isset($_POST['week18_pdf']))         { update_post_meta($post->ID, "week18_pdf",             $_POST["week18_pdf"]); }
  if (isset($_POST['week18_date']))        { update_post_meta($post->ID, "week18_date", strtotime( $_POST["week18_date"] )); }

  if (isset($_POST['week19_title']))       { update_post_meta($post->ID, "week19_title",           $_POST["week19_title"]); }
  if (isset($_POST['week19_passage']))     { update_post_meta($post->ID, "week19_passage",         $_POST["week19_passage"]); }
  if (isset($_POST['week19_speaker']))     { update_post_meta($post->ID, "week19_speaker",         $_POST["week19_speaker"]); }
  if (isset($_POST['week19_audio']))       { update_post_meta($post->ID, "week19_audio",           $_POST["week19_audio"]); }
  if (isset($_POST['week19_vimeo_title'])) { update_post_meta($post->ID, "week19_vimeo_title",     $_POST["week19_vimeo_title"]); }
  if (isset($_POST['week19_vimeo_id']))    { update_post_meta($post->ID, "week19_vimeo_id",        $_POST["week19_vimeo_id"]); }
  if (isset($_POST['week19_pdf']))         { update_post_meta($post->ID, "week19_pdf",             $_POST["week19_pdf"]); }
  if (isset($_POST['week19_date']))        { update_post_meta($post->ID, "week19_date", strtotime( $_POST["week19_date"] )); }

  if (isset($_POST['week20_title']))       { update_post_meta($post->ID, "week20_title",           $_POST["week20_title"]); }
  if (isset($_POST['week20_passage']))     { update_post_meta($post->ID, "week20_passage",         $_POST["week20_passage"]); }
  if (isset($_POST['week20_speaker']))     { update_post_meta($post->ID, "week20_speaker",         $_POST["week20_speaker"]); }
  if (isset($_POST['week20_audio']))       { update_post_meta($post->ID, "week20_audio",           $_POST["week20_audio"]); }
  if (isset($_POST['week20_vimeo_title'])) { update_post_meta($post->ID, "week20_vimeo_title",     $_POST["week20_vimeo_title"]); }
  if (isset($_POST['week20_vimeo_id']))    { update_post_meta($post->ID, "week20_vimeo_id",        $_POST["week20_vimeo_id"]); }
  if (isset($_POST['week20_pdf']))         { update_post_meta($post->ID, "week20_pdf",             $_POST["week20_pdf"]); }
  if (isset($_POST['week20_date']))        { update_post_meta($post->ID, "week20_date", strtotime( $_POST["week20_date"] )); }

  if (isset($_POST['week21_title']))       { update_post_meta($post->ID, "week21_title",           $_POST["week21_title"]); }
  if (isset($_POST['week21_passage']))     { update_post_meta($post->ID, "week21_passage",         $_POST["week21_passage"]); }
  if (isset($_POST['week21_speaker']))     { update_post_meta($post->ID, "week21_speaker",         $_POST["week21_speaker"]); }
  if (isset($_POST['week21_audio']))       { update_post_meta($post->ID, "week21_audio",           $_POST["week21_audio"]); }
  if (isset($_POST['week21_vimeo_title'])) { update_post_meta($post->ID, "week21_vimeo_title",     $_POST["week21_vimeo_title"]); }
  if (isset($_POST['week21_vimeo_id']))    { update_post_meta($post->ID, "week21_vimeo_id",        $_POST["week21_vimeo_id"]); }
  if (isset($_POST['week21_pdf']))         { update_post_meta($post->ID, "week21_pdf",             $_POST["week21_pdf"]); }
  if (isset($_POST['week21_date']))        { update_post_meta($post->ID, "week21_date", strtotime( $_POST["week21_date"] )); }

  if (isset($_POST['week21_date'])) { update_post_meta($post->ID, "feed_term", $_POST["feed_term"]); }

}
// END - Custom Fields

// BEGIN - Custom Columns in Admin
add_action("manage_posts_custom_column", "sermon_columns");
add_filter("manage_edit-sermon_columns", "sermon_edit_columns");

function sermon_edit_columns($columns) {
  $columns = array(
    "cb" => "<input type=\"checkbox\" />",
    "title" => "Series Title",
    "topics" => "Topics",
    "views" => "Views",
  );
  return $columns;
}

function sermon_columns($column){
  global $post;

  switch ($column){
    case "topics":
      echo get_the_term_list($post->ID, 'sermon-topics', '', ', ','');
      break;
    case "views":
      echo getPostViews($post->ID);
      break;
  }
}
// END - Custom Columns
/* End of Sermon Manager code */
