<?php
/**
 * Class for registration page in my-account
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\MyAccount;

use Exception;
use Onepix\BonusPlus\Entities\BPCustomer;

defined( 'ABSPATH' ) || exit();

/**
 * Class Base
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class BonusPlus extends Base {
	/**
	 * Page slug in menu, url and template
	 *
	 * @var string
	 */
	protected string $page_slug = 'bonusplus';

	/**
	 * Menu item slug after which to place this menu item
	 *
	 * @var string
	 */
	protected string $place_after = 'customer-logout';

	/**
	 * BonusPlus constructor.
	 */
	public function __construct() {
		$this->menu_title = bonus_plus()->get_option( 'my_account_title' ) ?: __( 'BonusPlus', 'bonusplus' );

		parent::__construct();
	}

	/**
	 * Get arguments for page template
	 *
	 * @return array
	 */
	public function get_content_arguments(): array {
		$bp_customer  = new BPCustomer();
		$phone_number = $bp_customer->customer()->get_billing_phone();

		if ( empty( $phone_number ) ) {
			return array(
				'error'              => 'phone_not_specified',
				'url_to_phone_input' => wc_get_page_permalink( 'myaccount' ) . 'edit-address/billing/',
			);
		}

		try {
			$customer_data = bonus_plus()->api( 'customer' )->get_by_phone( $phone_number );

			if ( is_null( $customer_data ) ) {
				return array(
					'error' => 'not_registered_in_bp',
				);
			}
		} catch ( Exception $e ) {
			return array(
				'error' => 'not_registered_in_bp',
			);
		}

		return array(
			'bonuses'   => (float) $customer_data['availableBonuses'] ?? 0,
			'discount'  => (float) $customer_data['baseDiscountPercent'] ?? 0,
			'card_name' => $customer_data['discountCardName'] ?? '',
		);
	}
}
