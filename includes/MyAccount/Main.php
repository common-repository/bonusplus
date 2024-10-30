<?php
/**
 * Class for registration pages in my-account
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\MyAccount;

use Onepix\BonusPlus\Tools\RewriteRules;

defined( 'ABSPATH' ) || exit();

/**
 * Class MyAccount
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Main {
	/**
	 * Main constructor.
	 */
	public function __construct() {
		new BonusPlus();

		add_action(
			'init',
			function () {
				RewriteRules::flush_rewrite_rules();
			},
			0,
			PHP_INT_MAX
		);
	}
}
