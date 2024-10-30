<?php
/**
 * Simple message class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Entities\Messages;

defined( 'ABSPATH' ) || exit();

/**
 * BPSimpleMessage class
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class BPSimpleMessage extends BPMessage {
	/**
	 * Message type
	 *
	 * @var string
	 */
	protected string $type = 'simple';

	/**
	 * Get message title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return bonus_plus()->get_option( 'discount_message_title' ) ?: '';
	}

	/**
	 * Get message text
	 *
	 * @return string
	 */
	public function get_text(): string {
		return bonus_plus()->get_option( 'simple_message_text' ) ?: '';
	}

	/**
	 * Get message image
	 *
	 * @return string
	 */
	public function get_image(): string {
		return bonus_plus()->get_option( 'simple_message_image' ) ?: '';
	}

	/**
	 * Get additional text
	 *
	 * @return string
	 */
	public function get_additional_text(): string {
		return bonus_plus()->get_option( 'simple_message_additional_text' ) ?: '';
	}

	/**
	 * Get data for frontend
	 *
	 * @return array
	 */
	public function get_js_data(): array {
		return array(
			'type'                    => $this->type,
			'first_appears_timestamp' => ( MINUTE_IN_SECONDS * bonus_plus()->get_option( 'simple_message_first_appears_minutes' ) ) * 1000,
			'repeat_after_timestamp'  => ( MINUTE_IN_SECONDS * bonus_plus()->get_option( 'simple_message_repeat_after_minutes' ) ) * 1000,
		);
	}
}
