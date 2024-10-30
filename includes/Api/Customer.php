<?php
/**
 * Customer Api class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Api;

use Exception;
use Onepix\BonusPlus\Entities\BPCustomer;
use Onepix\BonusPlus\Entities\BPOrder;
use WC_Order;

defined( 'ABSPATH' ) || exit();

/**
 * Customer API class
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Customer extends Base {
	/**
	 * Get customer data by phone
	 *
	 * @see https://bonusplus.pro/api/Help/Api/GET-customer_id_ean_phone
	 *
	 * @param string $phone_number customer phone number.
	 * @param bool   $throw_if_not_found throw exception if customer not found in BonusPlus.
	 * @param bool   $use_cache use previous request or re-request customer.
	 *
	 * @return array|null
	 * @throws Exception If API error or customer not found and $throw_if_not_found on true.
	 */
	public function get_by_phone( string $phone_number, bool $throw_if_not_found = false, bool $use_cache = true ): ?array {
		static $cached_customers = array();

		if ( empty( $phone_number ) ) {
			throw new Exception( __( 'Phone number must not be empty', 'bonusplus' ) );
		}

		if ( $use_cache && isset( $cached_customers[ $phone_number ] ) ) {
			return $cached_customers[ $phone_number ];
		}

		$res = $this->request(
			'customer',
			'GET',
			array(
				'phone' => $phone_number,
			)
		);

		if ( is_wp_error( $res ) ) {
			if ( ! $throw_if_not_found && $res->get_error_code() === 'CUSTOMER_NOT_FOUND' ) {
				return null;
			}

			throw new Exception( $res->get_error_message() );
		}

		$cached_customers[ $phone_number ] = $res;

		return $res;
	}

	/**
	 * Register customer by data from WC_Order
	 *
	 * @see https://bonusplus.pro/api/Help/Api/POST-customer
	 *
	 * @param mixed $order order to get customer.
	 *
	 * @return array
	 * @throws Exception If API error.
	 */
	public function register_by_order( $order ) {
		$order = wc_get_order( $order );

		$res = $this->request(
			'customer',
			'POST',
			array(
				'phone'   => $order->get_billing_phone(),
				'fn'      => $order->get_billing_first_name(),
				'ln'      => $order->get_billing_last_name(),
				'email'   => $order->get_billing_email(),
				'address' => preg_replace(
					'!\s+!',
					' ',
					implode(
						' ',
						array(
							$order->get_billing_address_1(),
							$order->get_billing_address_2(),
							$order->get_billing_city(),
							$order->get_billing_state(),
							$order->get_billing_postcode(),
						)
					)
				),
			)
		);

		if ( is_wp_error( $res ) ) {
			throw new Exception( $res->get_error_message() );
		}

		return $res;
	}

	/**
	 * Register customer by data from WC_Order
	 *
	 * @see https://bonusplus.pro/api/Help/Api/POST-customer
	 *
	 * @param string $phone customer phone number.
	 *
	 * @return array
	 * @throws Exception If API error.
	 */
	public function register_by_phone( string $phone ): array {
		$res = $this->request(
			'customer',
			'POST',
			array(
				'phone' => $phone,
			)
		);

		if ( is_wp_error( $res ) ) {
			throw new Exception( $res->get_error_message() );
		}

		return $res;
	}

	/**
	 * Get customer discount by his phone number
	 *
	 * @param string $phone customer phone number.
	 *
	 * @return float
	 * @throws Exception If API error or customer doesn't exist.
	 */
	public function get_discount( string $phone ): float {
		$res = $this->get_by_phone( $phone );

		if ( empty( $res ) ) {
			throw new Exception( __( 'Customer not found', 'bonusplus' ) );
		}

		return (float) $res['baseDiscountPercent'] ?? 0;
	}

	/**
	 * Get customer bonuses info by phone number
	 *
	 * @param  string $phone phone number of customer.
	 *
	 * @return array
	 */
	public function get_bonuses( string $phone ): array {
		try {
			$customer = $this->get_by_phone( $phone, true );
		} catch ( Exception $e ) {
			return array();
		}

		return array(
			'is_denied'    => $customer['bonusDebitDenided'],
			'total'        => $customer['availableBonuses'],
			'multiplicity' => $customer['multiplicityDebitBonus'],
		);
	}

	/**
	 * Reserve bonuses
	 *
	 * @param  BPOrder $bp_order order to reserve.
	 *
	 * @return bool
	 */
	public function reserve_bonuses( BPOrder $bp_order ): bool {
		$bp_customer = new BPCustomer( $bp_order->order()->get_customer_id() );

		try {
			$phone_number = $this->prepare_phone( $bp_customer->get_phone() );

			$this->request(
				"customer/$phone_number/balance/reserve",
				'PATCH',
				array(
					'id'     => $bp_order->order()->get_id(),
					'amount' => $bp_customer->get_applied_bonuses(),
				)
			);

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Release reserved bonuses
	 *
	 * @param  BPOrder $bp_order order to release bonuses.
	 *
	 * @return bool
	 */
	public function release_bonuses( BPOrder $bp_order ): bool {
		$bp_customer = new BPCustomer( $bp_order->order()->get_customer_id() );

		try {
			$phone_number = $this->prepare_phone( $bp_customer->get_phone() );

			$this->request(
				"customer/$phone_number/balance/reserve",
				'PATCH',
				array(
					'id'     => $bp_order->order()->get_id(),
					'amount' => -1 * $bp_order->get_applied_bonuses(),
				)
			);

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}
}
