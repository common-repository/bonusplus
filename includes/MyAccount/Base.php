<?php
/**
 * Class for registration page in my-account
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\MyAccount;

defined( 'ABSPATH' ) || exit();

/**
 * Class Base
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
abstract class Base {
	/**
	 * Page slug in menu, url and template
	 *
	 * @var string
	 */
	protected string $page_slug;

	/**
	 * Menu title
	 *
	 * @var string|void
	 */
	protected string $menu_title;

	/**
	 * Menu item slug after which to place this menu item
	 *
	 * @var string
	 */
	protected string $place_after;

	/**
	 * Base constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_item' ), 100 );
		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_action( "woocommerce_account_{$this->page_slug}_endpoint", array( $this, 'add_content' ) );
	}

	/**
	 * Add menu item to account
	 *
	 * @param array $menu_items Default menu links.
	 */
	public function add_menu_item( array $menu_items ) {
		$new_menu_links = array();

		foreach ( $menu_items as $menu_slug => $menu_title ) {
			if ( $this->place_after === $menu_slug ) {
				$new_menu_links[ $this->page_slug ] = $this->menu_title;
			}

			$new_menu_links[ $menu_slug ] = $menu_title;
		}

		return $new_menu_links;
	}

	/**
	 * Add endpoint
	 */
	public function add_endpoint() {
		add_rewrite_endpoint( $this->page_slug, EP_PAGES );
	}

	/**
	 * Add page content
	 */
	public function add_content() {
		$template_path = sprintf(
			'my-account/%s/%s.php',
			bonus_plus()->get_option( 'id' ),
			'index'
		);

		if ( bonus_plus()->template_exists( $template_path ) ) {
			bonus_plus()->include_template( $template_path, $this->get_content_arguments() );
		}
	}

	/**
	 * Get arguments for page template
	 *
	 * @return array
	 */
	public function get_content_arguments(): array {
		return array();
	}
}
