<?php
/**
 * Class for flushin rewrite rools
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Tools;

defined( 'ABSPATH' ) || exit();

/**
 * Class RewriteRules
 *
 * @package Onepix\BonusPlus
 * @since 1.1.0
 */
class RewriteRules {
	/**
	 * Flush rewrite rules if it was not flashed
	 */
	public static function flush_rewrite_rules() {
		if ( ! self::was_rewrite_rules_flashed() ) {
			flush_rewrite_rules( false );
			self::set_rewrite_rules_flashed( true );
		}
	}

	/**
	 * Rewrite rules after plugin disabled
	 */
	public static function disable_my_account_pages() {
		flush_rewrite_rules( false );
		self::set_rewrite_rules_flashed( false );
	}

	/**
	 * Set flushed or not rewrite rules
	 *
	 * @param  bool $value was rules flushed.
	 */
	public static function set_rewrite_rules_flashed( bool $value ) {
		update_option(
			self::get_rewrite_rules_flushed_option_key(),
			$value ? '1' : '0'
		);
	}

	/**
	 * Check if rewrite rules was flushed
	 *
	 * @return bool
	 */
	public static function was_rewrite_rules_flashed(): bool {
		return get_option( self::get_rewrite_rules_flushed_option_key() ) === '1';
	}

	/**
	 * Get rewrite rules option key
	 *
	 * @return string
	 */
	public static function get_rewrite_rules_flushed_option_key(): string {
		return bonus_plus()->get_option( 'id' ) . '_rewrite_rules_flushed';
	}
}
