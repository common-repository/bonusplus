<?php
/**
 * Login actions class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Actions;

use Exception;
use Onepix\BonusPlus\Entities\BPCustomer;
use WP_User;

defined( 'ABSPATH' ) || exit();

/**
 * Class Login
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Login {
	/**
	 * Login constructor.
	 */
	public function __construct() {
		add_action( 'wp_login', array( $this, 'update_phone' ), 10, 2 );
	}

	/**
	 * Check if customer registered in bonus plus
	 *
	 * @param  string  $login user login.
	 * @param  WP_User $user user instance.
	 */
	public function update_phone( string $login, WP_User $user ) {
		try {
			$bp_customer = new BPCustomer( $user->ID );
		} catch ( Exception $e ) {
			bonus_plus()->log( $e->getMessage() );
			return;
		}

		if ( empty( $bp_customer->get_phone() )
			 && ! empty( $_SESSION[ BPCustomer::SESSION_KEY_BP_PHONE_NUMBER ] )
		) {
			$bp_customer->set_registered_in_bp( $_SESSION[ BPCustomer::SESSION_KEY_BP_PHONE_NUMBER ] );
		}
	}
}
