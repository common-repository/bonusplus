<?php
/**
 * Messages base class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Entities\Messages;

use Onepix\BonusPlus\Tools\Features;

defined( 'ABSPATH' ) || exit();

/**
 * Messages base class
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
abstract class BPMessage {
	/**
	 * Message type
	 *
	 * @var string
	 */
	protected string $type;

	/**
	 * Return BPDiscountMessage for checkout and BPSimpleMessage for other pages
	 *
	 * @return BPMessage|null
	 */
	public static function for_current_page(): ?BPMessage {
		if ( ! is_admin() && ! is_user_logged_in() && ! is_account_page() && ! is_checkout() && Features::messages_enabled() ) {
			return new BPSimpleMessage();
		}

		return null;
	}

	/**
	 * Get message title
	 *
	 * @return string
	 */
	abstract public function get_title(): string;

	/**
	 * Get message text
	 *
	 * @return string
	 */
	abstract public function get_text(): string;

	/**
	 * Get message image
	 *
	 * @return string
	 */
	abstract public function get_image(): string;

	/**
	 * Get additional text
	 *
	 * @return string
	 */
	abstract public function get_additional_text(): string;

	/**
	 * Get privacy policy short text
	 *
	 * @return string
	 */
	public function get_privacy_policy(): string {
		return bonus_plus()->get_option( 'privacy_policy_text' ) ?: '';
	}

	/**
	 * Get data for frontend
	 *
	 * @return array
	 */
	abstract public function get_js_data(): array;
}
