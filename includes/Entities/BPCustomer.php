<?php
/**
 * Customer entity class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Entities;

use Exception;
use WC_Cart;
use WC_Customer;

defined( 'ABSPATH' ) || exit();

/**
 * Class BPCustomer
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class BPCustomer {
	/**
	 * Session key of phone in $_SESSION
	 */
	const SESSION_KEY_BP_PHONE_NUMBER = 'registered_in_bonus_plus_phone';

	/**
	 * Current customer
	 *
	 * @var WC_Customer|null
	 */
	private ?WC_Customer $customer;

	/**
	 * BPCustomer constructor.
	 *
	 * @param  WC_Customer|int $data  Customer ID or data.
	 */
	public function __construct( $data = null ) {
		if ( ! empty( $data ) ) {
			$this->customer = new WC_Customer( $data );
		} else {
			if ( ! empty( get_current_user_id() ) ) {
				$this->customer = new WC_Customer( get_current_user_id() );
			} else {
				$this->customer = WC()->customer;
			}
		}
	}

	/**
	 * Get current customer
	 *
	 * @return WC_Customer
	 */
	public function customer(): WC_Customer {
		return $this->customer;
	}

	/**
	 * Check if current customer registered in BP
	 *
	 * @param  bool $use_cache  use cached value or get new from API.
	 *
	 * @return bool
	 */
	public function is_registered_in_bp( $use_cache = true ): bool {
		if ( empty( $this->customer ) ) {
			return false;
		}

		if ( $use_cache && isset( $_SESSION['registered_in_bonus_plus'] ) ) {
			return '1' === $_SESSION['registered_in_bonus_plus'];
		}

		$phone_number = $this->get_phone();

		if ( empty( $phone_number ) ) {
			return false;
		}

		try {
			$customer = bonus_plus()->api( 'customer' )->get_by_phone( $phone_number );
		} catch ( Exception $e ) {
			return false;
		}

		$_SESSION['registered_in_bonus_plus'] = is_null( $customer ) ? '0' : '1';

		return ! is_null( $customer );
	}

	/**
	 * Set current user registered in BP and
	 *
	 * @param  string $phone_number  customer phone number.
	 */
	public function set_registered_in_bp( string $phone_number ) {
		$this->customer->set_billing_phone( $phone_number );
		$this->customer->save();

		$_SESSION['registered_in_bonus_plus']          = '1';
		$_SESSION[ self::SESSION_KEY_BP_PHONE_NUMBER ] = $phone_number;

		$this->update_discount_coef();

		WC()->cart->calculate_totals();
	}

	/**
	 * Get BonusPlus discount percent
	 *
	 * @param  bool $use_cache  use cached value or get new from API.
	 *
	 * @return float
	 */
	public function get_discount_percent( $use_cache = true ): float {
		$discount_percent = 0;

		if ( $use_cache ) {
			$discount_percent = $_SESSION['registered_in_bonus_plus_discount_percent'] ?? 0;
		}

		if ( 0 === $discount_percent ) {
			$discount_percent = $this->update_discount_coef();
		}

		return $discount_percent;
	}

	/**
	 * Get BonusPlus discount coef
	 *
	 * @param  bool $use_cache  use cached value or get new from API.
	 *
	 * @return float
	 */
	public function get_discount_coef( $use_cache = true ): float {
		return 1 - $this->get_discount_percent( $use_cache ) / 100;
	}

	/**
	 * Get discount from BonusPlus and set to customer meta
	 *
	 * @return float
	 */
	public function update_discount_coef(): float {
		try {
			$discount_percent = bonus_plus()->api( 'customer' )->get_discount( $this->get_phone() );
		} catch ( Exception $e ) {
			$discount_percent = 0;
		}

		$_SESSION['registered_in_bonus_plus_discount_percent'] = $discount_percent;

		return $discount_percent;
	}

	/**
	 * Get info about customer's bonuses
	 *
	 * @return array|false
	 */
	public function get_bonuses_data() {
		$bonuses_data = bonus_plus()->api( 'customer' )->get_bonuses( $this->get_phone() );

		if ( empty( $bonuses_data ) ) {
			return false;
		}

		$bonuses_data['available'] = $this->get_available_bonuses( $bonuses_data['total'] );
		$bonuses_data['applied']   = $this->get_applied_bonuses();

		return $bonuses_data;
	}

	/**
	 * Get amount of available bonuses.
	 *
	 * @param  int|null $total_bonuses total amount of bonuses or zero to get amount from api.
	 *
	 * @return int
	 */
	protected function get_available_bonuses( ?int $total_bonuses = null ): int {
		if ( is_null( $total_bonuses ) ) {
			$total_bonuses = bonus_plus()->api( 'customer' )->get_bonuses( $this->get_phone() )['total'];
		}

		if ( $total_bonuses <= 0 ) {
			return 0;
		}

		$cart_total = (int) WC()->cart->get_total( '' );

		if ( empty( $cart_total ) ) {
			return 0;
		}

		try {
			$max_debit_bonuses = bonus_plus()->api( 'retail' )->calc( $this )['maxDebitBonuses'];
		} catch (Exception $e) {
			bonus_plus()->log( $e->getMessage() );
			$max_debit_bonuses = 0;
		}

		if ( $max_debit_bonuses <= 0 ) {
			return 0;
		}

		return min(
			$max_debit_bonuses,
			$this->calculate_bonuses_max_percent_of_cart_total( $cart_total ),
			$this->calculate_bonuses_max_percent_of_available( $total_bonuses ),
		);
	}

	/**
	 * Calculate how many bonuses can be applied from the maximum number of bonuses, depending on the cart total
	 *
	 * @param  int $available_bonuses amount of available bonuses.
	 *
	 * @return int
	 */
	protected function calculate_bonuses_max_percent_of_cart_total( $available_bonuses ): int {
		$percent = (int) bonus_plus()->get_option( 'bonuses_max_percent_of_cart_total' ) ?: 100;

		return floor( $available_bonuses * $percent / 100 );
	}

	/**
	 * Calculate how many bonuses can be applied from the maximum number of bonuses, depending on the total bonuses
	 *
	 * @param  int $available_bonuses amount of available bonuses.
	 *
	 * @return int
	 */
	protected function calculate_bonuses_max_percent_of_available( int $available_bonuses ): int {
		$percent = (int) bonus_plus()->get_option( 'bonuses_max_percent_of_available' ) ?: 100;

		return floor( $available_bonuses * $percent / 100 );
	}

	/**
	 * Set amount of applied in checkout bonuses
	 *
	 * @param  int $amount amount of bonuses.
	 *
	 * @throws Exception If amount more than amount of available bonuses.
	 */
	public function set_applied_bonuses( int $amount ) {
		if ( $amount > 0 ) {
			$max_available_bonuses = $this->get_available_bonuses();

			if ( $amount > $max_available_bonuses ) {
				// translators: %1$s amount of available bonuses.
				throw new Exception( sprintf( __( 'Too many bonuses to apply. Available %1$s', 'bonusplus' ), $max_available_bonuses ) );
			}
		}

		$_SESSION['bonus_plus_bonuses'] = $amount;
	}

	/**
	 * Get amount of applied in checkout bonuses
	 *
	 * @return int
	 */
	public function get_applied_bonuses(): int {
		return isset( $_SESSION['bonus_plus_bonuses'] ) ? (int) $_SESSION['bonus_plus_bonuses'] : 0;
	}

	/**
	 * Get customer phone from meta or session
	 *
	 * @return string
	 */
	public function get_phone(): string {
		return $this->customer->get_billing_phone() ?: $this->customer->get_shipping_phone() ?: ( wc_sanitize_phone_number( $_SESSION['registered_in_bonus_plus_phone'] ?? '' )  ) ?: '';
	}
}
