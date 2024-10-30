<?php
/**
 * Actions class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Actions;

defined( 'ABSPATH' ) || exit();

/**
 * Class Actions
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Main {
	/**
	 * Actions constructor.
	 */
	public function __construct() {
		new Assets();
		new Cart();
		new Checkout();
		new Export();
		new Messages();
		new Login();
		new Order();
		new Product();
	}
}
