<?php
/**
 * Widgets
 *
 * @package     Discord_Display\Widgets
 * @since       1.0.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Discord widget
 *
 * @since       1.0.0
 * @return      void
*/
class discord_display_widget extends WP_Widget {


	public $api;


	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function __construct() {
		parent::__construct( 'discord_display_widget', __( 'Discord Server', 'discord-display' ), array( 'description' => __( 'Display the Discord server status widget', 'discord-display' ) ) );
	}


	/**
	 * Output the widget
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $args Arguments passed to the widget
	 * @param       array $instance The widget instance
	 * @return      void
	 */
	public function widget( $args, $instance ) {
		// Bail if no server ID set
		if( ! $instance['server_id'] || $instance['server_id'] == '' ) {
			return;
		}

		$this->api = new Discord_API( $instance['server_id'] );

		// Bail if API couldn't be setup
		if( ! $this->api ) {
			return;
		}

		$json_data = $this->api->fetch();

		// Bail if the ID is invalid
		if( isset( $json_data->message ) ) {
			return;
		}

		$instance['title']          = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		$instance['theme']          = ( isset( $instance['theme'] ) ) ? $instance['theme'] : 'default';
		$instance['online_label']   = ( isset( $instance['online_label'] ) ) ? $instance['online_label'] : __( 'Users Online', 'discord-display' );
		$instance['connect_button'] = ( isset( $instance['connect_button'] ) ) ? $instance['connect_button'] : 'text';

		// Sort channels
		usort( $json_data->channels, discord_display_channel_sort( 'position' ) );

		// Handle the %servername% template tag
		if( stristr( $instance['title'], '%servername%' ) ) {
			$instance['title'] = str_replace( '%servername%', $json_data->name, $instance['title'] );
		}

		$title           = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );
		$display_avatars = isset( $instance['display_avatars'] ) && $instance['display_avatars'] == 'on' ? 1 : 0;
		$display_status  = isset( $instance['display_status'] ) && $instance['display_status'] == 'on' ? 1 : 0;
		$display_online  = isset( $instance['display_online'] ) && $instance['display_online'] == 'on' ? 1 : 0;

		$online_users    = array();

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		do_action( 'discord_display_widget' );

		$widget  = '<div class="discord-display-widget discord-display-theme-' . $instance['theme'] . '">';

		foreach( $json_data->channels as $channel ) {
			$widget .= '<div class="discord-channel">' . $channel->name . '</div>';

			foreach( $json_data->members as $member ) {
				if( $display_online ) {
					if( isset( $member->status ) && $member->status == 'online' && ! array_key_exists( $member->id, $online_users ) ){
						$online_users[$member->id] = $member->username;
					}
				}

				if( isset( $member->channel_id ) && $member->channel_id == $channel->id ) {
					$widget .= '<div class="discord-member">';

					if( $display_avatars ) {
						$widget .= '<div class="discord-member-avatar"><img src="' . $member->avatar_url . '" /></div>';
					}

					$widget .= '<div class="discord-member-name">';
					$widget .= $member->username;
					$widget .= '</div>';

					if( $display_status ) {
						$widget .= '<div class="discord-member-status">';

						if( ( isset( $member->deaf ) && $member->deaf ) || ( isset( $member->self_mute ) && $member->self_mute ) ) {
							$widget .= '<i class="fa fa-volume-off"></i>';
						}

						if( ( isset( $member->mute ) && $member->mute ) || ( isset( $member->suppress ) && $member->suppress ) ) {
							$widget .= '<i class="fa fa-microphone-slash"></i>';
						}

						$widget .= '</div>';
					}

					$widget .= '</div>';
				}
			}
		}

		if( $display_online || $instance['connect_button'] != 'none' ) {
			$footer_class = 'discord-footer-connect-' . $instance['connect_button'];

			$widget .= '<div class="discord-footer ' . $footer_class . '">';

			if( $display_online ) {
				$widget .= '<div class="discord-online-users">';
				$widget .= apply_filters( 'discord_display_online_users', count( $online_users ) . ' ' . $instance['online_label'], count( $online_users ) );
				$widget .= '</div>';
			}

			if( $instance['connect_button'] != 'none' ) {
				$button_class = ( $instance['connect_button'] == 'button' ? 'button' : '' );

				$widget .= '<div class="discord-connect-button">';
				$widget .= '<a href="' . $json_data->instant_invite . '" class="' . $button_class . '" target="_blank">' . __( 'Connect', 'discord-display' ) . '</a>';
				$widget .= '</div>';
			}

			$widget .= '</div>';
		}

	//	var_dump( $json_data );

		$widget .= '</div>';

		echo $widget;

		do_action( 'discord_display_widget' );

		echo $args['after_widget'];
	}


	/**
	 * Update widget settings
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $new_instance The updated instance
	 * @param       array $old_instance The current instance
	 * @return      array $instance The updated instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']           = strip_tags( $new_instance['title'] );
		$instance['server_id']       = strip_tags( $new_instance['server_id'] );
		$instance['theme']           = $new_instance['theme'];
		$instance['display_avatars'] = isset( $new_instance['display_avatars'] ) ? $new_instance['display_avatars'] : '';
		$instance['display_status']  = isset( $new_instance['display_status'] ) ? $new_instance['display_status'] : '';
		$instance['display_online']  = isset( $new_instance['display_online'] ) ? $new_instance['display_online'] : '';
		$instance['online_label']    = strip_tags( $new_instance['online_label'] );
		$instance['connect_button']  = $new_instance['connect_button'];

		return $instance;
	}


	/**
	 * The widget settings
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       array $instance The widget instance
	 * @return      void
	 */
	public function form( $instance ) {
		$defaults = array(
			'title'           => '',
			'server_id'       => '',
			'theme'           => 'default',
			'display_avatars' => 'on',
			'display_status'  => 'on',
			'display_online'  => 'on',
			'online_label'    => __( 'Users Online', 'discord-display' ),
			'connect_button'  => 'text'
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'discord-display' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
			<span class="description"><?php _e( 'Enter %servername% to display the server name.', 'discord-display' ); ?></span>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'server_id' ) ); ?>"><?php _e( 'Server ID:', 'discord-display' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'server_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'server_id' ) ); ?>" type="text" value="<?php echo $instance['server_id']; ?>" />
			<span class="description"><?php _e( 'Go to Server Settings &rarr; Widget to get your Server ID.', 'discord-display' ); ?></span>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'theme' ) ); ?>"><?php _e( 'Theme:', 'discord-display' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'theme' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'theme' ) ); ?>">
				<?php
				$themes = discord_display_get_themes();

				foreach( $themes as $theme_id => $theme_name ) {
					echo '<option value="' . $theme_id . '" ' . selected( $theme_id, $instance['theme'] ) . '>' . $theme_name . '</option>';
				}
				?>
			</select>
		</p>

		<p>
			<input <?php checked( $instance['display_avatars'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'display_avatars' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_avatars' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_avatars' ) ); ?>"><?php _e( 'Display Avatars', 'discord-display' ); ?></label>
		</p>

		<p>
			<input <?php checked( $instance['display_status'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'display_status' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_status' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_status' ) ); ?>"><?php _e( 'Display Status Icons', 'discord-display' ); ?></label>
		</p>

		<p>
			<input <?php checked( $instance['display_online'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'display_online' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_online' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_online' ) ); ?>"><?php _e( 'Display User Count', 'discord-display' ); ?></label>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'online_label' ) ); ?>"><?php _e( 'Online Users Label:', 'discord-display' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'online_label' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'online_label' ) ); ?>" type="text" value="<?php echo $instance['online_label']; ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'connect_button' ) ); ?>"><?php _e( 'Connect Button:', 'discord-display' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'connect_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'connect_button' ) ); ?>">
				<option value="text"<?php echo selected( 'text', $instance['connect_button'] ); ?>><?php _e( 'Text', 'discord-display' ); ?></option>
				<option value="button"<?php echo selected( 'button', $instance['connect_button'] ); ?>><?php _e( 'Button', 'discord-display' ); ?></option>
				<option value="none"<?php echo selected( 'none', $instance['connect_button'] ); ?>><?php _e( 'None', 'discord-display' ); ?></option>
			</select>
		</p>
		<?php
	}
}


/**
 * Register our widgets
 *
 * @since       1.0.0
 * @return      void
 */
function discord_display_register_widgets() {
	register_widget( 'discord_display_widget' );
}
add_action( 'widgets_init', 'discord_display_register_widgets' );
