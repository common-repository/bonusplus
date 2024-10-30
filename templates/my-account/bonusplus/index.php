<?php
/**
 * My account > Bonus Plus page template
 *
 * @package Onepix\BonusPlus
 *
 * @var string $error 'phone_not_specified', 'not_registered_in_bp' or 'bonus_plus_error'
 * @var string $url_to_phone_input
 * @var string $card_name
 * @var float  $discount
 * @var float  $bonuses
 */

?>
<div class="bonusPlusAccount">
	<?php if ( empty( $error ) ) : ?>
		<?php if ( $discount > 0 || $bonuses > 0 ) : ?>
			<div class="bonusPlusAccount__block">
				<div class="bonusPlusAccount__badge">
				<span class="bonusPlusAccount__badge-status">
					<?php
					echo esc_html(
						sprintf(
						// translators: %1$s - card name.
							__( 'Your card «%1$s»', 'bonusplus' ),
							$card_name
						)
					)
					?>
				</span>
					<?php if ( $discount > 0 ) : ?>
						<span class="bonusPlusAccount__badge-discount"><?php echo esc_html( $discount ); ?>%</span>
						<div class="bonusPlusAccount__badge-message">
							<span><?php esc_html_e( 'Discount on next purchases', 'bonusplus' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( $bonuses > 0 ) : ?>
						<span class="bonusPlusAccount__badge-discount"><?php echo esc_html( $bonuses ); ?></span>
						<div class="bonusPlusAccount__badge-message">
							<span><?php esc_html_e( 'Available bonuses', 'bonusplus' ); ?></span>
						</div>
					<?php endif; ?>
				</div>
				<div class="bonusPlusAccount__messages">
					<?php if ( $discount > 0 ) : ?>
						<span class="bonusPlusAccount__message">
							<?php esc_html_e( 'Increase the total amount of purchases and increase the percentage of fireproof discounts on future purchases!', 'bonusplus' ); ?>
						</span>
					<?php endif; ?>
					<?php if ( $bonuses > 0 ) : ?>
						<span class="bonusPlusAccount__message">
							<?php esc_html_e( 'Make purchases and get more bonuses!', 'bonusplus' ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="bonusPlusAccount__error">
			<?php if ( 'phone_not_specified' === $error ) : ?>
				<?php
				// translators: %1$s - url to phone form input.
				echo sprintf( __( 'Specify your phone number <a href="%1$s" target="_blank">here</a> to get BonusPlus benefits', 'bonusplus' ), $url_to_phone_input ) //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			<?php elseif ( 'not_registered_in_bp' === $error ) : ?>
				<?php esc_html_e( 'Make an order and you will be automatically registered with BonusPlus', 'bonusplus' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'BonusPlus error. Try again or contact us', 'bonusplus' ); ?>
			<?php endif ?>
		</div>
	<?php endif; ?>
</div>
