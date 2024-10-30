<?php
/**
 * Assets class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Tools;

use Onepix\BonusPlus\Ajax\Cart;
use Onepix\BonusPlus\Ajax\Registration;
use Onepix\BonusPlus\Entities\BPCustomer;
use Onepix\BonusPlus\Entities\Messages\BPMessage;

defined( 'ABSPATH' ) || exit();

/**
 * Class Assets
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Assets {
	/**
	 * Prefix for styles and scripts
	 *
	 * @var string
	 */
	protected string $prefix;

	/**
	 * Files source
	 *
	 * @var string
	 */
	protected string $assets_src;

	/**
	 * Files version
	 *
	 * @var string
	 */
	protected string $version;

	/**
	 * Assets constructor.
	 */
	public function __construct() {
		$this->prefix     = bonus_plus()->get_option( 'id' );
		$this->assets_src = bonus_plus()->get_option( 'assets_url' );
		$this->version    = bonus_plus()->get_option( 'version' );
	}

	/**
	 * Register plugin styles file.
	 *
	 * @param  string $handle style id.
	 * @param  string $src style path.
	 * @param  array  $deps an array of registered styles handles this script depends on.
	 *
	 * @return Assets
	 */
	public function register_style( string $handle, string $src, $deps = array() ): Assets {
		wp_register_style(
			"{$this->prefix}-$handle",
			"{$this->assets_src}css/$src",
			$deps,
			$this->version
		);

		return $this;
	}

	/**
	 * Enqueue style. Also register script if $args sent
	 *
	 * @param  string $handle style handle.
	 *
	 * @return Assets
	 */
	public function enqueue_style( $handle ): Assets {
		wp_enqueue_style( "{$this->prefix}-$handle" );

		return $this;
	}

	/**
	 * Register plugin script.
	 *
	 * @param  string $handle script handle.
	 * @param  string $src script path.
	 * @param  array  $deps an array of registered script handles this script depends on.
	 * @param  array  $localizations data to be registered via wp_localize_script.
	 * @param  bool   $set_script_translation add localization to script via wp_set_script_translations.
	 *
	 * @return Assets
	 */
	public function register_script( string $handle, string $src, $deps = array(), $localizations = array(), $set_script_translation = false ): Assets {
		wp_register_script(
			"{$this->prefix}-$handle",
			"{$this->assets_src}js/$src",
			$deps,
			$this->version,
			true
		);

		foreach ( $localizations as $object_name => $data ) {
			wp_localize_script(
				"{$this->prefix}-$handle",
				$object_name,
				$data
			);
		}

		if ( $set_script_translation ) {
			wp_set_script_translations(
				"{$this->prefix}-$handle",
				'bonusplus',
				bonus_plus()->get_option( 'languages_path' )
			);
		}

		return $this;
	}

	/**
	 * Enqueue script. Also register script if $args sent
	 *
	 * @param string $handle script handle.
	 *
	 * @return $this
	 */
	public function enqueue_script( string $handle ): Assets {
		wp_enqueue_script( "{$this->prefix}-$handle" );

		return $this;
	}
}
