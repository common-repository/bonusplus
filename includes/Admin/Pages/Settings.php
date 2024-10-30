<?php
/**
 * Admin settings page class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Admin\Pages;

defined( 'ABSPATH' ) || exit();

/**
 * Class Settings
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Settings extends Base {
	/**
	 * Option and menu slug. Must be overwritten in child class
	 *
	 * @var string
	 */
	protected string $slug = 'settings';

	/**
	 * User capability to see page.
	 *
	 * @var string
	 */
	protected string $capability = 'edit_others_shop_orders';

	/**
	 * Split settings into tabs
	 *
	 * @var bool
	 */
	protected bool $tabs_enabled = true;

	/**
	 * Split settings into tabs
	 *
	 * @var bool
	 */
	protected bool $tabs_on_same_page = true;

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		$this->sections = array(
			/** Main tab */
			'api'              => array(
				'title'  => __( 'BonusPlus API', 'bonusplus' ),
				'tab'    => 'main',
				'fields' => array(
					'key'       => array(
						'type'  => 'api_key',
						'title' => __( 'API key', 'bonusplus' ),
					),
					'shop_name' => array(
						'type'        => 'text',
						'title'       => __( 'Shop name', 'bonusplus' ),
						'description' => sprintf(
						// translators: %s link to bonus plus account.
							__( 'Can be found <a href="%s" target="_blank">here</a>', 'bonusplus' ),
							'https://bonusplus.pro/lk_new/#/settings/cashiersAndStores/store/list'
						),
					),
				),
			),
			'bp_payment'       => array(
				'title'  => __( 'Order statuses', 'bonusplus' ),
				'tab'    => 'main',
				'fields' => array(
					'status' => array(
						'type'    => 'select',
						'title'   => __( 'Order status for payment in BonusPlus', 'bonusplus' ),
						'values'  => self::get_order_statuses_without_prefix(),
						'default' => 'processing',
					),
				),
			),
			'my_account'       => array(
				'title'  => __( 'My account page', 'bonusplus' ),
				'tab'    => 'main',
				'fields' => array(
					'title' => array(
						'type'    => 'text',
						'title'   => __( 'Title', 'bonusplus' ),
						'default' => __( 'Bonus Plus', 'bonusplus' ),
					),
				),
			),
			'debug'            => array(
				'title'  => __( 'Debug', 'bonusplus' ),
				'tab'    => 'main',
				'fields' => array(
					'logging' => array(
						'type'        => 'checkbox',
						'title'       => __( 'Save debug messages to the WooCommerce System Status log', 'bonusplus' ),
						'default'     => 'no',
						'description' => sprintf(
						// translators: %s link to bonus plus account.
							__( 'Logs can be found <a href="%s" target="_blank">here</a>', 'bonusplus' ),
							get_admin_url() . '/?page=wc-status&tab=logs#log_file'
						),
					),
				),
			),
			/** Discounts tab */
			'discounts'        => array(
				'title'  => __( 'General settings', 'bonusplus' ),
				'tab'    => 'discounts',
				'fields' => array(
					'enabled'        => array(
						'type'    => 'checkbox',
						'title'   => __( 'Enable feature', 'bonusplus' ),
						'default' => 'no',
					),
					'double_enabled' => array(
						'type'    => 'checkbox',
						'title'   => __( 'Enable double discount (Shop + BonusPlus)', 'bonusplus' ),
						'default' => 'no',
					),
				),
			),
			'discount_flash'   => array(
				'title'  => __( 'Discount flash', 'bonusplus' ),
				'tab'    => 'discounts',
				'fields' => array(
					'text' => array(
						'type'    => 'text',
						'title'   => __( 'Flash text', 'bonusplus' ),
						// translators: %1$s discount percent.
						'default' => __( 'Discount: -%1$s', 'bonusplus' ),
					),
				),
			),
			/** Bonuses tab */
			'bonuses'          => array(
				'title'  => __( 'General settings', 'bonusplus' ),
				'tab'    => 'bonuses',
				'fields' => array(
					'enabled'                         => array(
						'type'    => 'checkbox',
						'title'   => __( 'Enable feature', 'bonusplus' ),
						'default' => 'no',
					),
					'write_off_confirmation_required' => array(
						'type'    => 'checkbox',
						'title'   => __( 'Enable write-off confirmation by SMS', 'bonusplus' ),
						'default' => 'yes',
					),
					'max_percent_of_cart_total'       => array(
						'type'    => 'number',
						'title'   => __( 'Maximum percentage of the cart amount. 100 to disable limit', 'bonusplus' ),
						'default' => 100,
						'args'    => array(
							'min' => 0,
							'max' => 100,
						),
					),
					'max_percent_of_available'        => array(
						'type'    => 'number',
						'title'   => __( 'Maximum percentage of the bonuses on the card. 100 to disable limit', 'bonusplus' ),
						'default' => 100,
						'args'    => array(
							'min' => 0,
							'max' => 100,
						),
					),
				),
			),
			/** Messages tab */
			'messages'         => array(
				'title'  => __( 'General settings', 'bonusplus' ),
				'tab'    => 'messages',
				'fields' => array(
					'enabled' => array(
						'type'    => 'checkbox',
						'title'   => __( 'Enable feature', 'bonusplus' ),
						'default' => 'no',
					),
				),
			),
			'simple_message'   => array(
				'title'  => __( 'BonusPlus simple message (Empty cart)', 'bonusplus' ),
				'tab'    => 'messages',
				'fields' => array(
					'title'                 => array(
						'type'    => 'text',
						'title'   => __( 'Message title', 'bonusplus' ),
						'default' => __( 'BonusPlus registration', 'bonusplus' ),
					),
					'text'                  => array(
						'type'    => 'text',
						'title'   => __( 'Message text', 'bonusplus' ),
						'default' => __( 'Log in and use your personal discount', 'bonusplus' ),
					),
					'additional_text'       => array(
						'type'    => 'text',
						'title'   => __( 'Additional text under form', 'bonusplus' ),
						'default' => __( 'After logging in, you will be able to receive an individual discount and special offers', 'bonusplus' ),
					),
					'image'                 => array(
						'type'    => 'image',
						'title'   => __( 'Message image', 'bonusplus' ),
						'default' => '',
					),
					'first_appears_minutes' => array(
						'type'    => 'text',
						'title'   => __( 'Time until message first appears (minutes)', 'bonusplus' ),
						'default' => 3,
					),
					'repeat_after_minutes'  => array(
						'type'    => 'text',
						'title'   => __( 'Time to re-display message (minutes). Zero to disable repetition', 'bonusplus' ),
						'default' => 3,
					),
				),
			),
			'discount_message' => array(
				'title'  => __( 'BonusPlus discount message (Not empty cart)', 'bonusplus' ),
				'tab'    => 'messages',
				'fields' => array(
					'title'           => array(
						'type'    => 'text',
						'title'   => __( 'Message title', 'bonusplus' ),
						'default' => __( 'BonusPlus registration', 'bonusplus' ),
					),
					'text'            => array(
						'type'    => 'text',
						'title'   => __( 'Message text', 'bonusplus' ),
						// translators: %1$s - price with discount,  %2$s - regular price.
						'default' => __( 'After registration in the loyalty system, the order amount will be %1$s instead of %2$s', 'bonusplus' ),
					),
					'additional_text' => array(
						'type'    => 'text',
						'title'   => __( 'Additional text under form', 'bonusplus' ),
						'default' => __( 'After logging in, you will be able to receive an individual discount and special offers', 'bonusplus' ),
					),
					'image'           => array(
						'type'    => 'image',
						'title'   => __( 'Message image', 'bonusplus' ),
						'default' => '',
					),
					'discount'        => array(
						'type'    => 'number',
						'title'   => __( 'Discount in percents', 'bonusplus' ),
						'default' => 0,
						'args'    => array(
							'min' => 0,
							'max' => 100,
						),
					),
				),
			),
			'privacy_policy'   => array(
				'title'  => __( 'Privacy policy', 'bonusplus' ),
				'tab'    => 'messages',
				'fields' => array(
					'text' => array(
						'type'    => 'text',
						'title'   => __( 'Privacy policy text', 'bonusplus' ),
						'default' => __( 'By registering you agree to the privacy policy', 'bonusplus' ),
					),
				),
			),
		);

		$this->tabs = array(
			'main'      => __( 'Main settings', 'bonusplus' ),
			'discounts' => __( 'Product discounts', 'bonusplus' ),
			'bonuses'   => __( 'Bonuses', 'bonusplus' ),
			'messages'  => __( 'Popup messages', 'bonusplus' ),
		);

		parent::__construct();

		if ( $this->is_current_page() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'add_media_scripts' ) );
		}
	}

	/**
	 * Register submenu in tools menu
	 */
	public function create_menu() {
		add_menu_page(
			__( 'BonusPlus', 'bonusplus' ),
			__( 'BonusPlus', 'bonusplus' ),
			$this->capability,
			$this->menu_slug,
			array( $this, 'print_page' ),
			'dashicons-superhero-alt',
			58
		);
	}

	/**
	 * Add media scripts on page
	 */
	public function add_media_scripts() {
		wp_enqueue_media();
	}

	/**
	 * Get wc_get_order_statuses() but without 'wc-'.
	 *
	 * @return array
	 */
	public static function get_order_statuses_without_prefix(): array {
		$order_statuses = array();

		foreach ( wc_get_order_statuses() as $key => $val ) {
			$order_statuses[ str_replace( 'wc-', '', $key ) ] = $val;
		}

		return $order_statuses;
	}
}
