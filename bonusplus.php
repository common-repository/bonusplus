<?php
/**
 * Plugin Name: BonusPlus
 * Description: Bonus system via BonusPlus.
 * Author: Onepix
 * Author URI: https://onepix.net
 * Text Domain: bonusplus
 * Domain Path: /languages
 * WC requires at least: 4.8
 * WC tested up to: 6.2.1
 * Requires at least: 5.7
 * Requires PHP: 7.4
 * Version: 1.3.6
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus;

defined( 'ABSPATH' ) || exit();

/**
 * Class Main
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Main {
	/**
	 * Class instance
	 *
	 * @var Main|null
	 */
	private static ?Main $instance = null;

	/**
	 * Plugin id
	 *
	 * @var string
	 */
	private string $plugin_id = 'bonusplus';

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private string $version = '1.3.6';

	/**
	 * Plugin url
	 *
	 * @var string
	 */
	private string $plugin_url;

	/**
	 * Plugin path
	 *
	 * @var string
	 */
	private string $plugin_path;

	/**
	 * Plugin main file name with plugin dir name
	 *
	 * @var string
	 */
	private string $plugin_file;

	/**
	 * Assets url
	 *
	 * @var string
	 */
	private string $assets_url;

	/**
	 * Settings array
	 *
	 * @var array|null
	 */
	private ?array $plugin_options = null;

	/**
	 * Admin class instance
	 *
	 * @var Api\Main|null
	 */
	private ?Api\Main $api = null;

	/**
	 * Admin class instance
	 *
	 * @var Admin\Main|null
	 */
	private ?Admin\Main $admin = null;

	/**
	 * Assets class instance
	 *
	 * @var Tools\Assets|null
	 */
	private ?Tools\Assets $assets = null;

	/**
	 * Main constructor.
	 */
	private function __construct() {
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}

		$this->plugin_url  = plugin_dir_url( __FILE__ );
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->assets_url  = $this->plugin_url . '/assets/dist/';
		$this->plugin_file = basename( $this->plugin_path ) . '/index.php';

		require_once 'vendor/autoload.php';

		load_plugin_textdomain( $this->plugin_id, false, "$this->plugin_id/languages/" );

		new Integrations\Main();
		add_action( 'plugins_loaded', array( $this, 'action_plugins_loaded' ) );

		register_activation_hook( __FILE__, array( $this, 'activation_hook' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation_hook' ) );
	}

	/**
	 * Get api class instance.
	 *
	 * @param string $api_name Api name to get.
	 *
	 * @return object|null
	 */
	public function api( string $api_name ): ?object {
		if ( is_null( $this->api ) ) {
			$this->api = new Api\Main();
		}

		return $this->api->get( $api_name );
	}

	/**
	 * Get api class instance.
	 *
	 * @param string $page_slug Page slug to get.
	 *
	 * @return object|null
	 */
	public function pages( string $page_slug ): ?object {
		if ( is_null( $this->admin ) ) {
			$this->admin = new Admin\Main();
		}

		return $this->admin->pages()->get( $page_slug );
	}

	/**
	 * Get assets class instance.
	 *
	 * @return Tools\Assets
	 */
	public function assets(): Tools\Assets {
		if ( is_null( $this->assets ) ) {
			$this->assets = new Tools\Assets();
		}

		return $this->assets;
	}

	/**
	 * Plugins loaded handler
	 */
	public function action_plugins_loaded() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			$this->disable_plugin();
			return;
		}

		if ( is_null( $this->admin ) ) {
			$this->admin = new Admin\Main();
		}

		new Actions\Main();
		new MyAccount\Main();
		new Ajax\Main();
	}

	/**
	 * Activation hook
	 */
	public function activation_hook() {
		Actions\Export::activate_cron();
	}

	/**
	 * Deactivation hook
	 */
	public function deactivation_hook() {
		Actions\Export::deactivate_cron();

		Tools\RewriteRules::disable_my_account_pages();
	}

	/**
	 * Get setting
	 *
	 * @param  string $name  Setting key.
	 *
	 * @return mixed
	 */
	public function get_option( string $name ) {
		// Init plugin options.
		if ( empty( $this->plugin_options ) ) {
			$this->plugin_options = array(
				'id'             => $this->plugin_id,
				'plugin_id'      => $this->plugin_id,
				'version'        => $this->version,
				'plugin_url'     => $this->plugin_url,
				'plugin_path'    => $this->plugin_path,
				'plugin_file'    => $this->plugin_file,
				'assets_url'     => $this->assets_url,
				'languages_path' => $this->plugin_path . 'languages',
			);
		}

		// Return plugin option.
		return $this->plugin_options[ $name ] ?? $this->pages( 'settings' )->get_option( $name );
	}

	/**
	 * Wrapper of wc_get_template function
	 *
	 * @param string $template Template name.
	 * @param array  $args     Arguments.
	 * @param bool   $return   Return or echo. Echo by default.
	 *
	 * @return bool|string
	 */
	public function include_template( string $template, $args = array(), $return = false ) {
		if ( $return ) {
			ob_start();
		}

		wc_get_template(
			$template,
			$args,
			'',
			$this->plugin_path . 'templates/'
		);

		if ( $return ) {
			return ob_get_clean();
		}

		return true;
	}

	/**
	 * Check if template file exists
	 *
	 * @param string $template Template name.
	 */
	public function template_exists( string $template ): bool {
		return file_exists( $this->plugin_path . 'templates/' . $template );
	}

	/**
	 * Disable plugin
	 */
	public function disable_plugin() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		deactivate_plugins( $this->plugin_id . '/index.php' );
	}

	/**
	 * Add data to Woo logs
	 *
	 * @param array|string $data             Data to add to logs.
	 * @param string       $code_source      Source of log in code.
	 * @param string       $log_file_postfix Postfix for woo log file.
	 */
	public function log( $data, string $code_source = '', string $log_file_postfix = '' ) {
		if ( function_exists( 'wc_get_logger' ) &&
			 ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || $this->get_option( 'debug_logging' ) === 'yes' ) ) {
			if ( empty( $code_source ) ) {
				$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[1]; //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace

				$code_source  = isset( $backtrace['class'] ) ? $backtrace['class'] . '::' : '';
				$code_source .= $backtrace['function'] ?? '';
			}

			$data = array(
				'source' => $code_source,
				'data'   => $data,
			);

			wc_get_logger()->debug(
				print_r( $data, true ), // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
				array( 'source' => $this->plugin_id . ( empty( $log_file_postfix ) ? '' : "-$log_file_postfix" ) )
			);
		}
	}

	/**
	 * Get singleton instance
	 *
	 * @return Main
	 */
	public static function get_instance(): Main {
		static $instance_requested = false;

		if ( true === $instance_requested && is_null( self::$instance ) ) {
			$message = 'Function bonus_plus() called in time of initialization main plugin class. Recursion prevented';

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$message .= '<br>Stack trace for debugging:<br><pre>' . print_r( //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
					debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
					true
				) . '</pre>';
			}

			wp_die( $message ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$instance_requested = true;

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

require_once 'main-class-shortcut.php';

bonus_plus();
