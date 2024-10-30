<?php
/**
 * Base Ajax class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Ajax;

use Exception;

defined( 'ABSPATH' ) || exit();

/**
 * Base Ajax
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
abstract class Base {
	/**
	 * Prefix for actions
	 *
	 * @var string
	 */
	const PREFIX = '';

	/**
	 * Actions for wc api (registration with prefix)
	 *
	 * @var array
	 */
	const ACTIONS = array();

	/**
	 * Ajax Account constructor
	 */
	public function __construct() {
		foreach ( static::ACTIONS as $action ) {
			add_action( 'woocommerce_api_' . self::get_action_name( $action ), array( $this, 'pre_action' ), 10, 0 );
			add_action( 'woocommerce_api_' . self::get_action_name( $action ), array( $this, $action ), 100, 0 );
		}
	}

	/**
	 * Returns wc api url by short action name
	 *
	 * @param string $short_name short action name.
	 * @param bool   $add_nonce  add nonce to url.
	 *
	 * @return false|string
	 */
	public static function get_action_url( string $short_name, bool $add_nonce = true ) {
		$action = static::get_action_name( $short_name );

		if ( empty( $action ) ) {
			return false;
		}

		$request_url = WC()->api_request_url( $action );

		if ( $add_nonce ) {
			$request_url = wc_clean(
				add_query_arg(
					array(
						'_wpnonce' => wp_create_nonce( static::get_action_name( $short_name ) ),
					),
					$request_url
				)
			);
		}

		return $request_url;
	}

	/**
	 * Returns wc api action name by short action name
	 *
	 * @param string $short_name short action name.
	 *
	 * @return false|string
	 */
	public static function get_action_name( string $short_name ) {
		if ( in_array( $short_name, static::ACTIONS, true ) ) {
			return bonus_plus()->get_option( 'id' ) . '_' . static::PREFIX . '_' . $short_name;
		}

		return false;
	}

	/**
	 * Verify nonce in $_GET array
	 *
	 * @param string $function_name     function (action) name to verify. Use __FUNCTION__ to get right function name.
	 * @param bool   $must_be_logged_in must the user be logged in.
	 *
	 * @return void
	 */
	public static function verify_nonce( string $function_name = '', bool $must_be_logged_in = false ) {
		if ( $must_be_logged_in && ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You must be logged in', 'bonusplus' ) );
		}

		$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );

		$verified = wp_verify_nonce( $nonce, static::get_action_name( $function_name ) );

		if ( ! $verified ) {
			wp_die( esc_html__( 'Action failed. Please try again.', 'bonusplus' ) );
		}
	}

	/**
	 * If request content type is application/json and $_POST is empty decode data and put it to $_POST
	 */
	public static function set_json_to_post() {
		if ( empty( $_POST ) && isset( $_SERVER['CONTENT_TYPE'] ) && 'application/json' === $_SERVER['CONTENT_TYPE'] ) {
			$_POST = json_decode( file_get_contents( 'php://input' ), true );
		}
	}

	/**
	 * Action firing before main action
	 */
	public function pre_action() {
		try {
			self::set_json_to_post();
		} catch ( Exception $e ) {
			wp_send_json_error();
		}
	}
}
