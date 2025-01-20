<?php

if ( class_exists( 'SA_Settings_API' ) ) {
	// Another Sprout App is active
	return;
}


/**
 * Admin settings pages and meta controller.
 *
 * Add APIs for easily adding admin menus and meta boxes.
 *
 * @package Sprout_Invoice
 * @subpackage Settings
 */
class SA_Settings_API extends HSD_Controller {

	private static $admin_pages = array();
	private static $options = array();
	private static $option_tabs = array();
	protected static $settings_page;
	// meta boxes
	private static $meta_boxes = array();

	public static function get_admin_pages() {
		return self::$admin_pages;
	}

	public static function get_settings_page() {
		return self::$settings_page;
	}

	public static function get_option_tabs() {
		return self::$option_tabs;
	}

	public static function get_setting_options() {
		return self::$options;
	}

	public static function init() {

		/////////////////////////
		// Admin Setting Pages //
		/////////////////////////

		// Register Admin Pages
		add_action( 'sprout_settings_page', array( __CLASS__, 'register_page' ) );

		// Register Settings for pages
		add_action( 'sprout_settings', array( __CLASS__, 'register_settings' ), 10, 2 );

		// Build Menus
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_page' ), 10, 0 );

		// Build Menus
		add_action( 'admin_init', array( __CLASS__, 'add_options' ), 5, 0 );

		// AJAX Actions
		add_action( 'wp_ajax_si_save_options', array( __CLASS__, 'maybe_save_options_via_ajax' ) );

		//////////////////
		// Meta Box API //
		//////////////////

		// Register meta box
		add_action( 'sprout_meta_box', array( __CLASS__, 'register_meta_box' ), 10, 2 );

		// add meta boxes
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );

		// save meta boxes
		add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ), 10, 2 );

		// add submission query vars
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );

	}

	/**
	 * Add query vars for settings page
	 *
	 * @param  array $vars query vars.
	 *
	 * @return array $vars modified query vars.
	 */
	public static function add_query_vars( $vars ) {
		$vars[] = 'page';

		return $vars;
	}

	public static function manage_capability() {
		return apply_filters( 'manage_help_scout_options', 'manage_options' );
	}

	/**
	 * Register a settings sub-page in the plugin's menu
	 * @param  array $args
	 * @return string
	 */
	public static function register_page( $args ) {

		$defaults = array(
			'parent' => '',
			'slug' => 'undefined_slug',
			'title' => 'Undefined Title',
			'menu_title' => 'Undefined Menu Title',
			'tab_title' => false,
			'weight' => 10,
			'reset' => false,
			'section' => 'theme',
			'show_tabs' => true,
			'tab_only' => false,
			'callback' => null,
			'ajax' => false,
			'ajax_full_page' => false,
			'add_new' => '',
			'add_new_post_type' => '',
			'capability' => self::manage_capability(),
		);
		$parsed_args = wp_parse_args( $args, $defaults );
		extract( $parsed_args );

		$page = self::APP_DOMAIN.'/'.$slug;
		self::$option_tabs[ $slug ] = array(
			'slug' => $slug,
			'title' => $menu_title,
			'tab_title' => ( $tab_title ) ? $tab_title : $menu_title,
			'weight' => $weight,
			'section' => $section,
			'add_new_post_type' => $add_new_post_type,
			'add_new' => $add_new,
			'section' => $section,
			'callback' => $callback,
			'tab_only' => $tab_only,
			'capability' => $capability,
		);
		if ( ! $tab_only ) {
			self::$admin_pages[ $page ] = array(
				'parent' => $parent,
				'title' => $title,
				'menu_title' => $menu_title,
				'weight' => $weight,
				'ajax' => $ajax,
				'ajax_full_page' => $ajax_full_page,
				'reset' => $reset,
				'tab_only' => $tab_only,
				'section' => $section,
				'callback' => $callback,
				'capability' => $capability,
			);
		}
		return $page;
	}

	/**
	 * Register settings from action
	 * @param  array  $settings
	 * @param  string $page
	 * @return
	 */
	public static function register_settings( $settings = array(), $page = '' ) {
		if ( $page == '' ) {
			$page = self::SETTINGS_PAGE;
		}
		if ( ! isset( self::$options[ $page ] ) ) {
			self::$options[ $page ] = array();
		}
		self::$options[ $page ] = wp_parse_args( self::$options[ $page ], $settings );
	}

	/**
	 * Creates the main admin page, and any registered sub-pages
	 *
	 * @static
	 * @return void
	 */
	public static function add_admin_page() {

		// Add parent menu for SI
		self::$settings_page = add_submenu_page( 'options-general.php', __( 'Help Scout', 'help-scout' ), __( 'Help Scout', 'help-scout' ), self::manage_capability(), self::APP_DOMAIN, array( __CLASS__, 'default_admin_page' ) );

	}

	public static function dashboard_page() {
		self::load_view( 'admin/sprout-apps-dashboard.php', array() );
	}

	/**
	 * Get the current settings page slug.
	 *
	 * Example: settings_page_help-scout-desk to help-scout-desk
	 *
	 * @param  string $page the page id.
	 *
	 * @return string $result the page slug.
	 */
	public static function get_current_settings_page( $page = '' ) {

		// Match everything after the last "_"
		if ( preg_match( '/[^_]+$/', $page, $matches ) ) {
			$result = $matches[0];
			return $result;
		}
	}

	/**
	 * Displays an admin/settings page
	 *
	 * @static
	 * @return void
	 */
	public static function default_admin_page() {

		if ( ! current_user_can( self::manage_capability() ) ) {
			return; // not allowed to view this page
		}

		if ( isset( $_GET['settings-updated'] ) && isset( $_GET['settings-updated'] ) ) {
			// Update rewrite rules when options are updated.
			flush_rewrite_rules();
		}

		$plugin_page    = self::get_current_settings_page( get_current_screen()->id );
		$title          = ( isset( self::$admin_pages[ $plugin_page ]['title'] ) ) ? self::$admin_pages[ $plugin_page ]['title'] : '';
		$ajax           = isset( self::$admin_pages[ $plugin_page ]['ajax'] ) ? self::$admin_pages[ $plugin_page ]['ajax'] : '';
		$ajax_full_page = isset( self::$admin_pages[ $plugin_page ]['ajax_full_page'] ) ? self::$admin_pages[ $plugin_page ]['ajax_full_page'] : '';
		$reset          = isset( self::$admin_pages[ $plugin_page ]['reset'] ) ? self::$admin_pages[ $plugin_page ]['reset'] : '';
		$section        = isset( self::$admin_pages[ $plugin_page ]['section'] ) ? self::$admin_pages[ $plugin_page ]['section'] : '';

		self::load_view(
			'admin/settings',
			array(
				'title'          => $title,
				'page'           => $plugin_page,
				'ajax'           => $ajax,
				'ajax_full_page' => $ajax_full_page,
				'reset'          => $reset,
				'section'        => $section,
			),
			false
		);
	}

	/**
	 * Add all options via WP functions on admin_init
	 */
	public static function add_options() {
		$options = apply_filters( 'si_add_options', self::$options );

		foreach ( $options as $page => $sections ) {
			// Build Section
			uasort( $sections, array( __CLASS__, 'sort_by_weight' ) );
			foreach ( $sections as $section_id => $section_args ) {
				// Check to see if we're on a tab and try to figure out what settings to register
				$tab = ( isset( $section_args['tab'] ) ) ? $section_args['tab'] : $page;
				$tpage = self::APP_DOMAIN;
				$display = ( isset( $section_args['callback'] ) && is_callable( $section_args['callback'] ) ) ? $section_args['callback'] : array( __CLASS__, 'display_settings_section' ) ;
				$title = ( isset( $section_args['title'] ) ) ? $section_args['title'] : '' ;
				add_settings_section( $section_id, $title, $display, $tpage );

				// Build settings
				foreach ( $section_args['settings'] as $setting => $setting_args ) {
					// register setting
					$sanitize_callback = ( isset( $setting_args['sanitize_callback'] ) && is_callable( $setting_args['sanitize_callback'] ) ) ? $setting_args['sanitize_callback'] : '' ;
					register_setting( $tpage, $setting, $sanitize_callback );
					// register display callback
					$title = ( isset( $setting_args['label'] ) ) ? $setting_args['label'] : '' ;
					$callback = ( is_callable( $setting_args['option'] ) ) ? $setting_args['option'] : array( __CLASS__, 'option_field' );
					$setting_args['name'] = $setting;
					add_settings_field( $setting, $title, $callback, $tpage, $section_id, $setting_args );
				}
			}
		}
	}

	/**
	 * For most settings sections, there's nothing special to display.
	 * This function will display just that. Use it as a callback for
	 * add_settings_section().
	 *
	 * @return void
	 */
	public static function display_settings_section() {}

	/**
	 * Full option field.
	 * @param  array $args
	 * @return
	 */
	public static function option_field( $args ) {
		$name = $args['name'];
		$out = '';
		if ( $args['option']['type'] != 'checkbox' ) {
			$out .= self::setting_form_label( $name, $args['option'] );
			$out .= self::setting_form_field( $name, $args['option'] );
		} else {
			$label = ( isset( $args['option']['label'] ) ) ? $args['option']['label'] : '' ;
			$out .= '<label for="' . $name . '">' . self::setting_form_field( $name, $args['option'] ) . ' ' . $label . '</label>';
			if ( ! empty( $args['option']['description'] ) ) {
				$out .= '<p class="description help_block">' . $args['option']['description'] . '</p>';
			}
		}
		print apply_filters( 'sprout_settings_option_field', $out, $name, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Setting field label
	 * @param  string $name
	 * @param  array $data
	 * @return
	 */
	public static function setting_form_label( $name, $data ) {
		$out = '';
		if ( isset( $data['label'] ) ) {
			$out = '<label for="'.$name.'">'.$data['label'].'</label>';
		}
		return apply_filters( 'si_admin_settings_form_label', $out, $name, $data );
	}

	/**
	 * Settings form field
	 * @param  string $name
	 * @param  array $data
	 * @return
	 */
	public static function setting_form_field( $name, $data ) {
		if ( ! isset( $data['attributes'] ) || ! is_array( $data['attributes'] ) ) {
			$data['attributes'] = array();
		}
		if ( ! isset( $data['default'] ) ) {
			$data['default'] = '';
		}
		ob_start(); ?>

		<?php if ( $data['type'] == 'textarea' ) : ?>
			<textarea type="textarea" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" rows="<?php echo isset( $data['rows'] ) ? esc_attr( $data['rows'] ): 4; ?>" cols="<?php echo isset( $data['cols'] )? esc_attr( $data['cols'] ): 40; ?>" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo esc_attr( $attr ).'="'.esc_attr( $attr_value ).'" '; } ?> <?php if ( isset( $data['required'] ) && $data['required'] ) { echo 'required'; } ?>><?php echo esc_textarea( $data['default'] ); ?></textarea>
		<?php elseif ( $data['type'] == 'wysiwyg' ) : ?>
			<?php
				wp_editor_styleless( $data['default'], $name, array( 'textarea_rows' => 10 ) ); ?>
		<?php elseif ( $data['type'] == 'select-state' ) :  // FUTURE AJAX based on country selection  ?>
			<select type="select" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" class="regular-text" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo esc_attr( $attr ).'="'.esc_attr( $attr_value ).'" '; } ?> <?php if ( isset( $data['required'] ) && $data['required'] ) { echo 'required'; } ?>>
				<?php foreach ( $data['options'] as $group => $states ) : ?>
					<optgroup label="<?php echo esc_attr( $group ); ?>">
						<?php foreach ( $states as $option_key => $option_label ) : ?>
							<option value="<?php echo esc_attr( $option_key ) ?>" <?php selected( $option_key, $data['default'] ) ?>><?php echo esc_html( $option_label ); ?></option>
						<?php endforeach; ?>
					</optgroup>
				<?php endforeach; ?>
			</select>
		<?php elseif ( $data['type'] == 'select' ) : ?>
			<select type="select" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo esc_attr( $attr ).'="'.esc_attr( $attr_value ).'" '; } ?> <?php if ( isset( $data['required'] ) && $data['required'] ) { echo 'required'; } ?>>
				<?php foreach ( $data['options'] as $option_key => $option_label ) : ?>
				<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( $option_key, $data['default'] ) ?>><?php echo esc_html( $option_label ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php elseif ( $data['type'] == 'multiselect' ) : ?>
			<select type="select" name="<?php echo esc_attr( $name ); ?>[]" id="<?php echo esc_attr( $name ); ?>" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo esc_attr( $attr ).'="'.esc_attr( $attr_value ).'" '; } ?> multiple="multiple" <?php if ( isset( $data['required'] ) && $data['required'] ) { echo 'required'; } ?>>
				<?php foreach ( $data['options'] as $option_key => $option_label ) : ?>
					<option value="<?php echo esc_attr( $option_key ); ?>" <?php if ( in_array( $option_key, $data['default'] ) ) { echo 'selected="selected"'; } ?>><?php echo esc_html( $option_label ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php elseif ( $data['type'] == 'radios' ) : ?>
			<?php foreach ( $data['options'] as $option_key => $option_label ) : ?>
				<label for="<?php echo esc_attr( $name ); ?>_<?php echo esc_attr( $option_key ); ?>"><input type="radio" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>_<?php echo esc_attr( $option_key ); ?>" value="<?php echo esc_attr( $option_key ); ?>" <?php checked( $option_key, $data['default'] ) ?> />&nbsp;<?php echo wp_kses_post( $option_label ); ?></label>
				<br />
			<?php endforeach; ?>
		<?php elseif ( $data['type'] == 'checkbox' ) : ?>
			<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" <?php checked( $data['value'], $data['default'] ); ?> value="<?php echo isset( $data['value'] )? esc_attr( $data['value'] ):'On'; ?>" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo esc_attr( $attr ).'="'.esc_attr( $attr_value ).'" '; } ?> <?php if ( isset( $data['required'] ) && $data['required'] ) { echo 'required'; } ?>/>
		<?php elseif ( $data['type'] == 'hidden' ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $data['value'] ); ?>" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo esc_attr( $attr ).'="'.esc_attr( $attr_value ).'" '; } ?> />
		<?php elseif ( $data['type'] == 'file' ) : ?>
			<input type="file" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" <?php if ( isset( $data['required'] ) && $data['required'] ) { echo 'required'; } ?>/>
		<?php elseif ( 'reset_ids' === $data['type'] ) : ?>
			<span class="button" id="reset_customer_ids" data-nonce="<?php wp_create_nonce( HSD_Settings::HSD_NONCE ); ?>"><?php esc_html_e( 'Reset Customer IDS', 'help-scout' ); ?></span>
		<?php else : ?>
			<input type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $data['default'] ); ?>" placeholder="<?php echo isset( $data['placeholder'] )? esc_html( $data['placeholder'] ):''; ?>" size="<?php echo isset( $data['size'] )? esc_html( $data['size'] ):40; ?>" <?php foreach ( $data['attributes'] as $attr => $attr_value ) { echo esc_attr( $attr ).'="'.esc_attr( $attr_value ).'" '; } ?> <?php if ( isset( $data['required'] ) && $data['required'] ) { echo 'required'; } ?> class="text-input" />
		<?php endif; ?>
		<?php if ( $data['type'] != 'checkbox' && ! empty( $data['description'] ) ) : ?>
			<p class="description help_block"><?php echo wp_kses_post( $data['description'] ); ?></p>
		<?php endif; ?>
		<?php
		return apply_filters( 'si_admin_settings_form_field', ob_get_clean(), $name, $data );
	}

	/**
	 * Registered meta boxes for all post types, including the si_deal post type.
	 *
	 * @param  array  $registered_boxes array of registered metaboxes
	 * @param  string/array $post_types             post type(s)
	 * @return null 		                  modifies class variable for all pt metaboxes
	 */
	public static function register_meta_box( $registered_boxes = array(), $post_types = array() ) {
		if ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types ); // convert a string into an array.
		}
		foreach ( $post_types as $post_type ) {
			$defaults = array(
					'title' => 'Settings',
					'callback' => array( __CLASS__, 'show_meta_box' ),
					'screen' => $post_type,
					'context' => 'normal',
					'priority' => 'high',
					'callback_args' => array(),
					'weight' => 10,
					'save_priority' => 10,
				);

			if ( ! isset( self::$meta_boxes[ $post_type ] ) ) {
				self::$meta_boxes[ $post_type ] = array();
			}
			foreach ( $registered_boxes as $box_name => $args ) {
				$registered_boxes[ $box_name ] = wp_parse_args( $args, $defaults );
			}
			self::$meta_boxes[ $post_type ] = wp_parse_args( self::$meta_boxes[ $post_type ], $registered_boxes );
		}
	}

	/**
	 * loop through registered meta boxes and use the add_meta_box WP function.
	 *
	 */
	public static function add_meta_boxes() {
		// Loop through all registered meta boxes
		foreach ( self::$meta_boxes as $post_type => $meta_boxes ) {
			// Sort boxes based on weight before priority
			uasort( $meta_boxes, array( __CLASS__, 'sort_by_weight' ) );
			// Loop through each meta box registered under this type.
			foreach ( $meta_boxes as $metabox_name => $args ) {
				$args = apply_filters( $metabox_name . '_meta_box_args', $args );
				extract( $args );
				add_meta_box( $metabox_name, $title, $callback, $screen, $context, $priority, $args );
			}
		}
	}

	/**
	 * Show the meta box using the registered callback.
	 *
	 * @param  object $post
	 * @param  array $meta_box
	 */
	public static function show_meta_box( $post, $meta_box ) {
		if ( $is_callable = is_callable( $meta_box['args']['show_callback'] ) ) {
			do_action( implode( '::', $meta_box['args']['show_callback'] ), $post, $meta_box );
			call_user_func_array( $meta_box['args']['show_callback'], array( $post, $meta_box ) );
			do_action( implode( '::', $meta_box['args']['show_callback'] ), $post, $meta_box );
		} else {
			if ( method_exists( $meta_box['args']['show_callback'][0], $meta_box['args']['show_callback'][1] ) ) {
				do_action( 'si_error', __CLASS__ . '::' . __FUNCTION__ . ' - callback may be private.', $meta_box );
			}
		}
	}

	/**
	 * Attempt to save all registered meta boxes.
	 *
	 * @param  int $post_id
	 * @param  object $post
	 * @return
	 */
	public static function save_meta_boxes( $post_id, $post ) {

		$nonce = ( isset( $_POST['si_meta_box_nonce'] ) ) ? sanitize_text_field( wp_unslash( $_POST['si_meta_box_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, HSD_Settings::HSD_NONCE ) ) {
			wp_die( 'Helpscout Security check failed' );
		}

		// Don't save meta boxes when the importer is used.
		if ( isset( $_GET['import'] ) && $_GET['import'] == 'wordpress' ) {
			return;
		}
		if ( isset( $_POST['option_page'] ) ) {
			return;
		}

		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}

		foreach ( self::$meta_boxes as $post_type => $post_meta_boxes ) {
			// Only save the meta boxes that count
			if ( $post->post_type == $post_type ) {
				// Sort by saved weight
				uasort( $post_meta_boxes, array( __CLASS__, 'sort_by_save_weight' ) );
				// Loop through each meta box registered under this type.
				foreach ( $post_meta_boxes as $box_name => $args ) {
					if ( isset( $args['save_callback'] ) && is_array( $args['save_callback'] ) ) {
						if ( is_callable( $args['save_callback'] ) ) {
							$callback_args = ( ! isset( $args['save_callback_args'] ) ) ? array() : $args['save_callback_args'] ;

							$action_name = implode( '::', $args['save_callback'] );
							if ( did_action( $action_name ) > 1 ) {
								return;
							}
							// execute
							call_user_func_array( $args['save_callback'], array( $post_id, $post, $callback_args ) );
							// action
							do_action( $action_name, $post_id, $post, $callback_args );
						} elseif ( method_exists( $args['save_callback'][0], $args['save_callback'][1] ) ) {
							do_action( 'si_error', __CLASS__ . '::' . __FUNCTION__ . ' - callback may be private.', $args );
						}
					}
				}
			}
		}
	}

	/**
	 * Comparison function
	 */
	public static function sort_by_save_weight( $a, $b ) {
		if ( ! isset( $a['save_priority'] ) || ! isset( $b['save_priority'] ) ) {
			return 0; }

		if ( $a['save_priority'] == $b['save_priority'] ) {
			return 0;
		}
		return ( $a['save_priority'] < $b['save_priority'] ) ? -1 : 1;
	}
}
