<?php
/**
 * Cart handlers
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Ajax;

use Onepix\BonusPlus\Entities\BPCustomer;
use Onepix\BonusPlus\Tools\Features;

defined( 'ABSPATH' ) || exit();

/**
 * Class Cart
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Cart extends Base {
	/**
	 * Prefix for actions
	 *
	 * @var string
	 */
	const PREFIX = 'cart';

	/**
	 * Ajax for wc api (registration with prefix)
	 *
	 * @var array
	 */
	const ACTIONS = array(
		'recalculate_cart_with_new_phone',
	);

	/**
	 * Calculate discount for new phone number in checkout
	 */
	public function recalculate_cart_with_new_phone() {
		self::verify_nonce( __FUNCTION__ );

		if ( ! Features::discounts_enabled() || is_user_logged_in() ) {
			wp_send_json_error();
		}

		$phone_number = isset( $_POST['phone_number'] ) ? wc_clean( wp_unslash( $_POST['phone_number'] ) ) : '';

		if ( empty( $phone_number ) ) {
			wp_send_json_error();
		}

		$bp_customer = new BPCustomer();
		$bp_customer->set_registered_in_bp( $phone_number );
		$bp_customer->is_registered_in_bp( false );

		wp_send_json_success();
	}
}
