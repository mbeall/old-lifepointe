<?php
/**
 * Insert Image with Custom Size into Post
 * Source: http://kucrut.org/insert-image-with-custom-size-into-post/
 * 
 * Allow Custom Image Sizes to be selected in Media Uploader utility
 */

function lifepointe_get_additional_image_sizes() {
  $sizes = array();
  global $_wp_additional_image_sizes;
  if ( isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes) ) {
    $sizes = apply_filters( 'intermediate_image_sizes', $_wp_additional_image_sizes );
    $sizes = apply_filters( 'lifepointe_get_additional_image_sizes', $_wp_additional_image_sizes );
  }

  return $sizes;
}
function lifepointe_additional_image_size_input_fields( $fields, $post ) {
  if ( !isset($fields['image-size']['html']) || substr($post->post_mime_type, 0, 5) != 'image' )
    return $fields;

  $sizes = lifepointe_get_additional_image_sizes();
  if ( !count($sizes) )
    return $fields;

  $items = array();
  foreach ( array_keys($sizes) as $size ) {
    $downsize = image_downsize( $post->ID, $size );
    $enabled = $downsize[3];
    $css_id = "image-size-{$s}-{$post->ID}";
    $label = apply_filters( 'lifepointe_image_size_name', $size );

    $html  = "<div class='image-size-item'>\n";
    $html .= "\t<input type='radio' " . disabled( $enabled, false, false ) . "name='attachments[{$post->ID}][image-size]' id='{$css_id}' value='{$size}' />\n";
    $html .= "\t<label for='{$css_id}'>{$label}</label>\n";
    if ( $enabled )
      $html .= "\t<label for='{$css_id}' class='help'>" . sprintf( "(%d × %d)", $downsize[1], $downsize[2] ). "</label>\n";
    $html .= "</div>";

    $items[] = $html;
  }

  $items = join( "\n", $items );
  $fields['image-size']['html'] = "{$fields['image-size']['html']}\n{$items}";

  return $fields;
}
add_filter( 'attachment_fields_to_edit', 'lifepointe_additional_image_size_input_fields', 11, 2 );
