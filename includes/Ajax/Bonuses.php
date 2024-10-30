<?php
/**
 * Bonuses handlers
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Ajax;

use Exception;
use Onepix\BonusPlus\Entities\BPCustomer;
use Onepix\BonusPlus\Tools\Features;

defined( 'ABSPATH' ) || exit();

/**
 * Class Bonuses
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Bonuses extends Base {
	/**
	 * Prefix for actions
	 *
	 * @var string
	 */
	const PREFIX = 'bonuses';

	/**
	 * Ajax for wc api (registration with prefix)
	 *
	 * @var array
	 */
	const ACTIONS = array(
		'recalculate_bonuses_with_new_phone',
		'maybe_request_code_for_bonuses',
		'apply_bonuses',
	);

	/**
	 * Bonuses constructor. Register ajax actions if bonuses enabled
	 */
	public function __construct() {
		if ( Features::bonuses_enabled() ) {
			parent::__construct();
		}
	}

	/**
	 * Get bonuses for new phone number in checkout
	 */
	public function recalculate_bonuses_with_new_phone() {
		self::verify_nonce( __FUNCTION__ );

		if ( is_user_logged_in() ) {
			wp_send_json_error();
		}

		$phone_number = isset( $_POST['phone_number'] ) ? wc_clean( wp_unslash( $_POST['phone_number'] ) ) : '';

		if ( empty( $phone_number ) ) {
			wp_send_json_error();
		}

		$bp_customer = new BPCustomer();
		$bp_customer->customer()->set_billing_phone( $phone_number );
		$bp_customer->customer()->save();

		$bp_customer->set_applied_bonuses( 0 );

		wp_send_json(
			array(
				'success' => true,
				'bonuses' => $bp_customer->get_bonuses_data(),
			)
		);
	}

	/**
	 * Request code for using bonuses if code required
	 */
	public function maybe_request_code_for_bonuses() {
		self::verify_nonce( __FUNCTION__ );

		if ( bonus_plus()->get_option( 'bonuses_write_off_confirmation_required' ) ) {
			try {
				bonus_plus()->api( 'code' )->send( ( new BPCustomer() )->get_phone() );
				wp_send_json(
					array(
						'success'   => true,
						'need_code' => true,
					)
				);
			} catch ( Exception $e ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => $e->getMessage(),
					)
				);
			}
		} else {
			wp_send_json(
				array(
					'success'   => true,
					'need_code' => false,
				)
			);
		}
	}

	/**
	 * Apply bonuses
	 */
	public function apply_bonuses() {
		self::verify_nonce( __FUNCTION__ );

		$bonuses_amount = isset( $_POST['bonuses_amount'] ) ? (int) wc_clean( wp_unslash( $_POST['bonuses_amount'] ) ) : 0;
		$code           = isset( $_POST['code'] ) ? wc_clean( wp_unslash( $_POST['code'] ) ) : '';

		if ( empty( $bonuses_amount ) || $bonuses_amount <= 0 ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => 'Неверное количество баллов',
				)
			);
		}

		$bp_customer = new BPCustomer();

		if ( empty( $bp_customer->get_phone() ) ) {
			wp_send_json(
				array(
					'success' => false,
					'error'   => 'Укажите свой номер телефона при оформлении заказа перед применением баллов',
				)
			);
		}

		$need_sms_confirmation = bonus_plus()->get_option( 'bonuses_write_off_confirmation_required' );

		if ( $need_sms_confirmation ) {
			if ( empty( $code ) ) {
				wp_send_json(
					array(
						'success' => false,
						'error'   => sprintf( 'Требуется ввести код подтверждения, отправленный на номер %s', $bp_customer->get_phone() ),
					)
				);
			}

			try {
				bonus_plus()->api( 'code' )->check( $bp_customer->get_phone(), $code );
			} catch ( Exception $e ) {
				wp_send_json(
					array(
						'success'    => false,
						'wrong_code' => true,
						'error'      => $e->getMessage(),
					)
				);
			}
		}

		try {
			$bp_customer->set_applied_bonuses( $bonuses_amount );
		} catch ( Exception $e ) {
			wp_send_json(
				array(
					'success'    => false,
					'wrong_code' => false,
					'error'      => $e->getMessage(),
				)
			);
		}

		wp_send_json(
			array(
				'success' => true,
				'bonuses' => $bp_customer->get_bonuses_data(),
			)
		);
	}
}
