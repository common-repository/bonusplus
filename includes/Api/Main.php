<?php
/**
 * Api class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Api;

defined( 'ABSPATH' ) || exit();

/**
 * Class Api
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Main {
	/**
	 * APIs
	 *
	 * @var array
	 */
	private array $apis = array(
		'account'  => Account::class,
		'code'     => Code::class,
		'customer' => Customer::class,
		'export'   => Export::class,
		'retail'   => Retail::class,
	);

	/**
	 * Initiated APIs
	 *
	 * @var array
	 */
	private array $initiated_apis = array();

	/**
	 * Get api by name.
	 *
	 * @param string $api_name Api name to get.
	 *
	 * @return object
	 */
	public function get( string $api_name ): ?object {
		if ( isset( $this->initiated_apis[ $api_name ] ) ) {
			return $this->initiated_apis[ $api_name ];
		}

		if ( isset( $this->apis[ $api_name ] ) ) {
			$this->initiated_apis[ $api_name ] = new $this->apis[ $api_name ]();

			return $this->initiated_apis[ $api_name ];
		}

		return null;
	}
}
