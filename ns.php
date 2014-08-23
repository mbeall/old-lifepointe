<?php
/**
 * Next Steps
 */
function ns_meta() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
    <p><label>First Box</label> <input type="text" size="15" name="ns1" value="<?php if (isset($custom['ns1'])) { echo $custom["ns1"][0]; } ?>" /></p>
    <p><label>Second Box</label><input type="text" size="15" name="ns2" value="<?php if (isset($custom['ns2'])) { echo $custom["ns2"][0]; } ?>" /></p>
    <p><label>Third Box</label> <input type="text" size="15" name="ns3" value="<?php if (isset($custom['ns3'])) { echo $custom["ns3"][0]; } ?>" /></p><?php
}
/* Next Steps */
function feed_cat() {
  global $post;
  $custom = get_post_custom($post->ID);
?>
  <p><label>Category</label><input type="text" size="15" name="feed_term" value="<?php if (isset($custom['feed_term'])) { echo $custom["feed_term"][0]; } ?>" /></p><?php
}
