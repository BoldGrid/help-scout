<?php


/**
 * Help Scout API Controller
 *
 * @package Help_Scout_Desk
 * @subpackage Help
 */
class HSD_Beacon extends HSD_Controller {
	const BEACON_OPTION = 'help_scout_beacon';
	const BEACON_SEC_OPTION = 'help_scout_beacon_sec_key';
	protected static $beacon_embed;
	protected static $beacon_key;

	public static function init() {
		self::$beacon_embed = get_option( self::BEACON_OPTION, false );
		self::$beacon_key = get_option( self::BEACON_SEC_OPTION, false );

		// Register Settings
		self::register_settings();

		// front-end view
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'wp_footer', array( __CLASS__, 'add_beacon' ) );
	}

	/**
	 * Enqueue scripts
	 */
	public static function enqueue_scripts() {
		wp_enqueue_script( 'hsd-beacon', HSD_URL . '/resources/front-end/js/hsd-beacon.js', array(), self::HSD_VERSION, false );
		$pattern = '/window\.Beacon\(\s*[\'"]init[\'"]\s*,\s*[\'"]([0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12})[\'"]\s*\)/i';
		
		if ( self::$beacon_embed && preg_match( $pattern, self::$beacon_embed, $matches ) ) {
			$code = "window.Beacon('init', '" . $matches[1] . "');";
			wp_add_inline_script( 'hsd-beacon', $code );
		}
	}

	private static function is_beacon_2() {

		$bool = false;
		if ( strpos( self::$beacon_embed, 'beacon-v2' ) !== false ) {
			$bool = true;
		}

		return $bool;
	}

	public static function add_beacon() {
		if ( ! self::$beacon_embed ) {
			return;
		}
		if ( is_user_logged_in() ) {
			$user_data = get_userdata( get_current_user_id() );
			$uname = $user_data->user_firstname . ' ' . $user_data->user_lastname;
			$name = ( strlen( $uname ) > 1 ) ? $uname : '' ;
			$email = $user_data->user_email;

			$signature = hash_hmac(
				'sha256',
				$email,
				self::$beacon_key
			);
			?>

				<script type="text/javascript">
					<?php if ( self::is_beacon_2() ) : ?>

						<?php if ( self::$beacon_key ) : ?>
							window.Beacon("identify", {
								name: "<?php echo esc_js( $name ); ?>",
								email: "<?php echo esc_js( $email ) ?>",
								signature: "<?php echo esc_js( $signature ) ?>"
							});
						<?php else : ?>
							window.Beacon("identify", {
								name: "<?php echo esc_js( $name ); ?>",
								email: "<?php echo esc_js( $email ) ?>"
							});

						<?php endif ?>
					<?php else : ?>
						HS.beacon.ready(function() {
							HS.beacon.identify({
								name: '<?php echo esc_js( $name ); ?>',
								email: '<?php echo esc_js( $email ) ?>',
							});
						});
					<?php endif ?>
				</script>
			<?php
		} else {
			?>
				<script type="text/javascript">
					<?php if ( self::is_beacon_2() ) : ?>
						// nothing yet
					<?php else : ?>
						HS.beacon.ready();
					<?php endif ?>
				</script>
			<?php
		}
	}

	//////////////
	// Settings //
	//////////////

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {

		// Settings
		$settings = array(
			'hsd_beacon_options' => array(
				'weight' => 30,
				'settings' => array(
					self::BEACON_OPTION => array(
						'label' => __( 'Beacon', 'help-scout' ),
						'option' => array(
							'description' => sprintf(
								// translators: !: link to the Help Scout documentation.
								__( 'Copy and paste the beacon embed code. For more information about this please read the <a href="%1$s">Help Scout documentation</a>.', 'help-scout' ),
								'http://developer.helpscout.net/beacons/'
							),
							'type' => 'textarea',
							'default' => get_option( self::BEACON_OPTION, '' ),
						),
					),
					self::BEACON_SEC_OPTION => array(
						'label' => __( 'Support history security key', 'help-scout' ),
						'option' => array(
							'description' => sprintf( __( 'Click "advanced" for the "Support history security" option when setting up your Beacon.', 'help-scout' ) ),
							'type' => 'input',
							'default' => get_option( self::BEACON_SEC_OPTION, '' ),
						),
					),
				),
			),
		);
		do_action( 'sprout_settings', $settings, self::SETTINGS_PAGE );
	}
}
