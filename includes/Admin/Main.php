<?php
/**
 * Admin class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Admin;

defined( 'ABSPATH' ) || exit();

/**
 * Class Admin
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Main {
	/**
	 * Admin class instance
	 *
	 * @var Pages\Main
	 */
	private Pages\Main $pages;

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		new Assets();
		$this->pages = new Pages\Main();

		add_filter( 'plugin_action_links_' . bonus_plus()->get_option( 'plugin_file' ), array( $this, 'add_plugin_action_links' ), 1, 1 );
	}

	/**
	 * Filters the list of action links displayed for a specific plugin in the Plugins list table.
	 *
	 * The dynamic portion of the hook name, `$plugin_file`, refers to the path
	 * to the plugin file, relative to the plugins directory.
	 *
	 * @since 2.7.0
	 * @since 4.9.0 The 'Edit' link was removed from the list of action links.
	 *
	 * @param string[] $actions     An array of plugin action links. By default this can include
	 *                              'activate', 'deactivate', and 'delete'. With Multisite active
	 *                              this can also include 'network_active' and 'network_only' items.
	 */
	public function add_plugin_action_links( array $actions ): array {
		$action_links = array(
			'settings' => '<a href="' . bonus_plus()->pages( 'settings' )->get_page_url() . '" aria-label="' . esc_attr__( 'Plugin settings', 'bonusplus' ) . '">' . esc_html__( 'Settings', 'bonusplus' ) . '</a>',
		);

		return array_merge( $action_links, $actions );
	}

	/**
	 * Get Main Pages class
	 *
	 * @return Pages\Main
	 */
	public function pages(): Pages\Main {
		return $this->pages;
	}
}
