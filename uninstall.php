<?php

/**
 * Fired when the plugin is uninstalled.
 *
 *
 * @link       http://ptheme.com
 * @since      1.0.0
 *
 * @package    wp-random-post
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

setcookie( "randomIDs", "", time()-3600 );

$options = array( 'pt_randombtn_background', 'pt_randombtn_color', 'pt_randombtn_background_hover', 'pt_randombtn_font_hover', 'pt_randombtn_cookie_enabling' );
foreach ( $options as $option ) {
	delete_option( $option );
}
