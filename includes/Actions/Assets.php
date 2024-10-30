<?php
/**
 * Class Assets
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Actions;

use Onepix\BonusPlus\Ajax\Bonuses;
use Onepix\BonusPlus\Ajax\Cart;
use Onepix\BonusPlus\Ajax\Registration;
use Onepix\BonusPlus\Entities\BPCustomer;
use Onepix\BonusPlus\Entities\Messages\BPMessage;
use Onepix\BonusPlus\Tools\Features;

/**
 * Class Assets
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Assets {
	/**
	 * Assets constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue js and css on frontend
	 */
	public function enqueue_assets() {
		$bp_customer = new BPCustomer();
		$bp_message  = BPMessage::for_current_page();

		bonus_plus()
			->assets()
			->register_style( 'common', 'common.min.css' )
			->enqueue_style( 'common' )
			->register_style( 'messages', 'messages.min.css' )
			->register_style( 'bonuses', 'bonuses.min.css' );

		if ( ! empty( $bp_message ) ) {
			bonus_plus()
				->assets()
				->enqueue_style( 'messages' )
				->register_script(
					'messages',
					'messages.min.js',
					array( 'jquery' ),
					array(
						'bonusPlusMessagesData' => array(
							'urls'             => array(
								'request_code' => Registration::get_action_url( 'request_code' ),
								'register_by_phone_and_code' => Registration::get_action_url( 'register_by_phone_and_code' ),
							),
							'bp_message'       => empty( $bp_message ) ? array() : $bp_message->get_js_data(),
							'registered_in_bp' => $bp_customer->is_registered_in_bp(),
							'isCheckout'       => is_checkout(),
						),
					),
					true
				)
				->enqueue_script( 'messages' );
		}

		if ( is_checkout() && Features::discounts_enabled() ) {
			bonus_plus()
				->assets()
				->register_script(
					'discounts',
					'discounts.min.js',
					array( 'jquery' ),
					array(
						'bonusPlusDiscountsData' => array(
							'urls'              => array(
								'recalculate_cart_with_new_phone' => Cart::get_action_url( 'recalculate_cart_with_new_phone' ),
							),
							'is_user_logged_in' => is_user_logged_in(),
						),
					)
				)
				->enqueue_script( 'discounts' );
		}

		if ( is_checkout() && Features::bonuses_enabled() ) {
			bonus_plus()
				->assets()
				->enqueue_style( 'bonuses' )
				->register_script(
					'bonuses',
					'bonuses.min.js',
					array( 'jquery' ),
					array(
						'bonusPlusBonusesData' => array(
							'urls'              => array(
								'recalculate_bonuses_with_new_phone' => Bonuses::get_action_url( 'recalculate_bonuses_with_new_phone' ),
								'maybe_request_code_for_bonuses' => Bonuses::get_action_url( 'maybe_request_code_for_bonuses' ),
								'apply_bonuses' => Bonuses::get_action_url( 'apply_bonuses' ),
							),
							'isCheckout'        => is_checkout(),
							'bonuses'           => $bp_customer->get_bonuses_data(),
							'is_user_logged_in' => is_user_logged_in(),
						),
					),
					true
				)
				->enqueue_script( 'bonuses' );
		}
	}
}
