<?php
/*
Plugin Name: WP Random Button
Plugin URI: http://ptheme.com/item/wp-random-button/
Description: This plugin allows you to display a random post in a popup window via shortcode or custom widget.

@link              http://ptheme.com
@since             1.0.0
@package           wp-random-button
 
Author: Leo
Version: 1.1
Author URI: http://ptheme.com/
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*-----------------------------------------------------------------------------------*/
/* Define the URL and DIR path */
/*-----------------------------------------------------------------------------------*/

define( 'DEV_PLUGIN__VERSION',            '1.0' );
define( 'DEV_PLUGIN__PLUGIN_DIR',         plugin_dir_url( __FILE__ ) );
define( 'DEV_PLUGIN__PLUGIN_FILE',        __FILE__ );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'class/class-wp-random-button.php';
$radnompost = new PtRandomPost();

function wprb_alter_query( $args ) {
	if ( !get_option( 'pt_randombtn_cookie_enabling' ) ) {
		unset( $args[ 'post__not_in' ] );
	}
	return $args;
}
add_filter( 'wprb_query', 'wprb_alter_query' );


function random_button_html($text = 'Random Post', $cat = '', $nocat = '') {
	return '<a class="animated infinite pulse random-button" data-nonce="'.wp_create_nonce( 'pt_random_stuff_nonce' ).'" data-toggle="modal" data-target=".random-overlay" data-cat="'.$cat.'" data-nocat="'.$nocat.'">'.$text.'</a>';
}

add_shortcode( 'wp_random_button', 'random_button_shortcode' ); // add shortcode: [wp_random_button]
function random_button_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'text' => 'Random Post',
		'cat'  => '',
		'nocat'=> '',
	), $atts, 'wp_random_button' );

	return random_button_html( $atts['text'], $atts['cat'], $atts['nocat'] );
}

require 'class/widget.php'; // add Random Post Button widget