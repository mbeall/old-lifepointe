<?php
// [lslide title="string" id="int"]
function lslide_shortcode( $atts, $content = null ) {
  extract( shortcode_atts( array(
    'title' => 'Show',
    'id' => rand()
  ), $atts ) );

  return '<a class="lslide" href="#" onclick="Effect.toggle(\''. esc_attr($id) .'\', \'slide\'); return false;">'. esc_attr($title) .'</a><div id="'. esc_attr($id) .'" style="display: none;"><div>' . $content . '</div></div>';
}
add_shortcode( 'lslide', 'lslide_shortcode' );

// [ltoggles]
function ltoggles_shortcode( $atts, $content = null ) {
  return '<div class="ltoggles"' . do_shortcode($content) . '</div>';
}
add_shortcode( 'ltoggles', 'ltoggles_shortcode' );

function ltoggle_shortcode( $atts, $content = null ) {
  extract( shortcode_atts( array(
    'title' => 'Toggle',
    'id' => rand()
  ), $atts ) );

  return '<a class="ltoggle" href="#" onclick="Effect.toggle(\''. esc_attr($id) .'\', \'slide\'); return false;">'. esc_attr($title) .'</a><div id="'. esc_attr($id) .'" style="display: none;"><div>' . str_replace("\r\n", '', $content) . '</div></div>';
}
add_shortcode( 'ltoggle', 'ltoggle_shortcode' );

// [lbutton color="string" size="string" link="url" target="_blank" class="string"]
function lbutton_shortcode( $atts, $content = null ) {
  extract( shortcode_atts( array(
    'color' => 'gray',
    'size' => 'small',
    'link' => '#',
    'target' => '',
    'class' => '',
  ), $atts ) );

  return '<a class="lbutton '. esc_attr($color) .' '. esc_attr($class) .'" href="'. esc_attr($link) .'" target="'. esc_attr($target) .'">' . $content . '</a>';
}
add_shortcode( 'lbutton', 'lbutton_shortcode' );
