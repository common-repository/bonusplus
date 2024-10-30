<?php
/**
 * Registration handlers
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Ajax;

use Exception;
use Onepix\BonusPlus\Entities\BPCustomer;

defined( 'ABSPATH' ) || exit();

/**
 * Class Registration
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Registration extends Base {
	/**
	 * Prefix for actions
	 *
	 * @var string
	 */
	const PREFIX = 'registration';

	/**
	 * Ajax for wc api (registration with prefix)
	 *
	 * @var array
	 */
	const ACTIONS = array(
		'request_code',
		'register_by_phone_and_code',
	);

	/**
	 * Request code
	 *
	 * @phpcs:ignore Squiz.Commenting.FunctionCommentThrowTag.Missing
	 */
	public function request_code() {
		self::verify_nonce( __FUNCTION__ );

		try {
			$phone_number = isset( $_POST['phone_number'] ) ? wc_clean( wp_unslash( $_POST['phone_number'] ) ) : '';

			if ( empty( $phone_number ) ) {
				throw new Exception( __( 'Phone number not provided', 'bonusplus' ) );
			}

			$customer = bonus_plus()->api( 'customer' )->get_by_phone( $phone_number );

			if ( ! empty( $customer ) ) {
				( new BPCustomer() )->set_registered_in_bp( $phone_number );

				wp_send_json(
					array(
						'success'                 => true,
						'user_already_registered' => true,
						'message'                 => sprintf(
						 // translators: %1$s phone number.
							__( 'Customer with phone number %1$s already exists. Use your phone at checkout to get discount', 'bonusplus' ),
							$phone_number
						),
					)
				);
			}

			bonus_plus()->api( 'code' )->send( $phone_number );

			wp_send_json(
				array(
					'success'                 => true,
					'user_already_registered' => false,
				)
			);
		} catch ( Exception $e ) {
			bonus_plus()->log(
				array(
					$e->getMessage(),
					$e->getCode(),
				)
			);

			wp_send_json(
				array(
					'success' => false,
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Register customer in BonusPlus by phone number and code
	 *
	 * @phpcs:ignore Squiz.Commenting.FunctionCommentThrowTag.Missing
	 */
	public function register_by_phone_and_code() {
		self::verify_nonce( __FUNCTION__ );

		$phone_number = isset( $_POST['phone_number'] ) ? wc_clean( wp_unslash( $_POST['phone_number'] ) ) : '';
		$code         = isset( $_POST['code'] ) ? wc_clean( wp_unslash( $_POST['code'] ) ) : '';

		try {
			if ( empty( $phone_number ) || empty( $code ) ) {
				throw new Exception( __( 'Phone number or code not provided', 'bonusplus' ) );
			}

			bonus_plus()->api( 'code' )->check( $phone_number, $code );
			bonus_plus()->api( 'customer' )->register_by_phone( $phone_number );

			( new BPCustomer() )->set_registered_in_bp( $phone_number );
		} catch ( Exception $e ) {
			bonus_plus()->log(
				array(
					$e->getMessage(),
					$e->getCode(),
				)
			);

			wp_send_json(
				array(
					'success' => false,
					'message' => $e->getMessage(),
				)
			);
		}

		wp_send_json_success();
	}
}
