<?php
/**
 * Class Features
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Tools;

/**
 * Class Features
 *
 * @package Onepix\BonusPlus
 * @since   1.2.0
 */
abstract class Features {
	/**
	 * Check bonuses feature
	 *
	 * @return bool
	 */
	public static function bonuses_enabled(): bool {
		return self::enabled( 'bonuses' );
	}

	/**
	 * Check messages feature
	 *
	 * @return bool
	 */
	public static function messages_enabled(): bool {
		return self::enabled( 'messages' );
	}

	/**
	 * Check discounts feature
	 *
	 * @return bool
	 */
	public static function discounts_enabled(): bool {
		return self::enabled( 'discounts' );
	}

	/**
	 * Check double discounts feature
	 *
	 * @return bool
	 */
	public static function double_discount_enabled(): bool {
		return self::discounts_enabled() && self::enabled( 'discounts_double' );
	}

	/**
	 * Check if feature enabled
	 *
	 * @param string $feature feature name to check.
	 *
	 * @return bool
	 */
	private static function enabled( string $feature ): bool {
		return (bool) apply_filters( "bonus_plus_feature_{$feature}_enabled", bonus_plus()->get_option( "{$feature}_enabled" ) );
	}
}
