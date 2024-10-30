<?php
/**
 * Order entity class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Entities;

use Exception;
use WC_Order;

defined( 'ABSPATH' ) || exit();

/**
 * Class BPOrder
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class BPOrder {
	const META_ORDER_CREATED_IN_BP = '_bonus_plus_order_created';

	/**
	 * Current order
	 *
	 * @var WC_Order|null
	 */
	private ?WC_Order $order;

	/**
	 * BPOrder constructor.
	 *
	 * @param  mixed $order  data to get woo order.
	 */
	public function __construct( $order ) {
		$this->order = wc_get_order( $order );
	}

	/**
	 * Get current order
	 *
	 * @return WC_Order
	 */
	public function order(): WC_Order {
		return $this->order;
	}

	/**
	 * Create order in BonusPlus
	 *
	 * @see \Onepix\BonusPlus\Api\Retail::make()
	 */
	public function create_bp_order() {
		if ( $this->is_bp_order_created() ) {
			return true;
		}

		$result = bonus_plus()->api( 'retail' )->make( $this );

		if ( is_wp_error( $result ) ) {
			$this->order->add_order_note(
				sprintf(
				// translators: %1$s - error message, %2$s - error code.
					__( 'Order not created in BonusPlus. Error: %1$s (%2$s)', 'bonusplus' ),
					$result->get_error_message(),
					$result->get_error_code()
				)
			);

			return false;
		}

		// translators: %s amount of applied bonuses.
		$this->order->add_order_note( sprintf( __( 'Order created in BonusPlus. Applied %s bonuses', 'bonusplus' ), $this->get_applied_bonuses() ) );
		$this->order->update_meta_data( static::META_ORDER_CREATED_IN_BP, '1' );
		$this->order->save();

		( new BPCustomer( $this->order->get_customer_id() ) )->update_discount_coef();

		return true;
	}

	/**
	 * Set whether the order is created in BonusPlus or not
	 *
	 * @param  bool $created create or not.
	 */
	public function set_bp_order_created( bool $created ) {
		$this->order->update_meta_data( static::META_ORDER_CREATED_IN_BP, $created ? '1' : '0' );
		$this->order->save();
	}

	/**
	 * Check if order in BonusPlus created
	 *
	 * @return bool
	 */
	public function is_bp_order_created(): bool {
		return $this->order->get_meta( static::META_ORDER_CREATED_IN_BP ) === '1';
	}

	/**
	 * Set applied bonuses to order meta
	 *
	 * @param  int $bonuses bonuses amount to set.
	 */
	public function set_applied_bonuses( int $bonuses ) {
		$this->order->update_meta_data( '_bonus_plus_applied_bonuses', $bonuses );
		$this->order->save();
	}

	/**
	 * Get applied bonuses
	 *
	 * @return int
	 */
	public function get_applied_bonuses(): int {
		return (int) $this->order->get_meta( '_bonus_plus_applied_bonuses' ) ?: 0;
	}

	/**
	 * Calculate discount depending on bonuses
	 *
	 * @return float
	 */
	public function get_discount_percent_by_bonuses(): float {
		$bonuses = $this->get_applied_bonuses();
		$total   = $this->order()->get_total( '' ) + $bonuses;

		if ( empty( $total ) ) {
			return 0;
		}

		return round( $bonuses / $total * 100, 2 );
	}
}
