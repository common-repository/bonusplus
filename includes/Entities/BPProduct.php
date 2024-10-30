<?php
/**
 * Product class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Entities;

use Onepix\BonusPlus\Tools\Features;
use WC_Product;

defined( 'ABSPATH' ) || exit();

/**
 * Class BPProduct
 *
 * @package Onepix\BonusPlus
 * @since   1.1.7
 */
abstract class BPProduct {
	/**
	 * Check if BP discount can be applied
	 *
	 * @param  WC_Product|null $product product for check.
	 *
	 * @return bool
	 */
	public static function can_add_bp_discount( ?WC_Product $product = null ): bool {
		return ( new BPCustomer() )->is_registered_in_bp() &&
			   ! is_admin() &&
			   ( is_null( $product ) || ! $product->is_on_sale( '' ) || Features::double_discount_enabled() );
	}
}
