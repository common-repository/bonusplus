<?php
/**
 * Messages class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Actions;

use Onepix\BonusPlus\Entities\Messages\BPMessage;

defined( 'ABSPATH' ) || exit();

/**
 * Class Messages
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Messages {
	/**
	 * Messages constructor.
	 */
	public function __construct() {
		add_action( 'wp_footer', array( $this, 'show_message' ) );
	}

	/**
	 * Add message popup to page
	 */
	public function show_message() {
		$bp_message = BPMessage::for_current_page();

		if ( empty( $bp_message ) ) {
			return;
		}

		bonus_plus()->include_template(
			'message.php',
			array(
				'title'              => $bp_message->get_title(),
				'message'            => $bp_message->get_text(),
				'image'              => $bp_message->get_image(),
				'additional_message' => $bp_message->get_additional_text(),
				'privacy_policy'     => $bp_message->get_privacy_policy(),
			)
		);
	}
}
