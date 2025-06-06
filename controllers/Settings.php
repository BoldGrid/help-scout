<?php


/**
 * Help Scout API Controller
 *
 * @package HelpScout_Desk
 * @subpackage HSD Admin Settings
 */
class HSD_Settings extends HSD_Controller {
	const API_KEY = 'hs_api_key';
	const MAILBOX = 'hs_mailbox';
	const RESET_CUSTOMER_IDS_QV = 'hsd_reset_customer_ids';
	const OAUTHTOKEN = 'hs_oauthtoken';
	const APP_ID = 'hs_oauthtoken_app_id';
	const SECRET = 'hs_oauthtoken_secret';
	const HSD_NONCE = 'hsd_nonce';
	protected static $api_key;
	protected static $mailbox;
	protected static $oauth_token;
	protected static $app_id;
	protected static $secret;

	public static function init() {
		// Store options
		self::$api_key = get_option( self::API_KEY, '' );
		self::$mailbox = get_option( self::MAILBOX, '' );
		self::$oauth_token = get_option( self::OAUTHTOKEN, '' );
		self::$app_id = get_option( self::APP_ID, '' );
		self::$secret = get_option( self::SECRET, '' );

		// Register Settings
		self::register_settings();

	}

	public static function get_api_key() {
		return self::$api_key;
	}

	public static function get_mailbox() {
		return self::sanitize_mailbox_id( self::$mailbox );
	}

	public static function get_oauth_token() {
		if ( '' === self::$oauth_token && '' !== self::$api_key ) {

		}
		return self::$oauth_token;
	}

	public static function get_app_id() {
		return self::$app_id;
	}

	public static function get_secret() {
		return self::$secret;
	}


	//////////////
	// Settings //
	//////////////

	/**
	 * Hooked on init add the settings page and options.
	 *
	 */
	public static function register_settings() {
		// Option page
		$args = array(
			'slug' => self::SETTINGS_PAGE,
			'title' => 'Help Scout Desk Settings',
			'menu_title' => 'Help Scout Desk',
			'tab_title' => 'Getting Started',
			'weight' => 20,
			'reset' => false,
			'section' => self::SETTINGS_PAGE,
			);

		if ( HSD_FREE ) {
			$args['title'] = 'Help Scout Settings';
			$args['menu_title'] = 'Help Scout Plugin';
		}

		do_action( 'sprout_settings_page', $args );

		// Settings
		$settings = array(
			'hsd_site_settings' => array(
				'title' => 'Help Scout Setup',
				'weight' => 10,
				'callback' => array( __CLASS__, 'display_general_section' ),
				'settings' => array(
					self::APP_ID => array(
						'label' => __( 'Application ID', 'help-scout' ),
						'option' => array(
							'description' => sprintf(
								// translators: 1: the redirect url.
								__( 'You need to create an OAuth2 application before you can proceed. Create one by navigating to Your Profile > My apps and click Create My App. When creating your app use <code>%1$s</code> as the redirect url.', 'help-scout' ),
								HelpScout_API::get_redirect_url(),
							),
							'type' => 'text',
							'default' => self::$app_id,
						),
					),
					self::SECRET => array(
						'label' => __( 'App Secret', 'help-scout' ),
						'option' => array(
							'description' => sprintf( __( 'The app secret when creating a new OAuth2 application.', 'help-scout' ), HelpScout_API::get_redirect_url() ),
							'type' => 'text',
							'default' => self::$secret,
						),
					),
					self::MAILBOX => array(
						'label' => __( 'Mailbox ID', 'help-scout' ),
						'option' => array(
							'description' => __( 'When opening a mailbox within Help Scout, open the mailbox and click Settings in the bottom left corner of the mailbox filters list and click in Edit Mailbox. In the URL of the resulting settings screen, is your mailbox ID. Example, https://secure.helpscout.net/settings/mailbox/<b>123456</b>/', 'help-scout' ),
							'type' => 'text',
							'default' => self::sanitize_mailbox_id( self::$mailbox ),
						),
						'sanitize_callback' => array( __CLASS__, 'sanitize_mailbox_id' ),
					),
					self::RESET_CUSTOMER_IDS_QV => array(
						'label' => __( 'Advanced: Reset', 'help-scout' ),
						'option' => array(
							'description' => __( 'To be used if you\'ve recently migrated and have the API error "input could not be validate". Note: confirm the mailbox, APP ID, and Secret before using this option.', 'help-scout' ),
							'type' => 'reset_ids',
						),
						'sanitize_callback' => array( __CLASS__, 'sanitize_mailbox_id' ),
					),
				),
			),
			'hsd_options' => array(
				'title' => 'Options / Settings',
				'weight' => 20,
				'callback' => array( __CLASS__, 'section_desc' ),
				'settings' => array(),
			),
		);
		do_action( 'sprout_settings', $settings, self::SETTINGS_PAGE );
	}

	//////////////////////
	// General Settings //
	//////////////////////

	public static function display_general_section() {
		printf(
			// Translators: 1: opening paragraph tag 2: opening anchor tag 3: Support URL 4: closing open a tag 5: closing anchor tag and p tag.
			esc_html__( '%1$sEnter Help Scout API Information below. For details on how to find this information and setting your pages/shortcodes please review the %2$s%3$s%4$sdocumention%5$s', 'help-scout' ),
			'<p>',
			'<a href="',
			esc_attr( SUPPORT_URL ),
			'">',
			'</a>.</p>',
		);
	}

	public static function section_desc() {
		esc_html_e( 'Make sure to setup your Help Scout API key and Mailbox ID before proceeding to these options / settings.', 'help-scout' );
	}

	public static function auth_button() {
		ob_start();
		if ( '' == self::$app_id ) {
			printf( '<a href="javascript::void(0)" class="button" disabled="disabled">%1$s</a>', esc_html__( 'Authorize', 'help-scout' ) );
		} else {
			printf( '<a href="https://secure.helpscout.net/authentication/authorizeClientApplication?client_id=%1$s" class="button">%2$s</a>', esc_attr( self::$app_id ), esc_html__( 'Authorize', 'help-scout' ) );
		}
		return ob_get_clean();
	}

	public static function reset_customer_ids() {
		ob_start();
		?>
			<span class="button" id="reset_customer_ids" data-nonce="<?php wp_create_nonce( self::HSD_NONCE ); ?>"><?php esc_html_e( 'Reset Customer IDS', 'help-scout' ); ?></span>
			<script type="text/javascript">
				//<![CDATA[
				jQuery("#reset_customer_ids").on('click', function(event) {
					event.stopPropagation();
					event.preventDefault();
					var $button = jQuery( this );

					$button.after('<span class="spinner si_inline_spinner" style="visibility:visible;display:inline-block;"></span>');

					if( confirm( '<?php esc_html_e( 'Are you sure? This will delete stored customer ids for your users.', 'help-scout' ); ?>' ) ) {
						jQuery.post( ajaxurl, { action: 'hsd_reset_customer_ids', nonce: $button.data('nonce') },
							function( data ) {
								jQuery('.si_inline_spinner').remove();
								jQuery("#reset_customer_ids").removeClass('button');
								jQuery("#reset_customer_ids").html('<?php esc_html_e( 'All done', 'help-scout' ); ?>');
							}
						);
					}
				});
				//]]>
			</script>
		<?php
		return ob_get_clean();
	}

	///////////////
	// Sanitize //
	///////////////

	public static function sanitize_mailbox_id( $option = '' ) {
		// strip everything but the numbers incase they copy the entire url as the option.
		return preg_replace( '/[^0-9],/', '', $option );
	}
}
