<?php
/**
 * Earned bonuses info in checkout
 *
 * @package Onepix\BonusPlus
 *
 * @var int $earned_bonuses bonuses amount.
 */

?>

<tr class="order-total bonusPlus-total">
	<th><?php esc_html_e( 'Bonuses will be credited', 'bonusplus' ); ?>:</th>
	<td><?php echo esc_html( $earned_bonuses ?: 0 ); ?></td>
</tr>
