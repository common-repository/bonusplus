<?php
/**
 * Admin assets class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Admin;

defined( 'ABSPATH' ) || exit();

/**
 * Class Assets
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Assets {
	/**
	 * Assets constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueue plugin admin scripts
	 */
	public function enqueue_admin_scripts() {
		bonus_plus()
			->assets()
			->register_style( 'admin', 'admin.min.css' )
			->enqueue_style( 'admin' )
			->register_script(
				'admin',
				'admin.min.js',
				array( 'jquery', 'jquery-blockui' ),
				array(
					'urls' => array(),
				),
				true
			)
			->enqueue_script( 'admin' );
	}
}
