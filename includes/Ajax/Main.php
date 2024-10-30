<?php
/**
 * Ajax class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Ajax;

defined( 'ABSPATH' ) || exit();

/**
 * Class Ajax
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Main {
	/**
	 * Ajax constructor.
	 */
	public function __construct() {
		new Bonuses();
		new Cart();
		new Export();
		new Registration();
	}
}
