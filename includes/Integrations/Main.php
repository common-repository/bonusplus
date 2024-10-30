<?php
/**
 * Main integrations class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Integrations;

defined( 'ABSPATH' ) || exit();

/**
 * Class Main
 *
 * @package Onepix\BonusPlus
 * @since   1.2.1
 */
class Main {
	/**
	 * Main constructor.
	 */
	public function __construct() {
		new MoySklad();
	}
}
