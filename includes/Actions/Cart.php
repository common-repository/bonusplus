<?php
/**
 * Cart actions class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Actions;

use Onepix\BonusPlus\Entities\BPCustomer;
use Onepix\BonusPlus\Entities\BPProduct;
use Onepix\BonusPlus\Tools\Features;
use WC_Cart;
use WC_Product;

defined( 'ABSPATH' ) || exit();

/**
 * Class Cart
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Cart {
	/**
	 * Cart constructor.
	 */
	public function __construct() {
		if ( Features::discounts_enabled() ) {
			add_filter( 'woocommerce_before_calculate_totals', array( $this, 'add_bp_discount_to_totals_calculation' ), 100, 2 );
			add_filter( 'woocommerce_applied_coupon', array( $this, 'bp_discount_disabled_notification' ), 10, 0 );
			add_filter( 'woocommerce_cart_item_price', array( $this, 'add_bp_discount_to_cart_table' ), 100, 2 );
		}

		if ( Features::bonuses_enabled() ) {
			add_action( 'woocommerce_cart_calculate_fees', array( $this, 'set_fee' ) );

			add_action( 'woocommerce_add_to_cart', array( $this, 'cart_updated' ) );
			add_action( 'woocommerce_update_cart_action_cart_updated', array( $this, 'cart_updated' ) );
		}
	}

	/**
	 * Add price with BonusPlus discount to cart total calculation
	 *
	 * @param WC_Cart $cart current cart object.
	 */
	public function add_bp_discount_to_totals_calculation( WC_Cart $cart ) {
		if ( ! empty( $cart->applied_coupons ) ) {
			return;
		}

		foreach ( $cart->cart_contents as &$cart_item ) {
			/**
			 * Product from cart item
			 *
			 * @var WC_Product $product
			 */
			$product = $cart_item['data'];

			if ( BPProduct::can_add_bp_discount( $product ) ) {
				$cart_item['bonus_plus_discount'] = true;

				if ( Features::double_discount_enabled() ) {
					$discount_percent = ( new BPCustomer() )->get_discount_percent();
					$product->set_price( round( (float) $product->get_price() - $product->get_regular_price() * $discount_percent / 100, 2 ) );
				} else {
					$product->set_price( round( (float) $product->get_regular_price() * ( new BPCustomer() )->get_discount_coef() / 100, 2 ) );
				}

				$cart_item['line_total'] = $product->get_price() * $cart_item['quantity'];
			} else {
				unset( $cart_item['bonus_plus_discount'] );
			}
		}
	}

	/**
	 * BonusPlus discount disabled notification
	 */
	public function bp_discount_disabled_notification() {
		wc_add_notice(
			apply_filters( 'bonus_plus_discount_disabled_notification', __( 'Coupons disable BonusPlus discount', 'bonusplus' ) ),
			'error'
		);
	}

	/**
	 * Add bonus plus discount amount to product rows in cart table
	 *
	 * @param  string $price default cart item price html.
	 * @param  array  $cart_item current cart item.
	 *
	 * @return string
	 */
	public function add_bp_discount_to_cart_table( string $price, array $cart_item ): string {
		if ( ! empty( $cart_item['bonus_plus_discount'] ) ) {
			/**
			 * Product from cart item
			 *
			 * @var WC_Product $product
			 */
			$product = $cart_item['data'];

			$price = $product->get_price_html();
		}

		return $price;
	}

	/**
	 * Set bonuses to cart fee
	 *
	 * @param  WC_Cart $cart cart to set fee.
	 */
	public function set_fee( WC_Cart $cart ) {
		$bonuses = ( new BPCustomer() )->get_applied_bonuses();

		if ( ! empty( $bonuses ) ) {
			$cart->add_fee( __( 'Bonuses', 'bonusplus' ), - $bonuses );
		}
	}

	/**
	 *  Cancel discount after cart update
	 */
	public function cart_updated() {
		( new BPCustomer() )->set_applied_bonuses( 0 );

		return true;
	}
}
