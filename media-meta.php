<?php
/**
 * Custom Media Meta
 * Source: http://net.tutsplus.com/tutorials/wordpress/creating-custom-fields-for-attachments-in-wordpress/
 */

add_filter("attachment_fields_to_edit", "my_attachment_fields_to_edit", null, 2);
add_filter("attachment_fields_to_save", "my_attachment_fields_to_save", null, 2);

function my_attachment_fields_to_edit($form_fields, $post) {
  if( substr($post->post_mime_type, 0, 5) == 'audio' ){
    $form_fields["duration"]["label"] = __("Duration");
    $form_fields["duration"]["value"] = get_post_meta($post->ID, "_duration", true);
    $form_fields["duration"]["helps"] = "hh:mm:ss";
    $form_fields["duration"]["required"] = TRUE;

    $form_fields["keywords"]["label"] = __("Keywords");
    $form_fields["keywords"]["value"] = get_post_meta($post->ID, "_keywords", true);
    $form_fields["keywords"]["helps"] = "All lowercase, separated by commas";
    $form_fields["keywords"]["required"] = TRUE;
  }
return $form_fields;
}

function my_attachment_fields_to_save($post, $attachment) {
if( isset($attachment['duration']) ) {
update_post_meta($post['ID'], '_duration', $attachment['duration']);
}
if( isset($attachment['keywords']) ) {
update_post_meta($post['ID'], '_keywords', $attachment['keywords']);
}
return $post;
}

//End Custom Media Meta
