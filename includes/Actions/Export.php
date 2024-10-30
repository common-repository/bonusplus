<?php
/**
 * Exporter main class
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Actions;

use Onepix\BonusPlus\Entities\BPExporter;

defined( 'ABSPATH' ) || exit();

/**
 * Class Main
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Export {
	/**
	 * Cron action name
	 *
	 * @var string
	 */
	private static string $cron_action_name = 'bonus_plus_export_action';

	/**
	 * Activate cron. Use in register_activation_hook.
	 */
	public static function activate_cron() {
		wp_schedule_event( strtotime( 'today midnight' ), 'daily', self::$cron_action_name );
	}

	/**
	 * Deactivate cron. Use in register_deactivation_hook.
	 */
	public static function deactivate_cron() {
		wp_unschedule_event( wp_next_scheduled( self::$cron_action_name ), self::$cron_action_name );
	}

	/**
	 * Main constructor.
	 */
	public function __construct() {
		add_action( self::$cron_action_name, array( $this, 'export' ) );
	}

	/**
	 * Export data to BonusPlus
	 */
	public function export() {
		$this->export_entity( 'categories', BPExporter::get_categories() );
		$this->export_entity( 'products', BPExporter::get_products() );
	}

	/**
	 * Export to BP
	 *
	 * @param string $name entity name. 'products' or 'categories'.
	 * @param array  $data data to export.
	 */
	private function export_entity( string $name, array $data ) {
		bonus_plus()->log(
			array(
				$name => $data,
			),
			'',
			'-export'
		);

		$res = bonus_plus()->api( 'export' )->run( $data );

		if ( is_wp_error( $res ) ) {
			$res = array(
				'message' => $res->get_error_message(),
				'code'    => $res->get_error_code(),
			);
		}

		bonus_plus()->log( $res ?: 'success', '', '-export' );
	}
}
