<?php
/**
 * Code Api class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Api;

use Exception;

defined( 'ABSPATH' ) || exit();

/**
 * Code API class
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Code extends Base {
	/**
	 * Send code to phone
	 *
	 * @see https://bonusplus.pro/api/Help/Api/PUT-customer-phone-sendCode
	 *
	 * @param string $phone phone number to send code.
	 *
	 * @throws Exception If error.
	 */
	public function send( string $phone ) {
		$phone = $this->prepare_phone( $phone );
		$res   = $this->request( "customer/$phone/sendCode", 'PUT' );

		if ( is_wp_error( $res ) ) {
			throw new Exception( $res->get_error_message() );
		}
	}

	/**
	 * Check code
	 *
	 * @param string $phone customer phone number.
	 * @param string $code  code number.
	 *
	 * @throws Exception If error.
	 */
	public function check( string $phone, string $code ) {
		$phone = $this->prepare_phone( $phone );
		$res   = $this->request( "customer/$phone/checkCode/$code", 'PUT' );

		if ( is_wp_error( $res ) ) {
			throw new Exception( $res->get_error_message() );
		}
	}
}
