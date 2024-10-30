<?php
/**
 * Account Api class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Api;

defined( 'ABSPATH' ) || exit();

/**
 * Account API class
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Account extends Base {
	/**
	 * Get account data
	 *
	 * @return string
	 */
	public function status(): string {
		$res = $this->request( 'account' );

		if ( is_wp_error( $res ) ) {
			return sprintf(
			// translators: %s reason.
				__( 'API not active. Reason: %1$s. Api settings are <a href="%2$s">here</a>', 'bonusplus' ),
				$res->get_error_message(),
				'https://bonusplus.pro/lk_new/#/integrations/bonusplusapi/'
			);
		}

		return __( 'API active', 'bonusplus' );
	}
}
