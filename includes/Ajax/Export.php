<?php
/**
 * Registration handlers
 *
 * @package Onepix\BonusPlus
 */

namespace Onepix\BonusPlus\Ajax;

use Exception;
use Onepix\BonusPlus\Entities\BPCustomer;
use Onepix\BonusPlus\Entities\BPExporter;

defined( 'ABSPATH' ) || exit();

/**
 * Class Registration
 *
 * @package Onepix\BonusPlus
 * @since   1.0.0
 */
class Export extends Base {
	/**
	 * Prefix for actions
	 *
	 * @var string
	 */
	const PREFIX = 'export';

	/**
	 * Ajax for wc api (registration with prefix)
	 *
	 * @var array
	 */
	const ACTIONS = array(
		'run',
	);

	/**
	 * Returns data for export
	 */
	public function run() {
		wp_send_json( array_merge( BPExporter::get_categories(), BPExporter::get_products() ) );
	}
}
