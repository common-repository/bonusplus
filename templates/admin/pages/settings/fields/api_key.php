<?php
/**
 * API key template
 *
 * @package Onepix\BonusPlus
 *
 * @var string $id   input id
 * @var string $name input name
 * @var string $value option value
 */

?>

<input
		type="text"
		id="<?php echo esc_attr( $id ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
		style="width:100%"
>

<p><?php echo wp_kses_post( bonus_plus()->api( 'account' )->status() ); ?></p>
