<?php
/**
 * Discord API
 *
 * @package     Discord_Display\API
 * @since       1.0.0
*/

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Discord JSON API connector
 *
 * @since       1.0.0
 */
class Discord_API {


	/**
	 * @var         string $api_url The JSON API URL
	 * @since       1.0.0
	 */
	public $api_url;


	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $server_id The Discord server ID
	 * @return      void
	 */
	public function __construct( $server_id = null ) {
		// Bail if no server ID set
		if( ! $server_id ) {
			return false;
		}

		$this->api_url = 'https://discordapp.com/api/servers/' . $server_id . '/widget.json';
	}


	/**
	 * Fetch the JSON feed
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      object $response The JSON data for this widget
	 */
	public function fetch() {
		$response = false;

		if( $this->api_url ) {
			$response = wp_remote_retrieve_body( wp_remote_get( $this->api_url ) );
			$response = json_decode( $response );
		}

		return $response;
	}
}