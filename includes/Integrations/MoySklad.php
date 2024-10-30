<?php
/**
 * MoySklad integrations class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Integrations;

use Onepix\BonusPlus\Entities\BPOrder;

defined( 'ABSPATH' ) || exit();

/**
 * Class MoySklad
 *
 * @package Onepix\BonusPlus
 * @since   1.2.1
 */
class MoySklad {
	/**
	 * MoySklad constructor.
	 */
	public function __construct() {
		add_action( 'wooms_order_data', array( $this, 'add_discount_to_wooms_order_data' ), 100, 2 );
	}

	/**
	 * Add discount to moy sklad order positions
	 *
	 * @param array $order_data default moy sklad order data.
	 * @param int   $order_id woocommerce order id.
	 *
	 * @return array
	 */
	public function add_discount_to_wooms_order_data( array $order_data, int $order_id ): array {
		bonus_plus()->log(
			array(
				'$order_id'   => $order_id,
				'$order_data' => $order_data,
			)
		);

		if ( empty( $order_data['positions'] ) ) {
			return $order_data;
		}

		$discount_percent = ( new BPOrder( $order_id ) )->get_discount_percent_by_bonuses();

		bonus_plus()->log(
			array(
				'$discount_percent' => $discount_percent,
			)
		);

		if ( empty( $discount_percent ) ) {
			return $order_data;
		}

		foreach ( $order_data['positions'] as &$position ) {
			$position['discount'] += $discount_percent;
		}

		bonus_plus()->log(
			array(
				'$order_data[\'positions\']' => $order_data['positions'],
			)
		);

		return $order_data;
	}
}
