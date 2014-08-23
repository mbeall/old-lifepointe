<?php
/**
 * Podcast Taxonomy
 * Source: http://themeshaper.com/2011/05/24/powering-your-design-with-wordpress/
 *
 * Description: Adds custom taxonomy "Podcast" for use with Sermon Archives
 */

/* Register a custom taxonomy for featuring pages */
register_taxonomy(
  'rss',
  'sermon',
  array(
    'labels' => array(
      'name' => _x( 'RSS', 'lifepointe' ),
    ),
    'public' => false,
    'rewrite' => array(  'slug' => 'podcast' ),
  )
);

/* Set a default term for the Podcast Page taxonomy */
function lifepointe_podcast_term() {
  wp_insert_term(
    'Podcast',
    'rss',
    array(
        'description'=> 'To be broadcasted to iTunes Podcast',
        'slug' => 'itpc',
      )
  );
}
add_action( 'after_setup_theme', 'lifepointe_podcast_term' );

/* Add a custom meta box for the Podcast Page taxonomy */
function lifepointe_add_meta_mox() {
  add_meta_box(
    'lifepointe-podcast',
    __( 'Include in Podcast', 'lifepointe' ),
    'lifepointe_create_meta_box',
    'sermon',
    'side',
    'core'
  );
}
add_action( 'add_meta_boxes', 'lifepointe_add_meta_mox' );

/* Create a custom meta box for the Podcast Page taxonomy */
function lifepointe_create_meta_box( $post ) {

  // Use nonce for verification
    wp_nonce_field( 'lifepointe_rss_sermon', 'lifepointe_rss_sermon_nonce' );

  // Retrieve the metadata values if the exist
  $use_as_feature = get_post_meta( $post->ID, '_use_as_feature', true );
  $disable_feature = get_post_meta( $post->ID, '_disable_feature', true );

  ?>
    <label for="use_as_feature">
      <input type="checkbox" name="use_as_feature" id="use_as_feature" <?php checked( 'on', $use_as_feature ); ?> />
      <?php printf( __( 'Include in %1$s podcast', 'lifepointe' ), '<em>' . get_bloginfo( 'title' ) . '</em>' ); ?>
    </label><br />
<label for="disable_feature">
      <input type="checkbox" name="disable_feature" id="disable_feature" <?php checked( 'on', $disable_feature ); ?> />
      Disable modal windows
    </label>
  <?php
}

/* Save the Podcast Page meta box data */
function lifepointe_save_meta_box_data( $post_id ) {

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  if ( ! wp_verify_nonce( $_POST['lifepointe_rss_sermon_nonce'], 'lifepointe_rss_sermon' ) )
    return $post_id;

  // verify if this is an auto save routine.
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    return $post_id;

  // Check permissions
  if ( 'page' == $_POST['post_type'] ) {
    if ( ! current_user_can( 'edit_page', $post_id ) )
      return $post_id;
  } else {
    if ( ! current_user_can( 'edit_post', $post_id ) )
      return $post_id;
  }

  // OK, we're authenticated: we need to find and save the data

  // Update use_as_feature value, default is off
  $use_as_feature = isset( $_POST['use_as_feature'] ) ? $_POST['use_as_feature'] : 'off';
  update_post_meta( $post_id, '_use_as_feature', $use_as_feature ); // Save the data

  if ( 'on' == $use_as_feature ) {
    // Add the Podcast term to this post
    wp_set_object_terms( $post_id, 'Podcast', 'rss' );
  } elseif ( 'off' == $use_as_feature ) {
    // Let's not use that term then
    wp_delete_object_term_relationships( $post_id, 'rss' );
  }

  // Update disable_feature value, default is off
  $disable_feature = isset( $_POST['disable_feature'] ) ? $_POST['disable_feature'] : 'off';
  update_post_meta( $post_id, '_disable_feature', $disable_feature ); // Save the data

}
add_action( 'save_post', 'lifepointe_save_meta_box_data' );
