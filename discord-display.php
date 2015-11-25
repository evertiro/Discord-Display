<?php
/**
 * Plugin Name:     Discord Display
 * Plugin URI:      http://wordpress.org/plugins/discord-display
 * Description:     Provides a simple native widget for displaying your Discord server
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:      https://section214.com
 * Text Domain:     discord-display
 *
 * @package         Discord_Display
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( ! class_exists( 'Discord_Display' ) ) {


	/**
	 * Main Discord_Display class
	 *
	 * @since       1.0.0
	 */
	class Discord_Display {


		/**
		 * @var         Discord_Display $instance The one true Discord_Display
		 * @since       1.0.0
		 */
		private static $instance;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      self::$instance The one true Discord_Display
		 */
		public static function instance() {
			if( ! self::$instance ) {
				self::$instance = new Discord_Display();
				self::$instance->setup_constants();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->hooks();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function setup_constants() {
			// Plugin version
			define( 'DISCORD_DISPLAY_VER', '1.0.0' );

			// Plugin path
			define( 'DISCORD_DISPLAY_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'DISCORD_DISPLAY_URL', plugin_dir_url( __FILE__ ) );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @global		array $discord_display_options The Discord_Display options array
		 * @return      void
		 */
		private function includes() {
			require_once DISCORD_DISPLAY_DIR . 'includes/functions.php';
			require_once DISCORD_DISPLAY_DIR . 'includes/scripts.php';
			require_once DISCORD_DISPLAY_DIR . 'includes/widgets.php';
			require_once DISCORD_DISPLAY_DIR . 'includes/libraries/class.discord.php';
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function hooks() {

		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'discord_display_language_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), '' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'discord-display', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/discord-display/' . $mofile;

			if( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/discord-display/ folder
				load_textdomain( 'discord-display', $mofile_global );
			} elseif( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/discord-display/languages/ folder
				load_textdomain( 'discord-display', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'discord-display', false, $lang_dir );
			}
		}
	}
}


/**
 * The main function responsible for returning the one true Discord_Display
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      Discord_Display The one true Discord_Display
 */
function discord_display() {
	return Discord_Display::instance();
}
add_action( 'plugins_loaded', 'discord_display' );