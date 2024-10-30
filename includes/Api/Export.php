<?php
/**
 * Export Api class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Api;

use WP_Error;

defined( 'ABSPATH' ) || exit();

/**
 * Export class
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Export extends Base {
	/**
	 * Run products export
	 *
	 * @param array $products array of BP products.
	 *
	 * @see https://bonusplus.pro/api/Help/Api/POST-product-import
	 * @see https://bonusplus.pro/api/Help/ResourceModel?modelName=ProductTiny
	 *
	 * @return array|WP_Error
	 */
	public function run( array $products ) {
		return $this->request(
			'product/import',
			'POST',
			array(
				'products' => $products,
				'store'    => bonus_plus()->get_option( 'api_shop_name' ),
			)
		);
	}
}
