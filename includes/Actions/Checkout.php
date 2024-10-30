<?php
/**
 * Cart actions class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Actions;

use Exception;
use Onepix\BonusPlus\Entities\BPCustomer;
use Onepix\BonusPlus\Tools\Features;

defined( 'ABSPATH' ) || exit();

/**
 * Class Cart
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Checkout {
	/**
	 * Cart constructor.
	 */
	public function __construct() {
		add_filter( 'default_checkout_billing_phone', array( $this, 'add_bp_phone_to_checkout_input' ), 100, 1 );
		add_filter( 'woocommerce_review_order_after_order_total', array( $this, 'add_earned_bonuses_to_order_review' ), 100, 1 );

		if ( Features::bonuses_enabled() ) {
			add_action( 'woocommerce_checkout_order_review', array( $this, 'add_bonuses_form' ), 15 );
		}
	}

	/**
	 * If phone number is empty set it to bp customer's phone number
	 *
	 * @param  mixed  $value  default phone.
	 *
	 * @return mixed
	 */
	public function add_bp_phone_to_checkout_input( $value ) {
		if ( empty( $value ) ) {
			$value = ( new BPCustomer() )->get_phone();
		}

		return $value;
	}

	/**
	 * Add earned bonuses to order review
	 */
	public function add_earned_bonuses_to_order_review() : void {
		if( ! Features::bonuses_enabled() ) {
			return;
		}

		$bp_customer = new BPCustomer();

		if ( ! $bp_customer->is_registered_in_bp() ) {
			return;
		}

		static $earned_bonuses = null;

		if( is_null( $earned_bonuses ) ) {
			try {
				$earned_bonuses = bonus_plus()->api( 'retail' )->calc( $bp_customer )['earnedBonuses'];
			} catch ( Exception $e ) {
				$earned_bonuses = 0;
				bonus_plus()->log( $e->getMessage() );
				return;
			}
		}

		bonus_plus()->include_template(
			'checkout/order-review-bonuses.php',
			array(
				'earned_bonuses' => $earned_bonuses,
			)
		);
	}

	/**
	 * Add bonuses form to checkout.
	 */
	public function add_bonuses_form() {
		bonus_plus()->include_template( 'checkout/form-bonuses.php' );
	}
}
