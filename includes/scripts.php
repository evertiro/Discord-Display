<?php
/**
 * Scripts
 *
 * @package     Discord_Display\Scripts
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Load scripts
 *
 * @since		1.0.0
 * @return		void
 */
function discord_display_scripts() {
	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'discord-display', DISCORD_DISPLAY_URL . 'assets/css/discord-display' . $suffix . '.css', array(), DISCORD_DISPLAY_VER );
	wp_enqueue_style( 'discord-display-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css' );
}
add_action( 'wp_enqueue_scripts', 'discord_display_scripts' );