<?php
/**
 * Api class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Api;

use Exception;
use WP_Error;

defined( 'ABSPATH' ) || exit();

/**
 * Base API class
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
abstract class Base {
	/**
	 * API base url
	 *
	 * @var string
	 */
	private string $api_url = 'https://bonusplus.pro/api/';

	/**
	 * API key
	 *
	 * @var string
	 */
	private string $api_key;

	/**
	 * Api constructor.
	 */
	public function __construct() {
		$this->api_key = base64_encode( bonus_plus()->get_option( 'api_key' ) ?: '' ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * API request
	 *
	 * @param string $endpoint api endpoint.
	 * @param string $method   http method.
	 * @param array  $body     request body.
	 *
	 * @return array|WP_Error
	 */
	protected function request( string $endpoint, $method = 'GET', $body = array() ) {
		$url = $this->api_url . $endpoint;

		if ( 'GET' === $method ) {
			$url = wc_clean( add_query_arg( urlencode_deep( $body ), $url ) );
		}

		$params = array(
			'body'    => empty( $body ) || 'GET' === $method ? null : wp_json_encode( $body ),
			'method'  => $method,
			'timeout' => 20,
			'headers' => array(
				'Authorization' => 'ApiKey ' . $this->api_key,
				'Content-Type'  => 'application/json',
			),
		);

		$params['headers']['Content-Length'] = strlen( $params['body'] ?: '' );

		bonus_plus()->log(
			array(
				'url'    => $url,
				'method' => $method,
				'body'   => $body,
				'params' => $params,
			)
		);

		$res = wp_remote_request( $url, $params );

		if ( is_wp_error( $res ) ) {
			bonus_plus()->log(
				array(
					'error' => $res->get_error_message(),
				)
			);

			return $res;
		}

		bonus_plus()->log(
			array(
				'response_body'    => ! empty( $res['body'] ) ? $res['body'] : 'empty',
				'response_headers' => ! empty( $res['headers'] ) ? $res['headers'] : 'empty',
			)
		);

		$res_body = json_decode( $res['body'], true );

		if ( ! empty( $res['body'] ) && is_null( $res_body ) ) {
			bonus_plus()->log(
				array(
					'error' => 'wrong body',
					'body'  => $res['body'],
				)
			);

			return new WP_Error( 'WRONG_BODY', __( 'BonusPlus error. Try again or contact us', 'bonusplus' ) );
		}

		if ( $res['response']['code'] >= 400 ) {
			return new WP_Error(
				$res_body['code'] ?? $res['response']['code'] ?? '500',
				$res_body['msg'] ?? $res['response']['message'] ?? __( 'BonusPlus API error', 'bonusplus' )
			);
		}

		return $res_body;
	}

	/**
	 * Prepare phone number for api request
	 *
	 * @param string $phone phone number to prepare.
	 *
	 * @return string
	 */
	protected function prepare_phone( string $phone ): string {
		if ( '+' === $phone[0] ) {
			$phone = substr( $phone, 1 );
		}

		return $phone;
	}
}
