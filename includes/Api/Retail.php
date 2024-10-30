<?php
/**
 * Retail Api class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Api;

use Exception;
use Onepix\BonusPlus\Entities\BPCustomer;
use Onepix\BonusPlus\Entities\BPOrder;
use WC_Cart;
use WC_Order_Item_Product;
use WP_Error;

defined( 'ABSPATH' ) || exit();

/**
 * Retail API class
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Retail extends Base {

	/**
	 * Make retail in BonusPlus
	 *
	 * @param  BPOrder $bp_order order to get data.
	 */
	public function make( BPOrder $bp_order ) {
		$retail_items = array();

		foreach ( $bp_order->order()->get_items() as $order_item ) {
			/**
			 * Only WC_Order_Item_Product.
			 *
			 * @var WC_Order_Item_Product $order_item
			 */
			$product = wc_get_product( $order_item->get_variation_id() ?: $order_item->get_product_id() );

			if ( empty( $product ) ) {
				continue;
			}

			$retail_items[] = array(
				'sum'     => $product->get_regular_price( '' ) * $order_item->get_quantity(),
				'qnt'     => $order_item->get_quantity(),
				'product' => $product->get_id(),
				'ds'      => max( (float) $product->get_sale_price( '' ) - (float) $product->get_regular_price(), 0 ),
				'price'   => $product->get_regular_price(),
			);
		}

		bonus_plus()->api( 'customer' )->release_bonuses( $bp_order );

		return $this->request(
			'retail',
			'POST',
			apply_filters(
				'bonus_plus_create_retail_data',
				array(
					'phone'      => $bp_order->order()->get_billing_phone() ?: $bp_order->order()->get_shipping_phone(),
					'items'      => $retail_items,
					'bonusDebit' => $bp_order->get_applied_bonuses(),
					'date'       => wp_date( 'Y-m-d H:i:s' ),
					'store'      => bonus_plus()->get_option( 'api_shop_name' ),
				)
			)
		);
	}

	/**
	 * Calc retail in BonusPlus
	 *
	 * @param  BPCustomer  $bp_customer  current customer.
	 * @param  WC_Cart|null  $cart  customer's cart.
	 *
	 * @return array|WP_Error
	 * @throws Exception
	 */
	public function calc( BPCustomer $bp_customer, ?WC_Cart $cart = null ) {
		if ( is_null( $cart ) ) {
			$cart = WC()->cart;
		}

		$retail_items = array();

		foreach ( $cart->get_cart() as $cart_item ) {
			$product = $cart_item['data'];

			$retail_items[] = array(
				'sum'     => $product->get_regular_price( '' ) * $cart_item['quantity'],
				'qnt'     => $cart_item['quantity'],
				'product' => $product->get_id(),
				'ds'      => max( (float) $product->get_sale_price( '' ) - (float) $product->get_regular_price(), 0 ),
				'price'   => $product->get_regular_price(),
			);
		}

		$data = $this->request(
			'retail/calc',
			'PUT',
			array(
				'phone'      => $bp_customer->get_phone(),
				'bonusDebit' => $bp_customer->get_applied_bonuses(),
				'items'      => $retail_items,
				'store'      => bonus_plus()->get_option( 'api_shop_name' ),
			)
		);

		if( is_wp_error( $data ) ) {
			throw new Exception( $data->get_error_message() );
		}

		$data['earnedBonuses'] = array_reduce(
			$data['discount'] ?? array(),
			function ( $bonuses, $item ) {
				$bonuses += $item['cb'];
				return $bonuses;
			},
			0
		);

		return $data;
	}
}
