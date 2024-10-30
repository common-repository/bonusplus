<?php
/**
 * Main class shortcut
 *
 * @package Onepix\BonusPlus
 */

use Onepix\BonusPlus\Main;

/**
 * Shortcut for getting Main class instance
 *
 * @return Main
 */
function bonus_plus(): Main {
	return Main::get_instance();
}
