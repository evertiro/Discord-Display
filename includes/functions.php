<?php
/**
 * Misc Functions
 *
 * @package     Discord_Display\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get available themes
 *
 * @since       1.0.0
 * @return      array $themes The available themes
 */
function discord_display_get_themes() {
	$themes = apply_filters( 'discord_display_themes', array(
		'default' => __( 'Default', 'discord-display' )
	) );

	return $themes;
}


/**
 * Channel sort filter
 *
 * @since       1.0.0
 * @param       string $key The key to sort by
 * @return      object $channels The sorted list
 */
function discord_display_channel_sort( $key ) {
	return function( $a, $b ) use ( $key ) {
		return strcmp( $a->{$key}, $b->{$key} );
	};
}