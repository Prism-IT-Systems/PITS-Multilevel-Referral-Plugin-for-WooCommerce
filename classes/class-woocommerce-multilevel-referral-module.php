<?php
/**
 * WooCommerce Multilevel Referral Module Abstract Class
 *
 * @package Multilevel_Referral_Plugin_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WooCommerce_Multilevel_Referral_Module' ) ) {
	/**
	 * Abstract class to define/implement base methods for all module classes
	 */
	abstract class WooCommerce_Multilevel_Referral_Module {
		/**
		 * Instances of module classes.
		 *
		 * @var array
		 */
		private static $instances = array();

		/*
		 * Magic methods
		 */
		/**
		 * Public getter for protected variables.
		 *
		 * @mvc Model
		 *
		 * @param string $variable Variable name.
		 *
		 * @return mixed Variable value.
		 * @throws Exception If variable doesn't exist or isn't readable.
		 */
		public function __get( $variable ) {
			$module = get_called_class();
			if ( in_array( $variable, $module::$readable_properties, true ) ) {
				return $this->$variable;
			} else {
				throw new Exception( __METHOD__ . ' error: $' . esc_html( $variable ) . " doesn't exist or isn't readable." );
			}
		}
		/**
		 * Public setter for protected variables.
		 *
		 * @mvc Model
		 *
		 * @param string $variable Variable name.
		 * @param mixed  $value Variable value.
		 *
		 * @throws Exception If variable doesn't exist or isn't writable.
		 */
		public function __set( $variable, $value ) {
			$module = get_called_class();
			if ( in_array( $variable, $module::$writeable_properties, true ) ) {
				$this->$variable = $value;
				if ( ! $this->is_valid() ) {
					throw new Exception( __METHOD__ . ' error: $' . esc_html( $value ) . ' is not valid.' );
				}
			} else {
				throw new Exception( __METHOD__ . ' error: $' . esc_html( $variable ) . " doesn't exist or isn't writable." );
			}
		}

		/*
		 * Non-abstract methods
		 */
		/**
		 * Provides access to a single instance of a module using the singleton pattern
		 *
		 * @mvc Controller
		 *
		 * @return object
		 */
		public static function get_instance() {
			$module = get_called_class();
			if ( ! isset( self::$instances[ $module ] ) ) {
				self::$instances[ $module ] = new $module();
			}
			return self::$instances[ $module ];
		}
		/**
		 * Render a template
		 * Allows parent/child themes to override the markup by placing the a file named basename( $default_template_path ) in their root folder,
		 * and also allows plugins or themes to override the markup by a filter. Themes might prefer that method if they place their templates
		 * in sub-directories to avoid cluttering the root folder. In both cases, the theme/plugin will have access to the variables so they can
		 * fully customize the output.
		 *
		 * @mvc @model
		 *
		 * @param  string $default_template_path The path to the template, relative to the plugin's `views` folder.
		 * @param  array  $variables             An array of variables to pass into the template's scope, indexed with the variable name so that it can be extract()-ed.
		 * @param  string $isrequire               'once' to use require_once() | 'always' to use require().
		 *
		 * @return string Template content.
		 */
		protected static function render_template( $default_template_path = false, $variables = array(), $isrequire = 'once' ) {
			do_action( 'woocommerce_multilevel_referral_render_template_pre', $default_template_path, $variables );
			$template_path = locate_template( basename( $default_template_path ) );
			if ( ! $template_path ) {
				$template_path = dirname( __DIR__ ) . '/views/' . $default_template_path;
			}
			$template_path = apply_filters( 'woocommerce_multilevel_referral_template_path', $template_path );
			if ( is_file( $template_path ) ) {
				// Temporary backward compatibility.
				// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
				extract( $variables );
				ob_start();
				if ( 'always' === $isrequire ) {
					require $template_path;
				} else {
					require_once $template_path;
				}
				$template_content = apply_filters( 'woocommerce_multilevel_referral_template_content', ob_get_clean(), $default_template_path, $template_path, $variables );
			} else {
				$template_content = '';
			}
			do_action( 'woocommerce_multilevel_referral_render_template_post', $default_template_path, $variables, $template_path, $template_content );
			return $template_content;
		}

		/*
		 * Abstract methods
		 */
		/**
		 * Constructor
		 *
		 * @mvc Controller
		 */
		abstract protected function __construct();
		/**
		 * Prepares sites to use the plugin during single or network-wide activation.
		 *
		 * @mvc Controller
		 *
		 * @param bool $network_wide Network wide activation.
		 */
		abstract public function activate( $network_wide );
		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @mvc Controller
		 */
		abstract public function deactivate();
		/**
		 * Register callbacks for actions and filters
		 *
		 * @mvc Controller
		 */
		abstract public function register_hook_callbacks();
		/**
		 * Initializes variables
		 *
		 * @mvc Controller
		 */
		abstract public function init();
		/**
		 * Checks if the plugin was recently updated and upgrades if necessary.
		 *
		 * @mvc Controller
		 *
		 * @param string $db_version Database version.
		 */
		abstract public function upgrade( $db_version = 0 );
		/**
		 * Checks that the object is in a correct state.
		 *
		 * @mvc Model
		 *
		 * @param string $valid An individual property to check, or 'all' to check all of them.
		 *
		 * @return bool
		 */
		abstract protected function is_valid( $valid = 'all' );
	} // end WooCommerce_Multilevel_Referral_Module
}
