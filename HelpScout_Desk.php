<?php


/**
 * A fundamental class from which all other classes in the plugin should be derived.
 * The purpose of this class is to hold data useful to all classes.
 * @package SI
 */

if ( ! defined( 'HSD_FREE' ) ) {
	define( 'HSD_FREE', false ); }

if ( ! defined( 'HSD_DEV' ) ) {
	define( 'HSD_DEV', false ); }

if ( ! defined( 'SUPPORT_URL' ) ) {
	define( 'SUPPORT_URL', 'https://wphelpscout.com/support/docs/getting-started-with-help-scout-desk/' );
}

abstract class HelpScout_Desk {
	/**
	 * Application app-domain
	 */
	const APP_DOMAIN = 'help-scout-desk';

	/**
	 * Application text-domain
	 */
	const TEXT_DOMAIN = 'help-scout-desk';
	/**
	 * Application text-domain
	 */
	const PLUGIN_URL = 'https://wphelpscout.com';
	/**
	 * Current version. Should match help-scout-desk.php plugin version.
	 */
	const HSD_VERSION = '6.5.7';
	/**
	 * DB Version
	 */
	const DB_VERSION = 1;
	/**
	 * Application Name
	 */
	const PLUGIN_NAME = 'Help Scout Desk';
	const PLUGIN_FILE = HSD_PLUGIN_FILE;
	/**
	 * HSD_DEV constant within the wp-config to turn on SI debugging
	 * <code>
	 * define( 'HSD_DEV', TRUE/FALSE )
	 * </code>
	 */
	const DEBUG = HSD_DEV;
}
