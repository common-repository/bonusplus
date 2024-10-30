<?php
/**
 * Order actions class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Actions;

use Exception;
use Onepix\BonusPlus\Entities\BPCustomer;
use Onepix\BonusPlus\Entities\BPOrder;
use Onepix\BonusPlus\Tools\Features;
use WC_Order;

defined( 'ABSPATH' ) || exit();

/**
 * Class Order
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Order {
	/**
	 * Order constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_new_order', array( $this, 'register_customer_in_bp' ), 10, 2 );
		add_action( 'woocommerce_new_order', array( $this, 'set_is_bp_order_created' ), 10, 2 );
		add_action( 'woocommerce_order_status_' . bonus_plus()->get_option( 'bp_payment_status' ), array( $this, 'make_order_in_bp' ), 10, 2 );

		if ( Features::bonuses_enabled() ) {
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'reserve_bp_bonuses' ), 1, 3 );
		}
	}

	/**
	 * Register customer in BP by order
	 *
	 * @param int      $order_id current order id.
	 * @param WC_Order $order current order.
	 */
	public function register_customer_in_bp( int $order_id, WC_Order $order ) {
		if ( is_user_logged_in() ) {
			return;
		}

		try {
			bonus_plus()->api( 'customer' )->register_by_order( $order );
		} catch ( Exception $e ) {
			bonus_plus()->log( $e->getMessage() );
		}
	}

	/**
	 * Set BonusPlus order created on false
	 *
	 * @param int      $order_id current order id.
	 * @param WC_Order $order current order.
	 */
	public function set_is_bp_order_created( int $order_id, WC_Order $order ) {
		( new BPOrder( $order ) )->set_bp_order_created( false );
	}

	/**
	 * Export order to BP
	 *
	 * @param int      $order_id order id.
	 * @param WC_Order $order order instance.
	 */
	public function make_order_in_bp( int $order_id, WC_Order $order ) {
		if ( apply_filters( 'bonus_plus_create_order_automatically', true, $order ) ) {
			( new BPOrder( $order ) )->create_bp_order();
		} else {
			bonus_plus()->log("Automatic order #$order_id creation prevented via filter");
		}
	}

	/**
	 * Reserve bonuses for order
	 *
	 * @param  int      $order_id current order id.
	 * @param  array    $posted_data raw order data.
	 * @param  WC_Order $order current order.
	 *
	 * @throws Exception If points cannot be applied.
	 */
	public function reserve_bp_bonuses( int $order_id, array $posted_data, WC_Order $order ) {
		$bp_customer = new BPCustomer( $order->get_customer_id() );
		$bp_order    = new BPOrder( $order );

		$bonuses = $bp_customer->get_applied_bonuses();

		if ( empty( $bonuses ) ) {
			return;
		}

		$result = bonus_plus()->api( 'customer' )->reserve_bonuses( $bp_order );

		if ( ! $result ) {
			$bp_customer->set_applied_bonuses( 0 );
			throw new Exception( 'Невозможно применить скидочные баллы' );
		}

		$bp_order->set_applied_bonuses( $bonuses );
	}
}
